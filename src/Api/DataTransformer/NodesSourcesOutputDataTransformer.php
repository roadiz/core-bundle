<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\NodesSourcesDto;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class NodesSourcesOutputDataTransformer implements DataTransformerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    protected function transformNodesSources(NodesSourcesDto $output, NodesSources $data, array $context = [])
    {
        $output->title = $data->getTitle();
        $output->node = $data->getNode();
        $output->metaTitle = $data->getMetaTitle();
        $output->metaDescription = $data->getMetaDescription();
        $output->translation = $data->getTranslation();
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
