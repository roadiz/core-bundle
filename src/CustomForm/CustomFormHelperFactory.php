<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Document\PrivateDocumentFactory;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\Form\FormFactoryInterface;

final class CustomFormHelperFactory
{
    protected PrivateDocumentFactory $privateDocumentFactory;
    protected ObjectManager $em;
    protected FormFactoryInterface $formFactory;
    protected Settings $settingsBag;

    /**
     * @param PrivateDocumentFactory $privateDocumentFactory
     * @param ObjectManager $em
     * @param FormFactoryInterface $formFactory
     * @param Settings $settingsBag
     */
    public function __construct(
        PrivateDocumentFactory $privateDocumentFactory,
        ObjectManager $em,
        FormFactoryInterface $formFactory,
        Settings $settingsBag
    ) {
        $this->privateDocumentFactory = $privateDocumentFactory;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->settingsBag = $settingsBag;
    }

    public function createHelper(CustomForm $customForm): CustomFormHelper
    {
        return new CustomFormHelper(
            $this->em,
            $customForm,
            $this->privateDocumentFactory,
            $this->formFactory,
            $this->settingsBag
        );
    }
}
