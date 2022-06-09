<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Yaml\Yaml;

class GenerateApiResourceCommand extends Command
{
    protected ManagerRegistry $managerRegistry;
    protected ParameterBagInterface $parameterBag;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ManagerRegistry $managerRegistry, ParameterBagInterface $parameterBag)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->parameterBag = $parameterBag;
    }


    protected function configure(): void
    {
        $this->setName('generate:api-resources')
            ->setDescription('Generate node-sources entities API Platform resource files.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var NodeType[] $nodeTypes */
        $nodeTypes = $this->managerRegistry
            ->getRepository(NodeType::class)
            ->findAll();
        $filesystem = new Filesystem();
        $projectDir = $this->parameterBag->get('kernel.project_dir');

        if (!is_string($projectDir)) {
            throw new LogicException('kernel.project_dir parameter does not exist.');
        }
        if (!$filesystem->exists($projectDir)) {
            throw new LogicException($projectDir . ' folder does not exist.');
        }

        $directory = $projectDir . '/config/api_resources';
        if (!$filesystem->exists($directory)) {
            throw new LogicException($directory . ' folder does not exist.');
        }

        if (count($nodeTypes) > 0) {
            foreach ($nodeTypes as $nt) {
                $resourcePath = $directory . '/' . (new UnicodeString($nt->getName()))
                        ->lower()
                        ->prepend('ns')
                        ->append('.yml')
                        ->toString();
                if (!$filesystem->exists($resourcePath)) {
                    $filesystem->dumpFile(
                        $resourcePath,
                        Yaml::dump($this->getApiResourceDefinition($nt), 6)
                    );
                    $io->writeln("* API resource <info>" . $resourcePath . "</info> has been generated.");
                } else {
                    $io->writeln("* API resource <info>" . $resourcePath . "</info> already exists, left untouched.");
                }
            }
            return 0;
        } else {
            $io->error('No available node-types…');
            return 1;
        }
    }

    protected function getApiResourceDefinition(NodeTypeInterface $nodeType): array
    {
        $fqcn = (new UnicodeString($nodeType->getSourceEntityFullQualifiedClassName()))
            ->trimStart('\\')
            ->toString();

        return [ $fqcn => [
            'iri' => $nodeType->getName(),
            'shortName' => $nodeType->getName(),
            'collectionOperations' => $this->getCollectionOperations($nodeType),
            'itemOperations' => $this->getItemOperations($nodeType),
        ]];
    }

    protected function getCollectionOperations(NodeTypeInterface $nodeType): array
    {
        if ($nodeType->isReachable()) {
            $groups = [
                "nodes_sources_base",
                "nodes_sources_default",
                "urls",
                "tag_base",
                "translation_base",
                "document_display",
                ...$this->getGroupedFieldsSerializationGroups($nodeType)
            ];
            return [
                'get' => [
                    'method' => 'GET',
                    'normalization_context' => [
                        'groups' => array_values(array_filter(array_unique($groups)))
                    ],
                ]
            ];
        }
        return [];
    }

    protected function getItemOperations(NodeTypeInterface $nodeType): array
    {
        $groups = [
            "nodes_sources",
            "urls",
            "tag_base",
            "translation_base",
            "document_display",
            ...$this->getGroupedFieldsSerializationGroups($nodeType)
        ];
        $operations = [
            'get' => [
                'method' => 'GET',
                'normalization_context' => [
                    'groups' => array_values(array_filter(array_unique($groups)))
                ],
            ]
        ];

        /*
         * Create itemOperation for WebResponseController action
         */
        if ($nodeType->isReachable()) {
            $operations['getByPath'] = [
                'method' => 'GET',
                'normalization_context' => [
                    'enable_max_depth' => true,
                    'groups' => array_merge(array_values(array_filter(array_unique($groups))), [
                        'web_response',
                        'position',
                        'walker',
                        'walker_level',
                        'walker_metadata',
                        'meta',
                        'children',
                        'children_count',
                    ])
                ],
            ];
        }

        return $operations;
    }

    protected function getGroupedFieldsSerializationGroups(NodeTypeInterface $nodeType): array
    {
        $groups = [];
        foreach ($nodeType->getFields() as $field) {
            if (null !== $field->getGroupNameCanonical()) {
                $groups[] = (new UnicodeString($field->getGroupNameCanonical()))
                    ->lower()
                    ->snake()
                    ->prepend('nodes_sources_')
                    ->toString()
                ;
            }
        }
        return $groups;
    }
}
