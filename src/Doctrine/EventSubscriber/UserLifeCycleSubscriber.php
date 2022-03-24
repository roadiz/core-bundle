<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Event\User\UserCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserDisabledEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserEnabledEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserPasswordChangedEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserUpdatedEvent;
use RZ\Roadiz\CoreBundle\Security\User\UserViewer;
use RZ\Roadiz\Utils\MediaFinders\FacebookPictureFinder;
use RZ\Roadiz\Random\TokenGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserLifeCycleSubscriber implements EventSubscriber
{
    private UserViewer $userViewer;
    private EventDispatcherInterface $dispatcher;
    private EncoderFactoryInterface $encoderFactory;
    private UrlGeneratorInterface $urlGenerator;
    private LoggerInterface $logger;

    /**
     * @param UserViewer $userViewer
     * @param EventDispatcherInterface $dispatcher
     * @param EncoderFactoryInterface $encoderFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        UserViewer $userViewer,
        EventDispatcherInterface $dispatcher,
        EncoderFactoryInterface $encoderFactory,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->userViewer = $userViewer;
        $this->dispatcher = $dispatcher;
        $this->encoderFactory = $encoderFactory;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::prePersist,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    /**
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $user = $event->getEntity();
        if ($user instanceof User) {
            if (
                $event->hasChangedField('enabled') &&
                true === $event->getNewValue('enabled')
            ) {
                $userEvent = new UserEnabledEvent($user);
                $this->dispatcher->dispatch($userEvent);
            }

            if (
                $event->hasChangedField('enabled') &&
                false === $event->getNewValue('enabled')
            ) {
                $userEvent = new UserDisabledEvent($user);
                $this->dispatcher->dispatch($userEvent);
            }

            if ($event->hasChangedField('facebookName')) {
                if ('' != $event->getNewValue('facebookName')) {
                    try {
                        $facebook = new FacebookPictureFinder($user->getFacebookName());
                        $url = $facebook->getPictureUrl();
                        $user->setPictureUrl($url);
                    } catch (\Exception $e) {
                        $user->setFacebookName('');
                        $user->setPictureUrl($user->getGravatarUrl());
                    }
                } else {
                    $user->setPictureUrl($user->getGravatarUrl());
                }
            }
            /*
             * Encode user password
             */
            if (
                $event->hasChangedField('password') &&
                null !== $user->getPlainPassword() &&
                '' !== $user->getPlainPassword()
            ) {
                $this->setPassword($user, $user->getPlainPassword());
                $userEvent = new UserPasswordChangedEvent($user);
                $this->dispatcher->dispatch($userEvent);
            }
        }
    }

    /**
     * @param User $user
     * @param string|null $plainPassword
     */
    protected function setPassword(User $user, ?string $plainPassword)
    {
        if (null !== $plainPassword) {
            $encoder = $this->encoderFactory->getEncoder($user);
            $encodedPassword = $encoder->encodePassword(
                $plainPassword,
                $user->getSalt()
            );
            $user->setPassword($encodedPassword);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $user = $event->getEntity();
        if ($user instanceof User) {
            $userEvent = new UserUpdatedEvent($user);
            $this->dispatcher->dispatch($userEvent);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postRemove(LifecycleEventArgs $event)
    {
        $user = $event->getEntity();
        if ($user instanceof User) {
            $userEvent = new UserDeletedEvent($user);
            $this->dispatcher->dispatch($userEvent);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws \Exception
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $user = $event->getEntity();
        if ($user instanceof User) {
            $userEvent = new UserCreatedEvent($user);
            $this->dispatcher->dispatch($userEvent);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $user = $event->getEntity();
        if ($user instanceof User) {
            if (
                $user->willSendCreationConfirmationEmail() &&
                (null === $user->getPlainPassword() ||
                $user->getPlainPassword() === '')
            ) {
                /*
                 * Do not generate password for new users
                 * just send them a password reset link.
                 */
                $tokenGenerator = new TokenGenerator($this->logger);
                $user->setCredentialsExpired(true);
                $user->setPasswordRequestedAt(new \DateTime());
                $user->setConfirmationToken($tokenGenerator->generateToken());

                $this->userViewer->setUser($user);
                $this->userViewer->sendPasswordResetLink(
                    $this->urlGenerator,
                    'loginResetPage',
                    '@RoadizCore/email/users/welcome_user_email.html.twig',
                    '@RoadizCore/email/users/welcome_user_email.txt.twig'
                );
            } else {
                $this->setPassword($user, $user->getPlainPassword());
            }

            /*
             * Force a Gravatar image if not defined
             */
            if ($user->getPictureUrl() == '') {
                $user->setPictureUrl($user->getGravatarUrl());
            }
        }
    }
}
