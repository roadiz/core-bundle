<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use RZ\Roadiz\CoreBundle\EntityHandler\NodesSourcesHandler;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

class NodesSourcesHead implements NodesSourcesHeadInterface
{
    #[Serializer\Ignore]
    protected HandlerFactoryInterface $handlerFactory;

    #[Serializer\Ignore]
    protected ?array $seo = null;

    #[Serializer\Ignore]
    protected ?NodesSources $nodesSource;

    #[Serializer\Ignore]
    protected Settings $settingsBag;

    #[Serializer\Ignore]
    protected UrlGeneratorInterface $urlGenerator;

    #[Serializer\Ignore]
    protected NodeSourceApi $nodeSourceApi;

    #[Serializer\Ignore]
    protected TranslationInterface $defaultTranslation;

    /**
     * @param NodesSources|null $nodesSource
     * @param Settings $settingsBag
     * @param UrlGeneratorInterface $urlGenerator
     * @param NodeSourceApi $nodeSourceApi
     * @param HandlerFactoryInterface $handlerFactory
     * @param TranslationInterface $defaultTranslation
     */
    public function __construct(
        ?NodesSources $nodesSource,
        Settings $settingsBag,
        UrlGeneratorInterface $urlGenerator,
        NodeSourceApi $nodeSourceApi,
        HandlerFactoryInterface $handlerFactory,
        TranslationInterface $defaultTranslation
    ) {
        $this->nodesSource = $nodesSource;
        $this->settingsBag = $settingsBag;
        $this->urlGenerator = $urlGenerator;
        $this->nodeSourceApi = $nodeSourceApi;
        $this->defaultTranslation = $defaultTranslation;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getGoogleAnalytics(): ?string
    {
        return $this->settingsBag->get('universal_analytics_id', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getGoogleTagManager(): ?string
    {
        return $this->settingsBag->get('google_tag_manager_id', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getMatomoUrl(): ?string
    {
        return $this->settingsBag->get('matomo_url', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getMatomoSiteId(): ?string
    {
        return $this->settingsBag->get('matomo_site_id', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getSiteName(): ?string
    {
        // site_name
        return $this->settingsBag->get('site_name', null) ?? null;
    }

    /**
     * @return array
     */
    #[Serializer\Ignore]
    protected function getDefaultSeo(): array
    {
        if (null !== $this->nodesSource) {
            $nodesSourcesHandler = $this->handlerFactory->getHandler($this->nodesSource);
            if ($nodesSourcesHandler instanceof NodesSourcesHandler) {
                return $nodesSourcesHandler->getSEO();
            }
        }
        return [
            'title' => $this->settingsBag->get('site_name'),
            'description' => $this->settingsBag->get('seo_description'),
        ];
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getMetaTitle(): ?string
    {
        if (null === $this->seo) {
            $this->seo = $this->getDefaultSeo();
        }

        return $this->seo['title'];
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getMetaDescription(): ?string
    {
        if (null === $this->seo) {
            $this->seo = $this->getDefaultSeo();
        }

        return $this->seo['description'];
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getPolicyUrl(): ?string
    {
        $translation = $this->getTranslation();

        $policyNodeSource = $this->nodeSourceApi->getOneBy([
            'node.nodeName' => 'privacy',
            'translation' => $translation
        ]);
        if (null === $policyNodeSource) {
            $policyNodeSource = $this->nodeSourceApi->getOneBy([
                'node.nodeName' => 'legal',
                'translation' => $translation
            ]);
        }
        if (null !== $policyNodeSource) {
            return $this->urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                RouteObjectInterface::ROUTE_OBJECT => $policyNodeSource
            ]);
        }
        return null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getMainColor(): ?string
    {
        return $this->settingsBag->get('main_color', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getFacebookUrl(): ?string
    {
        return $this->settingsBag->get('facebook_url', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getInstagramUrl(): ?string
    {
        return $this->settingsBag->get('instagram_url', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getTwitterUrl(): ?string
    {
        return $this->settingsBag->get('twitter_url', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getYoutubeUrl(): ?string
    {
        return $this->settingsBag->get('youtube_url', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["nodes_sources_single", "walker"])]
    public function getLinkedinUrl(): ?string
    {
        return $this->settingsBag->get('linkedin_url', null) ?? null;
    }

    /**
     * @return string|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single", "walker"])]
    public function getHomePageUrl(): ?string
    {
        $homePage = $this->getHomePage();
        if (null !== $homePage) {
            return $this->urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                RouteObjectInterface::ROUTE_OBJECT => $homePage
            ]);
        }
        return null;
    }

    /**
     * @return DocumentInterface|null
     */
    #[Serializer\Groups(["web_response", "nodes_sources_single"])]
    public function getShareImage(): ?DocumentInterface
    {
        if (
            null !== $this->nodesSource &&
            method_exists($this->nodesSource, 'getHeaderImage') &&
            isset($this->nodesSource->getHeaderImage()[0])
        ) {
            return $this->nodesSource->getHeaderImage()[0];
        }
        if (
            null !== $this->nodesSource &&
            method_exists($this->nodesSource, 'getImage') &&
            isset($this->nodesSource->getImage()[0])
        ) {
            return $this->nodesSource->getImage()[0];
        }
        return $this->settingsBag->getDocument('share_image') ?? null;
    }

    /**
     * @return TranslationInterface
     */
    #[Serializer\Ignore()]
    public function getTranslation(): TranslationInterface
    {
        if (null !== $this->nodesSource) {
            return $this->nodesSource->getTranslation();
        }
        return $this->defaultTranslation;
    }

    /**
     * @return NodesSources|null
     */
    #[Serializer\Ignore()]
    private function getHomePage(): ?NodesSources
    {
        return $this->nodeSourceApi->getOneBy([
            'node.home' => true,
            'translation' => $this->getTranslation()
        ]);
    }
}
