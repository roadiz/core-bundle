<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class UniqueNodeGenerator
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        protected NodeNamePolicyInterface $nodeNamePolicy,
        protected Security $security,
    ) {
    }

    /**
     * Generate a node with a unique name.
     *
     * This method flush entity-manager.
     *
     * @param NodeType $nodeType
     * @param TranslationInterface $translation
     * @param Node|null $parent
     * @param Tag|null $tag
     * @param bool $pushToTop
     * @return NodesSources
     */
    public function generate(
        NodeType $nodeType,
        TranslationInterface $translation,
        Node $parent = null,
        Tag $tag = null,
        bool $pushToTop = false
    ): NodesSources {
        $name = $nodeType->getDisplayName() . " " . uniqid();
        $node = new Node($nodeType);
        $node->setTtl($nodeType->getDefaultTtl());

        if (null !== $tag) {
            $node->addTag($tag);
        }
        $parent?->addChild($node);

        if ($pushToTop) {
            /*
             * Force position before first item
             */
            $node->setPosition(0.5);
        }

        /** @var class-string<NodesSources> $sourceClass */ # phpstan hint
        $sourceClass = NodeType::getGeneratedEntitiesNamespace() . "\\" . $nodeType->getSourceEntityClassName();

        $source = new $sourceClass($node, $translation);
        $source->setTitle($name);
        $source->setPublishedAt(new DateTime());
        $node->setNodeName($this->nodeNamePolicy->getCanonicalNodeName($source));

        $manager = $this->managerRegistry->getManagerForClass(Node::class);
        if (null !== $manager) {
            $manager->persist($node);
            $manager->persist($source);
            $manager->flush();
        }

        return $source;
    }

    /**
     * Try to generate a unique node from request variables.
     *
     * @param Request $request
     *
     * @return NodesSources
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function generateFromRequest(Request $request): NodesSources
    {
        $pushToTop = false;

        if ($request->get('pushTop') == 1) {
            $pushToTop = true;
        }

        if ($request->get('tagId') > 0) {
            $tag = $this->managerRegistry
                ->getRepository(Tag::class)
                ->find((int) $request->get('tagId'));
        } else {
            $tag = null;
        }

        if ($request->get('parentNodeId') > 0) {
            $parent = $this->managerRegistry
                ->getRepository(Node::class)
                ->find((int) $request->get('parentNodeId'));
            if (null === $parent || !$this->security->isGranted(NodeVoter::CREATE, $parent)) {
                throw new BadRequestHttpException("Parent node does not exist.");
            }
        } else {
            if (!$this->security->isGranted(NodeVoter::CREATE_AT_ROOT)) {
                throw new AccessDeniedException('You are not allowed to create a node at root.');
            }
            $parent = null;
        }

        $nodeTypeId = $request->get('nodeTypeId');
        if (!is_numeric($nodeTypeId) || $nodeTypeId < 1) {
            throw new BadRequestHttpException("No node-type ID has been given.");
        }

        /** @var NodeType|null $nodeType */
        $nodeType = $this->managerRegistry
            ->getRepository(NodeType::class)
            ->find((int) $nodeTypeId);

        if (null === $nodeType) {
            throw new BadRequestHttpException("Node-type does not exist.");
        }

        if ($request->get('translationId') > 0) {
            /** @var Translation $translation */
            $translation = $this->managerRegistry
                ->getRepository(Translation::class)
                ->find((int) $request->get('translationId'));
        } else {
            /*
             * If parent has only on translation, use parent translation instead of default one.
             */
            if (null !== $parent && false !== $parentNodeSource = $parent->getNodeSources()->first()) {
                $translation = $parentNodeSource->getTranslation();
            } else {
                /** @var Translation $translation */
                $translation = $this->managerRegistry
                    ->getRepository(Translation::class)
                    ->findDefault();
            }
        }

        return $this->generate(
            $nodeType,
            $translation,
            $parent,
            $tag,
            $pushToTop
        );
    }
}
