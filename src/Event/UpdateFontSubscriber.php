<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use Pimple\Container;
use RZ\Roadiz\CoreBundle\Event\Font\PreUpdatedFontEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Calls font life cycle methods when no data changed according to Doctrine.
 *
 * @package RZ\Roadiz\CoreBundle\Event
 */
class UpdateFontSubscriber implements EventSubscriberInterface
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
    public static function getSubscribedEvents()
    {
        return [
            PreUpdatedFontEvent::class => 'onPreUpdatedFont'
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
