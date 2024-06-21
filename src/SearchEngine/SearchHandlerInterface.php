<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

interface SearchHandlerInterface
{
    /**
     * @param string $q
     * @param array  $args
     * @param int $rows Results per page
     * @param bool $searchTags Search in tags/folders too, even if a node don’t match
     * @param int $proximity Proximity matching: Lucene supports finding words are a within a specific distance away. Default 10000000
     * @param int $page Retrieve a specific page
     *
     * @return SearchResultsInterface Return an array of doctrine Entities (Document, NodesSources)
     */
    public function search(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $proximity = 1,
        int $page = 1
    ): SearchResultsInterface;

    /**
     * Search with pre-filled argument for highlighting.
     *
     * @param string $q
     * @param array $args
     * @param int $rows
     * @param boolean $searchTags Search in tags/folders too, even if a node don’t match
     * @param int $proximity Proximity matching: Lucene supports finding words are a within a specific distance away.
     * @param int $page
     *
     * @return SearchResultsInterface Return a SearchResultsInterface iterable object.
     */
    public function searchWithHighlight(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $proximity = 1,
        int $page = 1
    ): SearchResultsInterface;

    /**
     * @param int $highlightingFragmentSize
     * @return SearchHandlerInterface
     */
    public function setHighlightingFragmentSize(int $highlightingFragmentSize): SearchHandlerInterface;
}
