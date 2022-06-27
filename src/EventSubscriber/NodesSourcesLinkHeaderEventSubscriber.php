<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Fig\Link\GenericLinkProvider;
use Psr\Link\EvolvableLinkProviderInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\WebLink\Link;

class NodesSourcesLinkHeaderEventSubscriber implements EventSubscriberInterface
{
    private ManagerRegistry $managerRegistry;
    private UrlGeneratorInterface $urlGenerator;
    private PreviewResolverInterface $previewResolver;

    public function __construct(
        ManagerRegistry $managerRegistry,
        UrlGeneratorInterface $urlGenerator,
        PreviewResolverInterface $previewResolver
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->urlGenerator = $urlGenerator;
        $this->previewResolver = $previewResolver;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvent::class => ['onKernelView', 15]
        ];
    }

    public function onKernelView(ViewEvent $event)
    {
        $request = $event->getRequest();
        $resources = $request->attributes->get('data', null);
        $linkProvider = $request->attributes->get('_links', new GenericLinkProvider());

        if ($resources instanceof NodesSources && $linkProvider instanceof EvolvableLinkProviderInterface) {
            /** @var NodesSources[] $allSources */
            $allSources = $this->managerRegistry
                ->getRepository(get_class($resources))
                ->findByNode($resources->getNode());

            foreach ($allSources as $singleSource) {
                $linkProvider = $linkProvider->withLink(
                    (new Link(
                        'alternate',
                        $this->urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                            RouteObjectInterface::ROUTE_OBJECT => $singleSource
                        ])
                    ))
                        ->withAttribute('hreflang', $singleSource->getTranslation()->getLocale())
                        ->withAttribute('title', $singleSource->getTranslation()->getName())
                        ->withAttribute('type', 'text/html')
                );
            }
            $request->attributes->set('_links', $linkProvider);
        }
    }
}
