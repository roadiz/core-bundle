<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use InlineStyle\InlineStyle;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\Utils\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @package RZ\Roadiz\Utils
 */
class EmailManager
{
    protected ?string $subject = null;
    protected ?string $emailTitle = null;
    protected ?string $emailType = null;
    /** @var string|array|null  */
    protected $receiver = null;
    /** @var string|array|null  */
    protected $sender = null;
    protected ?string $origin = null;
    protected string $successMessage = 'email.successfully.sent';
    protected string $failMessage = 'email.has.errors';
    protected TranslatorInterface $translator;
    protected Environment $templating;
    protected MailerInterface $mailer;
    protected ?string $emailTemplate = null;
    protected ?string $emailPlainTextTemplate = null;
    protected string $emailStylesheet;
    protected RequestStack $requestStack;
    protected array $assignation;
    protected ?Email $message;
    protected ?Settings $settingsBag;
    protected ?DocumentUrlGeneratorInterface $documentUrlGenerator;

    /**
     * @param RequestStack $requestStack
     * @param TranslatorInterface                $translator
     * @param Environment                        $templating
     * @param MailerInterface                    $mailer
     * @param Settings|null                      $settingsBag
     * @param DocumentUrlGeneratorInterface|null $documentUrlGenerator
     */
    public function __construct(
        RequestStack $requestStack,
        TranslatorInterface $translator,
        Environment $templating,
        MailerInterface $mailer,
        ?Settings $settingsBag = null,
        ?DocumentUrlGeneratorInterface $documentUrlGenerator = null
    ) {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->assignation = [];
        $this->message = null;

        /*
         * Sets a default CSS for emails.
         */
        $this->emailStylesheet = dirname(__DIR__) . '/../css/transactionalStyles.css';
        $this->settingsBag = $settingsBag;
        $this->documentUrlGenerator = $documentUrlGenerator;
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderHtmlEmailBody(): string
    {
        return $this->templating->render($this->getEmailTemplate(), $this->assignation);
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderHtmlEmailBodyWithCss(): string
    {
        if (null !== $this->getEmailStylesheet()) {
            $htmldoc = new InlineStyle($this->renderHtmlEmailBody());
            $htmldoc->applyStylesheet(file_get_contents(
                $this->getEmailStylesheet()
            ));

            return $htmldoc->getHTML();
        }

        return $this->renderHtmlEmailBody();
    }

    /**
     * @return string
     */
    public function renderPlainTextEmailBody(): string
    {
        return $this->templating->render($this->getEmailPlainTextTemplate(), $this->assignation);
    }

    /**
     * Added mainColor and headerImageSrc assignation
     * to display email header.
     *
     * @return EmailManager
     */
    public function appendWebsiteIcon()
    {
        if (empty($this->assignation['mainColor']) && null !== $this->settingsBag) {
            $this->assignation['mainColor'] = $this->settingsBag->get('main_color');
        }

        if (empty($this->assignation['headerImageSrc']) && null !== $this->settingsBag) {
            $adminImage = $this->settingsBag->getDocument('admin_image');
            if ($adminImage instanceof DocumentInterface && null !== $this->documentUrlGenerator) {
                $this->documentUrlGenerator->setDocument($adminImage);
                $this->assignation['headerImageSrc'] = $this->documentUrlGenerator->getUrl(true);
            }
        }

        return $this;
    }

    /**
     * @return Email
     */
    public function createMessage(): Email
    {
        $this->appendWebsiteIcon();

        $this->message = (new Email())
            ->subject($this->getSubject())
            ->from($this->getOrigin())
            ->to($this->getReceiver())
            // Force using string and only one email
            ->returnPath($this->getSenderEmail());

        if (null !== $this->getEmailTemplate()) {
            $this->message->html($this->renderHtmlEmailBodyWithCss());
        }
        if (null !== $this->getEmailPlainTextTemplate()) {
            $this->message->text($this->renderPlainTextEmailBody());
        }

        /*
         * Use sender email in ReplyTo: header only
         * to keep From: header with a know domain email.
         */
        if (null !== $this->getSender()) {
            $this->message->replyTo($this->getSender());
        }

        return $this->message;
    }

    /**
     * Send email.
     *
     * @throws \RuntimeException
     * @return void
     */
    public function send()
    {
        if (empty($this->assignation)) {
            throw new \RuntimeException("Can’t send a contact form without data.");
        }

        if (null === $this->message) {
            $this->message = $this->createMessage();
        }

        // Send the message
        $this->mailer->send($this->message);
    }

    /**
     * @return null|string
     */
    public function getSubject(): ?string
    {
        return null !== $this->subject ? trim(strip_tags($this->subject)) : null;
    }

    /**
     * @param null|string $subject
     * @return EmailManager
     */
    public function setSubject(?string $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmailTitle(): ?string
    {
        return null !== $this->emailTitle ? trim(strip_tags($this->emailTitle)) : null;
    }

    /**
     * @param null|string $emailTitle
     * @return EmailManager
     */
    public function setEmailTitle(?string $emailTitle)
    {
        $this->emailTitle = $emailTitle;
        return $this;
    }

    /**
     * Message destination email(s).
     *
     * @return null|array|string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Return only one email as string.
     *
     * @return null|string
     */
    public function getReceiverEmail(): ?string
    {
        if (is_array($this->receiver) && count($this->receiver) > 0) {
            $emails = array_keys($this->receiver);
            return $emails[0];
        }

        return $this->receiver;
    }

    /**
     * Sets the value of receiver.
     *
     * @param string|array $receiver the receiver
     *
     * @return EmailManager
     * @throws \Exception
     */
    public function setReceiver($receiver)
    {
        if (is_string($receiver)) {
            if (false === filter_var($receiver, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Sender must be a valid email address.", 1);
            }
        } elseif (is_array($receiver)) {
            foreach ($receiver as $email => $name) {
                /*
                 * Allow simple array with email as value as well as assoc. array
                 * with email as key and name as value.
                 */
                if (false === filter_var($name, FILTER_VALIDATE_EMAIL) &&
                    false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException("Sender must be a valid email address.", 1);
                }
            }
        }

        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Message virtual sender email.
     *
     * This email will be used as ReplyTo: and ReturnPath:
     *
     * @return null|string
     */
    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * Return only one email as string.
     *
     * @return null|string
     */
    public function getSenderEmail(): ?string
    {
        if (is_array($this->sender) && count($this->sender) > 0) {
            $emails = array_keys($this->sender);
            return $emails[0];
        }

        return $this->sender;
    }

    /**
     * Sets the value of sender.
     *
     * @param string|array $sender the sender
     * @return EmailManager
     * @throws \Exception
     */
    public function setSender($sender)
    {
        if (is_string($sender)) {
            if (false === filter_var($sender, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Sender must be a valid email address.", 1);
            }
        } elseif (is_array($sender)) {
            foreach ($sender as $email => $name) {
                if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException("Sender must be a valid email address.", 1);
                }
            }
        }

        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }

    /**
     * @param string $successMessage
     * @return EmailManager
     */
    public function setSuccessMessage(string $successMessage)
    {
        $this->successMessage = $successMessage;
        return $this;
    }

    /**
     * @return string
     */
    public function getFailMessage(): string
    {
        return $this->failMessage;
    }

    /**
     * @param string $failMessage
     * @return EmailManager
     */
    public function setFailMessage(string $failMessage)
    {
        $this->failMessage = $failMessage;
        return $this;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param TranslatorInterface $translator
     * @return EmailManager
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * @return Environment
     */
    public function getTemplating(): Environment
    {
        return $this->templating;
    }

    /**
     * @param Environment $templating
     * @return EmailManager
     */
    public function setTemplating(Environment $templating)
    {
        $this->templating = $templating;
        return $this;
    }

    /**
     * @return MailerInterface
     */
    public function getMailer(): MailerInterface
    {
        return $this->mailer;
    }

    /**
     * @param MailerInterface $mailer
     * @return EmailManager
     */
    public function setMailer(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailTemplate(): ?string
    {
        return $this->emailTemplate;
    }

    /**
     * @param string|null $emailTemplate
     * @return EmailManager
     */
    public function setEmailTemplate(?string $emailTemplate = null)
    {
        $this->emailTemplate = $emailTemplate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailPlainTextTemplate(): ?string
    {
        return $this->emailPlainTextTemplate;
    }

    /**
     * @param string|null $emailPlainTextTemplate
     * @return EmailManager
     */
    public function setEmailPlainTextTemplate(?string $emailPlainTextTemplate = null)
    {
        $this->emailPlainTextTemplate = $emailPlainTextTemplate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailStylesheet(): ?string
    {
        return $this->emailStylesheet;
    }

    /**
     * @param string|null $emailStylesheet
     * @return EmailManager
     */
    public function setEmailStylesheet(?string $emailStylesheet = null)
    {
        $this->emailStylesheet = $emailStylesheet;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->requestStack->getMainRequest();
    }

    /**
     * Origin is the real From envelop.
     *
     * This must be an email address with a know
     * domain name to be validated on your SMTP server.
     *
     * @return null|string
     */
    public function getOrigin(): ?string
    {
        $defaultSender = 'origin@roadiz.io';
        if (null !== $this->settingsBag && $this->settingsBag->get('email_sender')) {
            $defaultSender = $this->settingsBag->get('email_sender');
        }
        return (null !== $this->origin && $this->origin != "") ? ($this->origin) : ($defaultSender);
    }

    /**
     * @param string $origin
     * @return EmailManager
     */
    public function setOrigin(string $origin)
    {
        if (false === filter_var($origin, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Origin must be a valid email address.", 1);
        }

        $this->origin = $origin;
        return $this;
    }

    /**
     * @return array
     */
    public function getAssignation(): array
    {
        return $this->assignation;
    }

    /**
     * @param array $assignation
     * @return EmailManager
     */
    public function setAssignation(array $assignation)
    {
        $this->assignation = $assignation;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmailType(): ?string
    {
        return $this->emailType;
    }

    /**
     * @param null|string $emailType
     *
     * @return EmailManager
     */
    public function setEmailType(?string $emailType)
    {
        $this->emailType = $emailType;
        return $this;
    }
}
