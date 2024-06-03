<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use RZ\Roadiz\CoreBundle\Message\UpdateDoctrineSchemaMessage;
use RZ\Roadiz\CoreBundle\Message\UpdateNodeTypeSchemaMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpdateNodeTypeSchemaMessageHandler implements MessageHandlerInterface
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

    public function __invoke(UpdateNodeTypeSchemaMessage $message): void
    {
        $nodeType = $this->managerRegistry->getRepository(NodeType::class)->find($message->getNodeTypeId());

        if (!$nodeType instanceof NodeType) {
            throw new UnrecoverableMessageHandlingException('NodeType does not exist');
        }

        /** @var NodeTypeHandler $handler */
        $handler = $this->handlerFactory->getHandler($nodeType);
        $handler->updateSchema();

        $this->managerRegistry->getManager()->clear();
        $this->messageBus->dispatch(
            (new Envelope(new UpdateDoctrineSchemaMessage()))
        );
    }
}
