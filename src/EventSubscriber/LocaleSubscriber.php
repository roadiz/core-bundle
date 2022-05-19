<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RequestContextAwareInterface;

final class LocaleSubscriber implements EventSubscriberInterface
{
    private ManagerRegistry $managerRegistry;
    private RequestContextAwareInterface $router;

    public function __construct(ManagerRegistry $managerRegistry, RequestContextAwareInterface $router)
    {
        $this->managerRegistry = $managerRegistry;
        $this->router = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered just after Symfony\Component\HttpKernel\EventListener\LocaleListener
            RequestEvent::class => [['onKernelRequest', 16]],
        ];
    }

    private function getDefaultTranslation(): ?TranslationInterface
    {
        return $this->managerRegistry->getRepository(Translation::class)->findDefault();
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $locale = $request->query->get('_locale') ?? $request->attributes->get('_locale');
        /*
         * Set default locale
         */
        if (null !== $locale && $locale !== '') {
            $event->getRequest()->setLocale($locale);
            \Locale::setDefault($locale);
            $this->router->getContext()->setParameter('_locale', $locale);
        } elseif (null !== $translation = $this->getDefaultTranslation()) {
            $shortLocale = $translation->getLocale();
            $event->getRequest()->setLocale($shortLocale);
            \Locale::setDefault($shortLocale);
            $this->router->getContext()->setParameter('_locale', $shortLocale);
        }
    }
}
