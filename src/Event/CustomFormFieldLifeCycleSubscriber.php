<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use RZ\Roadiz\CoreBundle\EntityHandler\CustomFormFieldHandler;

class CustomFormFieldLifeCycleSubscriber implements EventSubscriber
{
    private CustomFormFieldHandler $customFormFieldHandler;

    /**
     * @param CustomFormFieldHandler $customFormFieldHandler
     */
    public function __construct(CustomFormFieldHandler $customFormFieldHandler)
    {
        $this->customFormFieldHandler = $customFormFieldHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $field = $event->getEntity();
        if ($field instanceof CustomFormField) {
            /*
             * Automatically set position only if not manually set before.
             */
            if ($field->getPosition() === 0.0) {
                /*
                 * Get the last index after last node in parent
                 */
                $this->customFormFieldHandler->setCustomFormField($field);
                $lastPosition = $this->customFormFieldHandler->cleanPositions(false);
                if ($lastPosition > 1) {
                    /*
                     * Need to decrement position because current field is already
                     * in custom-form field collection count.
                     */
                    $field->setPosition($lastPosition - 1);
                } else {
                    $field->setPosition($lastPosition);
                }
            }
        }
    }
}
