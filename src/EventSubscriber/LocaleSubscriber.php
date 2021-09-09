<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event dispatched to set up theme configuration at kernel request.
 */
final class LocaleSubscriber implements EventSubscriberInterface
{
    private ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        /*
         * Locale subscriber has HIGH priority over Firewall and Routing
         */
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 70],
        ];
    }

    private function getDefaultTranslation(): ?TranslationInterface
    {
        return $this->managerRegistry->getRepository(Translation::class)->findDefault();
    }

    /**
     * After a controller has been matched. We need to inject current
     * Kernel instance and main DI container.
     *
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->isMainRequest()) {
            $request = $event->getRequest();
            /*
             * Set default locale
             */
            if ($request->attributes->has('_locale') &&
                $request->attributes->get('_locale') !== '') {
                $locale = $request->attributes->get('_locale');
                $event->getRequest()->setLocale($locale);
                \Locale::setDefault($locale);
            } elseif (null !== $translation = $this->getDefaultTranslation()) {
                $shortLocale = $translation->getLocale();
                $event->getRequest()->setLocale($shortLocale);
                \Locale::setDefault($shortLocale);
            }
        }
    }
}
