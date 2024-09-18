<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\User;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Mailer\EmailManagerFactory;
use RZ\Roadiz\CoreBundle\Security\LoginLink\LoginLinkSenderInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserViewer
{
    public function __construct(
        private readonly Settings $settingsBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly EmailManagerFactory $emailManagerFactory,
        private readonly LoggerInterface $logger,
        private readonly LoginLinkSenderInterface $loginLinkSender
    ) {
    }

    /**
     * Send email to reset user password.
     *
     * @param User $user
     * @param object|string $route
     * @param string $htmlTemplate
     * @param string $txtTemplate
     *
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetLink(
        User $user,
        object|string $route = 'loginResetPage',
        string $htmlTemplate = '@RoadizCore/email/users/reset_password_email.html.twig',
        string $txtTemplate = '@RoadizCore/email/users/reset_password_email.txt.twig'
    ): bool {
        $emailManager = $this->emailManagerFactory->create();
        $emailContact = $this->getContactEmail();
        $siteName = $this->getSiteName();

        if (is_string($route)) {
            $resetLink = $this->urlGenerator->generate(
                $route,
                [
                    'token' => $user->getConfirmationToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } else {
            $resetLink = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $route,
                    'token' => $user->getConfirmationToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        $emailManager->setAssignation([
            'resetLink' => $resetLink,
            'user' => $user,
            'site' => $siteName,
            'mailContact' => $emailContact,
        ]);
        $emailManager->setEmailTemplate($htmlTemplate);
        $emailManager->setEmailPlainTextTemplate($txtTemplate);
        $emailManager->setSubject($this->translator->trans(
            'reset.password.request'
        ));

        try {
            $emailManager->setReceiver($user->getEmail());
            $emailManager->setSender([$emailContact => $siteName]);

            // Send the message
            $emailManager->send();
            return true;
        } catch (\Exception $e) {
            // Silent error not to prevent user creation if mailer is not configured
            $this->logger->error('Unable to send password reset link', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'entity' => $user,
            ]);
            return false;
        }
    }

    /**
     * @deprecated Use LoginLinkSenderInterface::sendLoginLink instead
     */
    public function sendLoginLink(
        UserInterface $user,
        LoginLinkDetails $loginLinkDetails,
        string $htmlTemplate = '@RoadizCore/email/users/login_link_email.html.twig',
        string $txtTemplate = '@RoadizCore/email/users/login_link_email.txt.twig'
    ): void {
        $this->loginLinkSender->sendLoginLink($user, $loginLinkDetails);
    }

    /**
     * @return string
     */
    protected function getContactEmail(): string
    {
        $emailContact = $this->settingsBag->get('email_sender') ?? '';
        if (empty($emailContact)) {
            $emailContact = "noreply@roadiz.io";
        }

        return $emailContact;
    }

    /**
     * @return string
     */
    protected function getSiteName(): string
    {
        $siteName = $this->settingsBag->get('site_name') ?? '';
        if (empty($siteName)) {
            $siteName = "Unnamed site";
        }

        return $siteName;
    }
}
