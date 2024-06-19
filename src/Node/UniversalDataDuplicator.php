<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;

final class UniversalDataDuplicator
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
     * Duplicate node-source universal to any other language source for the same node.
     *
     * **Be careful, this method does not flush.**
     *
     * @param NodesSources $source
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function duplicateUniversalContents(NodesSources $source): bool
    {
        /*
         * Only if source is default translation.
         * Non-default translation source should not contain universal fields.
         */
        if ($source->getTranslation()->isDefaultTranslation() || !$this->hasDefaultTranslation($source)) {
            $nodeTypeFieldRepository = $this->managerRegistry->getRepository(NodeTypeField::class);
            $universalFields = $nodeTypeFieldRepository->findAllUniversal($source->getNode()->getNodeType());

            if (count($universalFields) > 0) {
                $repository = $this->managerRegistry->getRepository(NodesSources::class);
                $repository->setDisplayingAllNodesStatuses(true)
                    ->setDisplayingNotPublishedNodes(true)
                ;
                $otherSources = $repository->findBy([
                    'node' => $source->getNode(),
                    'id' => ['!=', $source->getId()],
                ]);

                /** @var NodeTypeField $universalField */
                foreach ($universalFields as $universalField) {
                    /** @var NodesSources $otherSource */
                    foreach ($otherSources as $otherSource) {
                        if (!$universalField->isVirtual()) {
                            $this->duplicateNonVirtualField($source, $otherSource, $universalField);
                        } else {
                            switch ($universalField->getType()) {
                                case AbstractField::DOCUMENTS_T:
                                    $this->duplicateDocumentsField($source, $otherSource, $universalField);
                                    break;
                                case AbstractField::MULTI_PROVIDER_T:
                                case AbstractField::SINGLE_PROVIDER_T:
                                case AbstractField::MANY_TO_ONE_T:
                                case AbstractField::MANY_TO_MANY_T:
                                    $this->duplicateNonVirtualField($source, $otherSource, $universalField);
                                    break;
                            }
                        }
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * @param NodesSources $source
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function hasDefaultTranslation(NodesSources $source): bool
    {
        /** @var TranslationRepository $translationRepository */
        $translationRepository = $this->managerRegistry->getRepository(Translation::class);
        /** @var Translation $defaultTranslation */
        $defaultTranslation = $translationRepository->findDefault();

        /** @var NodesSourcesRepository $repository */
        $repository = $this->managerRegistry->getRepository(NodesSources::class);
        $sourceCount = $repository->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true)
            ->countBy([
                'node' => $source->getNode(),
                'translation' => $defaultTranslation,
            ]);

        return $sourceCount === 1;
    }

    protected function duplicateNonVirtualField(
        NodesSources $universalSource,
        NodesSources $destSource,
        NodeTypeFieldInterface $field
    ): void {
        $getter = $field->getGetterName();
        $setter = $field->getSetterName();

        $destSource->$setter($universalSource->$getter());
    }

    protected function duplicateDocumentsField(
        NodesSources $universalSource,
        NodesSources $destSource,
        NodeTypeFieldInterface $field
    ): void {
        $newDocuments = $this->managerRegistry
            ->getRepository(NodesSourcesDocuments::class)
            ->findBy(['nodeSource' => $universalSource, 'fieldName' => $field->getName()]);

        $formerDocuments = $this->managerRegistry
            ->getRepository(NodesSourcesDocuments::class)
            ->findBy(['nodeSource' => $destSource, 'fieldName' => $field->getName()]);

        $manager = $this->managerRegistry->getManagerForClass(NodesSourcesDocuments::class);
        if (null === $manager) {
            return;
        }

        /* Delete former documents */
        if (count($formerDocuments) > 0) {
            foreach ($formerDocuments as $formerDocument) {
                $manager->remove($formerDocument);
            }
        }
        /* Add new documents */
        if (count($newDocuments) > 0) {
            $position = 1;
            /** @var NodesSourcesDocuments $newDocument */
            foreach ($newDocuments as $newDocument) {
                $nsDoc = new NodesSourcesDocuments($destSource, $newDocument->getDocument(), $field);
                $nsDoc->setPosition($position);
                $position++;

                $manager->persist($nsDoc);
            }
        }
    }
}
