<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use RZ\Roadiz\CoreBundle\Entity\CustomFormFieldAttribute;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Form\CustomFormsType;
use RZ\Roadiz\Utils\Document\AbstractDocumentFactory;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @package RZ\Roadiz\Utils\CustomForm
 */
class CustomFormHelper
{
    public const ARRAY_SEPARATOR = ', ';

    protected AbstractDocumentFactory $documentFactory;
    protected ObjectManager $em;
    protected CustomForm $customForm;
    protected FormFactoryInterface $formFactory;
    protected Settings $settingsBag;

    /**
     * @param ObjectManager $em
     * @param CustomForm $customForm
     * @param AbstractDocumentFactory $documentFactory
     * @param FormFactoryInterface $formFactory
     * @param Settings $settingsBag
     */
    public function __construct(
        ObjectManager $em,
        CustomForm $customForm,
        AbstractDocumentFactory $documentFactory,
        FormFactoryInterface $formFactory,
        Settings $settingsBag
    ) {
        $this->em = $em;
        $this->customForm = $customForm;
        $this->documentFactory = $documentFactory;
        $this->formFactory = $formFactory;
        $this->settingsBag = $settingsBag;
    }

    /**
     * @param Request $request
     * @param boolean $forceExpanded
     * @param bool $prefix
     * @return FormInterface
     */
    public function getForm(
        Request $request,
        bool $forceExpanded = false,
        bool $prefix = true
    ): FormInterface {
        $defaults = $request->query->all();
        if ($prefix) {
            $name = (new AsciiSlugger())->slug($this->customForm->getName())->snake()->toString();
        } else {
            $name = '';
        }
        return $this->formFactory->createNamed($name, CustomFormsType::class, $defaults, [
            'recaptcha_public_key' => $this->settingsBag->get('recaptcha_public_key'),
            'recaptcha_private_key' => $this->settingsBag->get('recaptcha_private_key'),
            'customForm' => $this->customForm,
            'forceExpanded' => $forceExpanded,
        ]);
    }

    /**
     * Create or update custom-form answer and its attributes from
     * a submitted form data.
     *
     * @param FormInterface         $form
     * @param CustomFormAnswer|null $answer
     * @param string                $ipAddress
     * @return CustomFormAnswer
     */
    public function parseAnswerFormData(
        FormInterface $form,
        CustomFormAnswer $answer = null,
        string $ipAddress = ""
    ): CustomFormAnswer {
        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * Create answer if null.
             */
            if (null === $answer) {
                $answer = new CustomFormAnswer();
                $answer->setCustomForm($this->customForm);
                $this->em->persist($answer);
            }
            $answer->setSubmittedAt(new \DateTime());
            $answer->setIp($ipAddress);

            /** @var CustomFormField $customFormField */
            foreach ($this->customForm->getFields() as $customFormField) {
                $formField = null;
                $fieldAttr = null;

                /*
                 * Get data in form groups
                 */
                if ($customFormField->getGroupName() != '') {
                    $groupCanonical = StringHandler::slugify($customFormField->getGroupName());
                    $formGroup = $form->get($groupCanonical);
                    if ($formGroup->has($customFormField->getName())) {
                        $formField = $formGroup->get($customFormField->getName());
                        $fieldAttr = $this->getAttribute($answer, $customFormField);
                    }
                } else {
                    if ($form->has($customFormField->getName())) {
                        $formField = $form->get($customFormField->getName());
                        $fieldAttr = $this->getAttribute($answer, $customFormField);
                    }
                }

                if (null !== $formField) {
                    $data = $formField->getData();
                    /*
                    * Create attribute if null.
                    */
                    if (null === $fieldAttr) {
                        $fieldAttr = new CustomFormFieldAttribute();
                        $fieldAttr->setCustomFormAnswer($answer);
                        $fieldAttr->setCustomFormField($customFormField);
                        $this->em->persist($fieldAttr);
                    }

                    if (is_array($data) && isset($data[0]) && $data[0] instanceof UploadedFile) {
                        /** @var UploadedFile $file */
                        foreach ($data as $file) {
                            $this->handleUploadedFile($file, $fieldAttr);
                        }
                    } elseif ($data instanceof UploadedFile) {
                        $this->handleUploadedFile($data, $fieldAttr);
                    } else {
                        $fieldAttr->setValue($this->formValueToString($data));
                    }
                }
            }

            $this->em->flush();
            $this->em->refresh($answer);

            return $answer;
        }

        throw new \InvalidArgumentException('Form must be submitted and validated before begin parsing.');
    }

    /**
     * @param UploadedFile $file
     * @param CustomFormFieldAttribute $fieldAttr
     * @return CustomFormFieldAttribute
     */
    protected function handleUploadedFile(
        UploadedFile $file,
        CustomFormFieldAttribute $fieldAttr
    ): CustomFormFieldAttribute {
        $this->documentFactory->setFile($file);
        $this->documentFactory->setFolder($this->getDocumentFolderForCustomForm());
        $document = $this->documentFactory->getDocument();
        $fieldAttr->getDocuments()->add($document);
        $fieldAttr->setValue($fieldAttr->getValue() . ', ' . $file->getPathname());
        return $fieldAttr;
    }

    /**
     * @return Folder|null
     */
    protected function getDocumentFolderForCustomForm(): ?Folder
    {
        return $this->em->getRepository(Folder::class)
            ->findOrCreateByPath(
                'custom_forms/' .
                $this->customForm->getCreatedAt()->format('Ymd') . '_' .
                substr($this->customForm->getDisplayName(), 0, 30)
            );
    }

    /**
     * @param mixed $rawValue
     * @return string
     */
    private function formValueToString($rawValue): string
    {
        if ($rawValue instanceof \DateTime) {
            return $rawValue->format('Y-m-d H:i:s');
        } elseif (is_array($rawValue)) {
            $values = $rawValue;
            $values = array_map('trim', $values);
            $values = array_map('strip_tags', $values);
            return implode(static::ARRAY_SEPARATOR, $values);
        } else {
            return strip_tags((string) $rawValue);
        }
    }

    /**
     * @param CustomFormAnswer $answer
     * @param CustomFormField $field
     * @return CustomFormFieldAttribute|null
     */
    private function getAttribute(CustomFormAnswer $answer, CustomFormField $field): ?CustomFormFieldAttribute
    {
        return $this->em->getRepository(CustomFormFieldAttribute::class)->findOneBy([
            'customFormAnswer' => $answer,
            'customFormField' => $field,
        ]);
    }
}
