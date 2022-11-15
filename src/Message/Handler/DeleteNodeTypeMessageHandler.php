<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use RZ\Roadiz\CoreBundle\Message\DeleteNodeTypeMessage;
use RZ\Roadiz\CoreBundle\Message\UpdateDoctrineSchemaMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final class DeleteNodeTypeMessageHandler implements MessageHandlerInterface
{
    private ManagerRegistry $managerRegistry;
    private HandlerFactoryInterface $handlerFactory;
    private MessageBusInterface $messageBus;

    public function __construct(ManagerRegistry $managerRegistry, HandlerFactoryInterface $handlerFactory, MessageBusInterface $messageBus)
    {
        $this->managerRegistry = $managerRegistry;
        $this->handlerFactory = $handlerFactory;
        $this->messageBus = $messageBus;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(DeleteNodeTypeMessage $message): void
    {
        $nodeType = $this->managerRegistry->getRepository(NodeType::class)->find($message->getNodeTypeId());

        if (!$nodeType instanceof NodeType) {
            throw new \InvalidArgumentException('NodeType does not exist');
        }

        /** @var NodeTypeHandler $handler */
        $handler = $this->handlerFactory->getHandler($nodeType);
        $handler->deleteWithAssociations();

        $this->messageBus->dispatch(
            (new Envelope(new UpdateDoctrineSchemaMessage()))
        );
        $this->managerRegistry->getManager()->clear();
    }
}