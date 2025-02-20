<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Document\PrivateDocumentFactory;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class CustomFormHelperFactory
{
    public function __construct(
        private PrivateDocumentFactory $privateDocumentFactory,
        private ObjectManager $em,
        private FormFactoryInterface $formFactory,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createHelper(CustomForm $customForm): CustomFormHelper
    {
        return new CustomFormHelper(
            $this->em,
            $customForm,
            $this->privateDocumentFactory,
            $this->formFactory,
            $this->eventDispatcher
        );
    }
}
