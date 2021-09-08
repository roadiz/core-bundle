<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Node\UniversalDataDuplicator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class NodeClearTagCommand extends Command
{
    protected SymfonyStyle $io;
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure()
    {
        $this->setName('nodes:clear-tag')
            ->addArgument('tagId', InputArgument::REQUIRED, 'Tag ID to delete nodes from.')
            ->setDescription('Delete every Nodes linked with a given Tag. <info>Danger zone</info>')
        ;
    }

    protected function getNodeQueryBuilder(Tag $tag): QueryBuilder
    {
        $qb = $this->managerRegistry->getRepository(Node::class)->createQueryBuilder('n');
        return $qb->innerJoin('n.tags', 't')
            ->andWhere($qb->expr()->eq('t.id', ':tagId'))
            ->setParameter(':tagId', $tag);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->managerRegistry->getManagerForClass(Node::class);
        $this->io = new SymfonyStyle($input, $output);

        $tagId = (int) $input->getArgument('tagId');
        if ($tagId <= 0) {
            throw new \InvalidArgumentException('Tag ID must be a valid ID');
        }
        /** @var Tag|null $tag */
        $tag = $em->find(Tag::class, $tagId);
        if ($tag === null) {
            throw new \InvalidArgumentException(sprintf('Tag #%d does not exist.', $tagId));
        }

        $batchSize = 20;
        $i = 0;

        $count = $this->getNodeQueryBuilder($tag)
            ->select('count(n)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($count <= 0) {
            $this->io->warning('No nodes were found linked with this tag.');
            return 0;
        }

        if ($this->io->askQuestion(new ConfirmationQuestion(
            sprintf('Are you sure to delete permanently %d nodes?', $count),
            false
        ))) {
            $results = $this->getNodeQueryBuilder($tag)
                ->select('n')
                ->getQuery()
                ->getResult();

            $this->io->progressStart($count);
            /** @var Node $node */
            foreach ($results as $node) {
                $em->remove($node);
                if (($i % $batchSize) === 0) {
                    $em->flush(); // Executes all updates.
                }
                ++$i;
                $this->io->progressAdvance();
            }
            $em->flush();
            $this->io->progressFinish();
        }

        return 0;
    }
}
