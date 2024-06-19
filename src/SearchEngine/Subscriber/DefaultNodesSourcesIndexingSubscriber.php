<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Subscriber;

use Doctrine\Common\Collections\Criteria;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumNodeSource;

final class DefaultNodesSourcesIndexingSubscriber extends AbstractIndexingSubscriber
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesIndexingEvent::class => ['onIndexing', 1000],
        ];
    }

    public function onIndexing(NodesSourcesIndexingEvent $event): void
    {
        $nodeSource = $event->getNodeSource();
        $subResource = $event->isSubResource();
        $assoc = $event->getAssociations();
        $collection = [];
        $node = $nodeSource->getNode();

        if (null === $node) {
            throw new \RuntimeException("No node relation found for source: " . $nodeSource->getTitle(), 1);
        }

        // Need a documentType field
        $assoc[SolariumNodeSource::TYPE_DISCRIMINATOR] = SolariumNodeSource::DOCUMENT_TYPE;
        // Need a nodeSourceId field
        $assoc[SolariumNodeSource::IDENTIFIER_KEY] = $nodeSource->getId();
        $assoc['node_type_s'] = $node->getNodeType()->getName();
        $assoc['node_name_s'] = $node->getNodeName();
        $assoc['node_status_i'] = $node->getStatus();
        $assoc['node_visible_b'] = $node->isVisible();

        // Need a locale field
        $locale = $nodeSource->getTranslation()->getLocale();
        $lang = \Locale::getPrimaryLanguage($locale);
        $assoc['locale_s'] = $locale;

        /*
         * Index resource title
         */
        $title = $event->getSolariumDocument()->cleanTextContent($nodeSource->getTitle(), false);
        $assoc['title'] = $title;
        $assoc['title_txt_' . $lang] = $title;

        $assoc['created_at_dt'] = $this->formatDateTimeToUTC($node->getCreatedAt());
        $assoc['updated_at_dt'] = $this->formatDateTimeToUTC($node->getUpdatedAt());

        if (null !== $nodeSource->getPublishedAt()) {
            $assoc['published_at_dt'] =  $this->formatDateTimeToUTC($nodeSource->getPublishedAt());
        }

        /*
         * Do not index locale and tags if this is a sub-resource
         */
        if (!$subResource) {
            if ($this->canIndexTitleInCollection($nodeSource)) {
                $collection[] = $title;
            }
            /*
             * Index parent node ID and name to filter on it
             */
            $parent = $node->getParent();
            if (null !== $parent) {
                $assoc['node_parent_i'] = $parent->getId();
                $assoc['node_parent_s'] = $parent->getNodeName();
            }

            /*
             * `tags_txt` Must store only public, visible and user-searchable content.
             */
            $out = array_map(
                function (Tag $tag) use ($event, $nodeSource) {
                    $translatedTag = $tag->getTranslatedTagsByTranslation($nodeSource->getTranslation())->first();
                    $tagName = $translatedTag ?
                        $translatedTag->getName() :
                        $tag->getTagName();
                    return $event->getSolariumDocument()->cleanTextContent($tagName, false);
                },
                $nodeSource->getNode()->getTags()->filter(function (Tag $tag) {
                    return $tag->isVisible();
                })->toArray()
            );
            $out = array_filter(array_unique($out));
            // Use tags_txt to be compatible with other data types
            $assoc['tags_txt'] = $out;
            // Compile all tags names into a single localized text field.
            $assoc['tags_txt_' . $lang] = implode(' ', $out);

            /*
             * `all_tags_slugs_ss` can store all tags, even technical one, this fields should not user searchable.
             */
            $allOut = array_map(
                function (Tag $tag) {
                    return $tag->getTagName();
                },
                $nodeSource->getNode()->getTags()->toArray()
            );
            $allOut = array_filter(array_unique($allOut));
            // Use all_tags_slugs_ss to be compatible with other data types
            $assoc['all_tags_slugs_ss'] = $allOut;
        }

        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->eq("type", AbstractField::BOOLEAN_T));
        $booleanFields = $node->getNodeType()->getFields()->matching($criteria);

        /** @var NodeTypeField $booleanField */
        foreach ($booleanFields as $booleanField) {
            $name = $booleanField->getName();
            $name .= '_b';
            $getter = $booleanField->getGetterName();
            $assoc[$name] = $nodeSource->$getter();
        }

        $searchableFields = $node->getNodeType()->getSearchableFields();
        /** @var NodeTypeField $field */
        foreach ($searchableFields as $field) {
            $name = $field->getName();
            $getter = $field->getGetterName();
            $content = $nodeSource->$getter();
            /*
             * Strip markdown syntax
             */
            $content = $event->getSolariumDocument()->cleanTextContent($content);
            /*
             * Use locale to create field name
             * with right language
             */
            if (in_array($lang, SolariumNodeSource::$availableLocalizedTextFields)) {
                $name .= '_txt_' . $lang;
            } else {
                $name .= '_t';
            }

            $assoc[$name] = $content;
            $collection[] = $content;
        }
        /*
         * Collect data in a single field
         * for global search
         */
        $assoc['collection_txt'] = $collection;
        // Compile all text content into a single localized text field.
        $assoc['collection_txt_' . $lang] = implode(PHP_EOL, $collection);
        $event->setAssociations($assoc);
    }

    /**
     * @param NodesSources $source
     * @return bool
     */
    protected function canIndexTitleInCollection(NodesSources $source): bool
    {
        if (method_exists($source, 'getHideTitle')) {
            return !((bool) $source->getHideTitle());
        }
        if (method_exists($source, 'getShowTitle')) {
            return ((bool) $source->getShowTitle());
        }

        if (null !== $source->getNode() && $source->getNode()->getNodeType()) {
            return $source->getNode()->getNodeType()->isSearchable();
        }
        return true;
    }
}
