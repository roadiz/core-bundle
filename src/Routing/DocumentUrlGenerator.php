<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Utils\Asset\Packages;
use RZ\Roadiz\Utils\UrlGenerators\AbstractDocumentUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @package RZ\Roadiz\Utils\UrlGenerators
 */
final class DocumentUrlGenerator extends AbstractDocumentUrlGenerator
{
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param Packages $packages
     * @param UrlGeneratorInterface $urlGenerator
     * @param CacheItemPoolInterface $optionsCacheAdapter
     */
    public function __construct(
        Packages $packages,
        UrlGeneratorInterface $urlGenerator,
        CacheItemPoolInterface $optionsCacheAdapter
    ) {
        parent::__construct($packages, $optionsCacheAdapter);
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return string
     */
    protected function getRouteName(): string
    {
        return 'interventionRequestProcess';
    }

    protected function getProcessedDocumentUrlByArray(bool $absolute = false): string
    {
        if (null === $this->getDocument()) {
            throw new \InvalidArgumentException('Cannot get URL from a NULL document');
        }

        $referenceType = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        $routeParams = [
            'queryString' => $this->optionCompiler->compile($this->options),
            'filename' => $this->getDocument()->getRelativePath(),
        ];

        return $this->urlGenerator->generate(
            $this->getRouteName(),
            $routeParams,
            $referenceType
        );
    }
}
