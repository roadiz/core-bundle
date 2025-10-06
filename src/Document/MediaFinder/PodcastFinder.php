<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\Documents\MediaFinders\AbstractPodcastFinder;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class PodcastFinder extends AbstractPodcastFinder
{
    use EmbedFinderTrait;

    #[\Override]
    protected function injectMetaFromPodcastItem(
        ObjectManager $objectManager,
        DocumentInterface $document,
        \SimpleXMLElement $item,
    ): void {
        $translations = $objectManager->getRepository(Translation::class)->findAll();

        try {
            /** @var Translation $translation */
            foreach ($translations as $translation) {
                $documentTr = new DocumentTranslation();
                $documentTr->setDocument($document);
                $documentTr->setTranslation($translation);
                $documentTr->setName($this->getPodcastItemTitle($item));
                $documentTr->setDescription($this->getPodcastItemDescription($item));
                $documentTr->setCopyright($this->getPodcastItemCopyright($item));
                $objectManager->persist($documentTr);
            }
        } catch (ClientExceptionInterface) {
            // do not prevent from creating document if platform has errors, such as
            // too much API usage.
        }
    }
}
