<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\CoreBundle\Entity\Document;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueFilenameValidator extends ConstraintValidator
{
    protected FilesystemOperator $documentsStorage;

    public function __construct(FilesystemOperator $documentsStorage)
    {
        $this->documentsStorage = $documentsStorage;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     * @throws FilesystemException
     */
    public function validate($value, Constraint $constraint)
    {
        if ($constraint instanceof UniqueFilename) {
            /** @var Document $document */
            $document = $constraint->document;
            /*
             * If value is already the filename
             * do nothing.
             */
            if (
                null !== $document &&
                $value == $document->getFilename()
            ) {
                return;
            }

            $folder = $document->getMountFolderPath();

            if ($this->documentsStorage->fileExists($folder . '/' . $value)) {
                $this->context->addViolation($constraint->message);
            }
        }
    }
}
