<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Unique Entity Validator checks if one or a set of fields contain unique values.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @package RZ\Roadiz\CoreBundle\Form\Constraint
 * @see https://github.com/symfony/doctrine-bridge/blob/master/Validator/Constraints/UniqueEntityValidator.php
 * @deprecated Use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator
 */
class UniqueEntityValidator extends ConstraintValidator
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param mixed $value
     * @param UniqueEntity $constraint
     *
     * @throws \Exception
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueEntity');
        }

        $fields = $constraint->fields;
        if (0 === count($fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        $class = $this->managerRegistry
            ->getManagerForClass(get_class($value))
            ->getClassMetadata(get_class($value));

        $criteria = [];
        $hasNullValue = false;
        foreach ($fields as $fieldName) {
            if (!$class instanceof ClassMetadataInfo) {
                throw new ConstraintDefinitionException(sprintf('The class "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.', get_class($value)));
            }
            if (!$class->hasField($fieldName) && !$class->hasAssociation($fieldName)) {
                throw new ConstraintDefinitionException(sprintf('The field "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.', $fieldName));
            }
            $fieldValue = $class->getReflectionProperty($fieldName)->getValue($value);

            if (null === $fieldValue) {
                $hasNullValue = true;
            }
            if ($constraint->ignoreNull && null === $fieldValue) {
                continue;
            }
            $criteria[$fieldName] = $fieldValue;
            if (null !== $criteria[$fieldName] && $class->hasAssociation($fieldName)) {
                /* Ensure the Proxy is initialized before using reflection to
                 * read its identifiers. This is necessary because the wrapped
                 * getter methods in the Proxy are being bypassed.
                 */
                $this->managerRegistry
                    ->getManagerForClass(get_class($value))
                    ->initializeObject($criteria[$fieldName]);
            }
        }
        // validation doesn't fail if one of the fields is null and if null values should be ignored
        if ($hasNullValue && $constraint->ignoreNull) {
            return;
        }
        // skip validation if there are no criteria (this can happen when the
        // "ignoreNull" option is enabled and fields to be checked are null
        if (empty($criteria)) {
            return;
        }
        if (null !== $constraint->entityClass) {
            /* Retrieve repository from given entity name.
             * We ensure the retrieved repository can handle the entity
             * by checking the entity is the same, or subclass of the supported entity.
             */
            $repository = $this->managerRegistry->getRepository($constraint->entityClass);
            $supportedClass = $repository->getClassName();
            if (!$value instanceof $supportedClass) {
                throw new ConstraintDefinitionException(sprintf('The "%s" entity repository does not support the "%s" entity. The entity should be an instance of or extend "%s".', $constraint->entityClass, $class->getName(), $supportedClass));
            }
        } else {
            $repository = $this->managerRegistry->getRepository(get_class($value));
        }
        $result = $repository->{$constraint->repositoryMethod}($criteria);
        if ($result instanceof \IteratorAggregate) {
            $result = $result->getIterator();
        }
        /* If the result is a MongoCursor, it must be advanced to the first
         * element. Rewinding should have no ill effect if $result is another
         * iterator implementation.
         */
        if ($result instanceof \Iterator) {
            $result->rewind();
        } elseif (is_array($result)) {
            reset($result);
        }
        /* If no entity matched the query criteria or a single entity matched,
         * which is the same as the entity being validated, the criteria is
         * unique.
         */
        if (0 === count($result) || (1 === count($result) && $value === ($result instanceof \Iterator ? $result->current() : current($result)))) {
            return;
        }

        $errorPath = null !== $constraint->errorPath ? $constraint->errorPath : $fields[0];
        $invalidValue = $criteria[$errorPath] ?? $criteria[$fields[0]];

        $this->context->buildViolation($constraint->message)
            ->atPath($errorPath)
            ->setParameter('{{ value }}', $this->formatWithIdentifiers($class, $invalidValue))
            ->setInvalidValue($invalidValue)
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->addViolation();
    }

    private function formatWithIdentifiers(ClassMetadata $class, mixed $value): string
    {
        if (!is_object($value) || $value instanceof \DateTimeInterface) {
            return $this->formatValue($value, self::PRETTY_DATE);
        }
        if ($class->getName() !== $idClass = get_class($value)) {
            // non unique value might be a composite PK that consists of other entity objects
            if ($this->managerRegistry->getManagerForClass($idClass)->getMetadataFactory()->hasMetadataFor($idClass)) {
                $identifiers = $this->managerRegistry
                    ->getManagerForClass($idClass)
                    ->getClassMetadata($idClass)
                    ->getIdentifierValues($value);
            } else {
                // this case might happen if the non unique column has a custom doctrine type and its value is an object
                // in which case we cannot get any identifiers for it
                $identifiers = [];
            }
        } else {
            $identifiers = $class->getIdentifierValues($value);
        }
        if (!$identifiers) {
            return sprintf('object("%s")', $idClass);
        }
        array_walk($identifiers, function (&$id, $field) {
            if (!is_object($id) || $id instanceof \DateTimeInterface) {
                $idAsString = $this->formatValue($id, self::PRETTY_DATE);
            } else {
                $idAsString = sprintf('object("%s")', get_class($id));
            }
            $id = sprintf('%s => %s', $field, $idAsString);
        });
        return sprintf('object("%s") identified by (%s)', $idClass, implode(', ', $identifiers));
    }
}
