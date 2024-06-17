<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not extend this class, use NodesSourcesPathGeneratingEvent::class event.
 */
final class NodesSourcesUrlGenerator
{
    public function __construct(
        private readonly NodesSourcesPathAggregator $pathAggregator,
        private readonly ?NodesSources $nodeSource = null,
        private readonly bool $forceLocale = false,
        private readonly bool $forceLocaleWithUrlAlias = false
    ) {
    }

    /**
     * @param NodesSources $nodeSource
     * @return bool
     */
    protected function isNodeSourceHome(NodesSources $nodeSource): bool
    {
        if ($nodeSource->getNode()->isHome()) {
            return true;
        }

        return false;
    }

    /**
     * Return a NodesSources url without hostname and without
     * root folder.
     *
     * It returns a relative url to Roadiz, not relative to your server root.
     *
     * @param Theme|null $theme
     * @param array $parameters
     *
     * @return string
     */
    public function getNonContextualUrl(?Theme $theme = null, array $parameters = []): string
    {
        if (null !== $this->nodeSource) {
            if ($this->isNodeSourceHome($this->nodeSource)) {
                if (
                    $this->nodeSource->getTranslation()->isDefaultTranslation() &&
                    false === $this->forceLocale
                ) {
                    return '';
                } else {
                    return $this->nodeSource->getTranslation()->getPreferredLocale();
                }
            }

            $path = $this->pathAggregator->aggregatePath($this->nodeSource, $parameters);

            /*
             * If using node-name, we must use shortLocale when current
             * translation is not the default one.
             */
            if ($this->urlNeedsLocalePrefix($this->nodeSource)) {
                $path = $this->nodeSource->getTranslation()->getPreferredLocale() . '/' . $path;
            }

            if (null !== $theme && $theme->getRoutePrefix() != '') {
                $path = $theme->getRoutePrefix() . '/' . $path;
            }
            /*
             * Add non default format at the path end.
             */
            if (isset($parameters['_format']) && in_array($parameters['_format'], ['xml', 'json', 'pdf'])) {
                $path .= '.' . $parameters['_format'];
            }

            return $path;
        } else {
            throw new \RuntimeException("Cannot generate Url for a NULL NodesSources", 1);
        }
    }

    /**
     * @param NodesSources $nodesSources
     *
     * @return bool
     */
    protected function useUrlAlias(NodesSources $nodesSources): bool
    {
        if ($nodesSources->getIdentifier() !== $nodesSources->getNode()->getNodeName()) {
            return true;
        }

        return false;
    }

    /**
     * @param NodesSources $nodesSources
     *
     * @return bool
     */
    protected function urlNeedsLocalePrefix(NodesSources $nodesSources): bool
    {
        /*
         * Needs a prefix only if translation is not default AND nodeSource does not have an Url alias
         * for this translation.
         * Of course we force prefix if admin said so…
         * Or we can force prefix only when we use urlAliases
         */
        if (
            (
                !$this->useUrlAlias($nodesSources) &&
                !$nodesSources->getTranslation()->isDefaultTranslation()
            ) ||
            (
                $this->useUrlAlias($nodesSources) &&
                !$nodesSources->getTranslation()->isDefaultTranslation() &&
                true === $this->forceLocaleWithUrlAlias
            ) ||
            true === $this->forceLocale
        ) {
            return true;
        }

        return false;
    }
}
