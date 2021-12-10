<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\NodeOutput;
use RZ\Roadiz\CoreBundle\Api\Dto\NodesSourcesDto;
use RZ\Roadiz\CoreBundle\Api\Dto\TranslationOutput;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class NodesSourcesOutputDataTransformer implements DataTransformerInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private NodeOutputDataTransformer $nodeOutputDataTransformer;
    private TranslationOutputDataTransformer $translationOutputDataTransformer;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param NodeOutputDataTransformer $nodeOutputDataTransformer
     * @param TranslationOutputDataTransformer $translationOutputDataTransformer
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        NodeOutputDataTransformer $nodeOutputDataTransformer,
        TranslationOutputDataTransformer $translationOutputDataTransformer
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->nodeOutputDataTransformer = $nodeOutputDataTransformer;
        $this->translationOutputDataTransformer = $translationOutputDataTransformer;
    }

    protected function transformNodesSources(NodesSourcesDto $output, NodesSources $data, array $context = [])
    {
        $output->title = $data->getTitle();
        $output->node = $this->nodeOutputDataTransformer->transform(
            $data->getNode(),
            NodeOutput::class,
            $context
        );
        $output->metaTitle = $data->getMetaTitle();
        $output->metaDescription = $data->getMetaDescription();
        $output->translation = $this->translationOutputDataTransformer->transform(
            $data->getTranslation(),
            TranslationOutput::class,
            $context
        );
        $output->slug = $data->getIdentifier();
        if ($data->isPublishable()) {
            $output->publishedAt = $data->getPublishedAt();
        }
        if ($data->isReachable()) {
            $output->url = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $data
                ],
                UrlGeneratorInterface::RELATIVE_PATH
            );
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $data instanceof NodesSources;
    }
}
