<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Translation;

/**
 * @package RZ\Roadiz\CoreBundle\SearchEngine
 */
class DocumentSearchHandler extends AbstractSearchHandler
{
    /**
     * @param string  $q
     * @param array   $args
     * @param integer $rows
     * @param boolean $searchTags
     * @param integer $proximity Proximity matching: Lucene supports finding words are a within a specific distance away.
     * @param integer $page
     *
     * @return array|null
     */
    protected function nativeSearch(
        $q,
        $args = [],
        $rows = 20,
        $searchTags = false,
        $proximity = 10000000,
        $page = 1
    ): ?array {
        if (!empty($q)) {
            $query = $this->createSolrQuery($args, $rows, $page);
            $queryTxt = $this->buildQuery($q, $args, $searchTags, $proximity);
            $query->setQuery($queryTxt);

            /*
             * Only need these fields as Doctrine
             * will do the rest.
             */
            $query->setFields([
                'id',
                'sort',
                'document_type_s',
                SolariumDocumentTranslation::IDENTIFIER_KEY,
                'filename_s',
                'locale_s',
            ]);


            if (null !== $this->logger) {
                $this->logger->debug('[Solr] Request document search…', [
                    'query' => $queryTxt,
                    'params' => $query->getParams(),
                ]);
            }

            $solrRequest = $this->getSolr()->execute($query);
            return $solrRequest->getData();
        } else {
            return null;
        }
    }

    /**
     * @param array $args
     * @return mixed
     */
    protected function argFqProcess(array &$args)
    {
        if (!isset($args["fq"])) {
            $args["fq"] = [];
        }

        // filter by tag or tags
        if (!empty($args['folders'])) {
            if ($args['folders'] instanceof Folder) {
                $args["fq"][] = "tags_txt:" . $args['folders']->getTranslatedFolders()->first()->getName();
            } elseif (is_array($args['folders'])) {
                foreach ($args['folders'] as $tag) {
                    if ($tag instanceof Folder) {
                        $args["fq"][] = "tags_txt:" . $tag->getTranslatedFolders()->first()->getName();
                    }
                }
            }
            unset($args['folders']);
        }

        if (isset($args['mimeType'])) {
            $tmp = "mime_type_s:";
            if (!is_array($args['mimeType'])) {
                $tmp .= (string) $args['mimeType'];
            } else {
                $value = implode(' AND ', $args['mimeType']);
                $tmp .= '(' . $value . ')';
            }
            unset($args['mimeType']);
            $args["fq"][] = $tmp;
        }

        /*
         * Filter by translation or locale
         */
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            $args["fq"][] = "locale_s:" . $args['translation']->getLocale();
        }
        if (isset($args['locale']) && is_string($args['locale'])) {
            $args["fq"][] = "locale_s:" . $args['locale'];
        }

        /*
         * Filter by filename
         */
        if (isset($args['filename'])) {
            $args["fq"][] = "filename_s:" . trim($args['filename']);
        }

        /*
         * Filter out non-valid copyright documents
         */
        if (isset($args['copyrightValid'])) {
            $args["fq"][] = '(copyright_valid_since_dt:[* TO NOW] AND copyright_valid_until_dt:[NOW TO *])';
            unset($args['copyrightValid']);
        }

        return $args;
    }

    /**
     * @return string
     */
    protected function getDocumentType(): string
    {
        return 'DocumentTranslation';
    }
}
