<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ObjectManager;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Doctrine\SchemaUpdater;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\NodeType\ApiResourceGenerator;
use RZ\Roadiz\EntityGenerator\EntityGeneratorFactory;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Handle operations with node-type entities.
 */
class NodeTypeHandler extends AbstractHandler
{
    private ?NodeType $nodeType = null;
    private EntityGeneratorFactory $entityGeneratorFactory;
    private ApiResourceGenerator $apiResourceGenerator;
    private HandlerFactory $handlerFactory;
    private string $generatedEntitiesDir;
    private SerializerInterface $serializer;
    private string $serializedNodeTypesDir;
    private string $importFilesConfigPath;
    private string $kernelProjectDir;

    /**
     * @return NodeType
     */
    public function getNodeType(): NodeType
    {
        if (null === $this->nodeType) {
            throw new \BadMethodCallException('NodeType is null');
        }
        return $this->nodeType;
    }

    /**
     * @param NodeType $nodeType
     * @return $this
     */
    public function setNodeType(NodeType $nodeType)
    {
        $this->nodeType = $nodeType;
        return $this;
    }

    public function __construct(
        ObjectManager $objectManager,
        EntityGeneratorFactory $entityGeneratorFactory,
        HandlerFactory $handlerFactory,
        SerializerInterface $serializer,
        ApiResourceGenerator $apiResourceGenerator,
        string $generatedEntitiesDir,
        string $serializedNodeTypesDir,
        string $importFilesConfigPath,
        string $kernelProjectDir
    ) {
        parent::__construct($objectManager);
        $this->entityGeneratorFactory = $entityGeneratorFactory;
        $this->handlerFactory = $handlerFactory;
        $this->generatedEntitiesDir = $generatedEntitiesDir;
        $this->serializer = $serializer;
        $this->serializedNodeTypesDir = $serializedNodeTypesDir;
        $this->importFilesConfigPath = $importFilesConfigPath;
        $this->kernelProjectDir = $kernelProjectDir;
        $this->apiResourceGenerator = $apiResourceGenerator;
    }

    public function getGeneratedEntitiesFolder(): string
    {
        return $this->generatedEntitiesDir;
    }

    public function getGeneratedRepositoriesFolder(): string
    {
        return $this->getGeneratedEntitiesFolder() . DIRECTORY_SEPARATOR . 'Repository';
    }

    /**
     * Remove node type entity class file from server.
     */
    public function removeSourceEntityClass(): bool
    {
        $file = $this->getSourceClassPath();
        $repositoryFile = $this->getRepositoryClassPath();
        $fileSystem = new Filesystem();

        if ($fileSystem->exists($file) && is_file($file)) {
            $fileSystem->remove($file);
            /*
             * Delete repository class file too.
             */
            if ($fileSystem->exists($repositoryFile) && is_file($repositoryFile)) {
                $fileSystem->remove($repositoryFile);
            }
            return true;
        }

        return false;
    }

    public function exportNodeTypeJsonFile(): ?string
    {
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($this->serializedNodeTypesDir)) {
            $content = $this->serializer->serialize(
                $this->nodeType,
                'json',
                SerializationContext::create()->setGroups(['node_type', 'position'])
            );
            $file = $this->serializedNodeTypesDir . DIRECTORY_SEPARATOR . $this->nodeType->getName() . '.json';
            @file_put_contents($file, $content);

            $this->addNodeTypeToImportFilesConfiguration($fileSystem, $file);

            return $file;
        }
        return null;
    }

    protected function removeNodeTypeJsonFile(): void
    {
        $fileSystem = new Filesystem();
        $file = $this->serializedNodeTypesDir . DIRECTORY_SEPARATOR . $this->nodeType->getName() . '.json';
        if ($fileSystem->exists($file)) {
            @unlink($file);
            $this->removeNodeTypeFromImportFilesConfiguration($fileSystem, $file);
        }
    }

    protected function addNodeTypeToImportFilesConfiguration(Filesystem $fileSystem, string $file): void
    {
        if ($fileSystem->exists($this->importFilesConfigPath)) {
            $configFile = new File($this->importFilesConfigPath);
            if ($configFile->isWritable()) {
                try {
                    $config = Yaml::parseFile($this->importFilesConfigPath);
                    if (!isset($config['importFiles'])) {
                        $config['importFiles'] = [
                            'nodetypes' => []
                        ];
                    }
                    if (!isset($config['importFiles']['nodetypes'])) {
                        $config['importFiles']['nodetypes'] = [];
                    }

                    $relativePath = str_replace(
                        $this->kernelProjectDir . DIRECTORY_SEPARATOR,
                        '',
                        $file
                    );
                    if (!in_array($relativePath, $config['importFiles']['nodetypes'])) {
                        $config['importFiles']['nodetypes'][] = $relativePath;
                        sort($config['importFiles']['nodetypes']);

                        $yamlContent = Yaml::dump($config, 3);
                        @file_put_contents($this->importFilesConfigPath, $yamlContent);
                    }
                } catch (ParseException $exception) {
                    // Silent errors
                }
            }
        }
    }

    protected function removeNodeTypeFromImportFilesConfiguration(Filesystem $fileSystem, string $file): void
    {
        if ($fileSystem->exists($this->importFilesConfigPath)) {
            $configFile = new File($this->importFilesConfigPath);
            if ($configFile->isWritable()) {
                try {
                    $config = Yaml::parseFile($this->importFilesConfigPath);
                    if (!isset($config['importFiles'])) {
                        return;
                    }
                    if (!isset($config['importFiles']['nodetypes'])) {
                        return;
                    }

                    $relativePath = str_replace(
                        $this->kernelProjectDir . DIRECTORY_SEPARATOR,
                        '',
                        $file
                    );
                    if (false !== $key = array_search($relativePath, $config['importFiles']['nodetypes'])) {
                        unset($config['importFiles']['nodetypes'][$key]);
                        $config['importFiles']['nodetypes'] = array_values(array_filter($config['importFiles']['nodetypes']));
                        sort($config['importFiles']['nodetypes']);
                        $yamlContent = Yaml::dump($config, 3);
                        @file_put_contents($this->importFilesConfigPath, $yamlContent);
                    }
                } catch (ParseException $exception) {
                    // Silent errors
                }
            }
        }
    }

    /**
     * Generate Doctrine entity class for current node-type.
     *
     * @return bool
     */
    public function generateSourceEntityClass(): bool
    {
        $folder = $this->getGeneratedEntitiesFolder();
        $repositoryFolder = $this->getGeneratedRepositoriesFolder();
        $file = $this->getSourceClassPath();
        $repositoryFile = $this->getRepositoryClassPath();
        $fileSystem = new Filesystem();

        if (!$fileSystem->exists($folder)) {
            $fileSystem->mkdir($folder, 0775);
        }
        if (!$fileSystem->exists($repositoryFolder)) {
            $fileSystem->mkdir($repositoryFolder, 0775);
        }

        if (!$fileSystem->exists($file)) {
            $classGenerator = $this->entityGeneratorFactory->createWithCustomRepository($this->nodeType);
            $repositoryGenerator = $this->entityGeneratorFactory->createCustomRepository($this->nodeType);
            $content = $classGenerator->getClassContent();
            $repositoryContent = $repositoryGenerator->getClassContent();

            if (false === @file_put_contents($file, $content)) {
                throw new IOException("Impossible to write entity class file (" . $file . ").", 1);
            }
            if (false === @file_put_contents($repositoryFile, $repositoryContent)) {
                throw new IOException("Impossible to write entity class file (" . $repositoryFile . ").", 1);
            }
            /*
             * Force Zend OPcache to reset file
             */
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file, true);
                opcache_invalidate($repositoryFile, true);
            }
            if (function_exists('apcu_clear_cache')) {
                apcu_clear_cache();
            }

            \clearstatcache(true, $file);
            \clearstatcache(true, $repositoryFile);

            return true;
        }
        return false;
    }

    public function getSourceClassPath(): string
    {
        $folder = $this->getGeneratedEntitiesFolder();
        return $folder . DIRECTORY_SEPARATOR . $this->nodeType->getSourceEntityClassName() . '.php';
    }

    public function getRepositoryClassPath(): string
    {
        $folder = $this->getGeneratedRepositoriesFolder();
        return $folder . DIRECTORY_SEPARATOR . $this->nodeType->getSourceEntityClassName() . 'Repository.php';
    }

    /**
     * Clear doctrine metadata cache and
     * regenerate entity class file.
     *
     * @return $this
     */
    public function updateSchema(): NodeTypeHandler
    {
        $this->regenerateEntityClass();
        $this->exportNodeTypeJsonFile();

        return $this;
    }

    /**
     * Delete and recreate entity class file.
     */
    public function regenerateEntityClass(): NodeTypeHandler
    {
        $this->removeSourceEntityClass();
        $this->generateSourceEntityClass();
        if (null !== $this->nodeType) {
            $this->apiResourceGenerator->generate($this->nodeType);
        }

        return $this;
    }

    /**
     * Delete node-type class from database.
     *
     * @return $this
     */
    public function deleteSchema(): NodeTypeHandler
    {
        if (null !== $this->nodeType) {
            $this->apiResourceGenerator->remove($this->nodeType);
        }
        $this->removeSourceEntityClass();
        $this->removeNodeTypeJsonFile();

        return $this;
    }

    /**
     * Delete node-type inherited nodes and its database schema
     * before removing it from node-types table.
     *
     * @return $this
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function deleteWithAssociations(): NodeTypeHandler
    {
        /*
         * Delete every nodes
         */
        $nodes = $this->objectManager
            ->getRepository(Node::class)
            ->setDisplayingNotPublishedNodes(true)
            ->findBy([
                'nodeType' => $this->getNodeType(),
            ]);

        /** @var Node $node */
        foreach ($nodes as $node) {
            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->removeWithChildrenAndAssociations();
        }

        /*
         * Remove node type
         */
        $this->objectManager->remove($this->getNodeType());
        $this->objectManager->flush();

        /*
         * Remove class and database table
         */
        $this->deleteSchema();

        return $this;
    }

    /**
     * Reset current node-type fields positions.
     *
     * @param bool $setPositions
     * @return float Return the next position after the **last** field
     */
    public function cleanPositions(bool $setPositions = false): float
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);
        $fields = $this->nodeType->getFields()->matching($criteria);
        $i = 1;
        /** @var NodeTypeField $field */
        foreach ($fields as $field) {
            $field->setPosition($i);
            $i++;
        }

        return $i;
    }
}
