<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber\FontLifeCycleSubscriber;
use RZ\Roadiz\CoreBundle\Event\Font\PreUpdatedFontEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Calls font life cycle methods when no data changed according to Doctrine.
 *
 * @package RZ\Roadiz\CoreBundle\Event
 */
final class UpdateFontSubscriber implements EventSubscriberInterface
{
    private FontLifeCycleSubscriber $fontSubscriber;

    /**
     * @param FontLifeCycleSubscriber $fontSubscriber
     */
    public function __construct(FontLifeCycleSubscriber $fontSubscriber)
    {
        $this->fontSubscriber = $fontSubscriber;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PreUpdatedFontEvent::class => 'onPreUpdatedFont',
            '\RZ\Roadiz\Core\Events\Font\PreUpdatedFontEvent' => 'onPreUpdatedFont',
        ];
    }

    public function onPreUpdatedFont(PreUpdatedFontEvent $event)
    {
        $font = $event->getFont();
        if (null !== $font) {
            /*
             * Force updating files if uploaded
             * as doctrine won't see any changes.
             */
            $this->fontSubscriber->setFontFilesNames($font);
            $this->fontSubscriber->upload($font);
        }
    }
}
