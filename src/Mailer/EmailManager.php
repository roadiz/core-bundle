<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use InlineStyle\InlineStyle;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\Utils\UrlGenerators\DocumentUrlGeneratorInterface;
use Solarium\QueryType\Update\Query\Command\Add;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
    /** @var Address[]|null  */
    protected ?array $receiver = null;
    /** @var Address[]|null  */
    protected ?array $sender = null;
    protected ?Address $origin = null;
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
            ->to(...$this->getReceiver())
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
            $this->message->replyTo(...$this->getSender());
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
     * @return null|Address[]
     */
    public function getReceiver(): ?array
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
        if (is_array($this->getReceiver()) && count($this->getReceiver()) > 0) {
            return $this->getReceiver()[0]->getAddress();
        }

        return null;
    }

    /**
     * Sets the value of receiver.
     *
     * @param string|array<string, string>|array<Address> $receiver the receiver
     *
     * @return EmailManager
     * @throws \Exception
     */
    public function setReceiver($receiver)
    {
        if (\is_string($receiver)) {
            $this->receiver = [new Address($receiver)];
        } elseif (\is_array($receiver)) {
            $this->receiver = [];
            foreach ($receiver as $email => $name) {
                if ($name instanceof Address) {
                    $this->receiver[] = $name;
                } elseif (\is_string($email)) {
                    $this->receiver[] = new Address($email, $name);
                } else {
                    $this->receiver[] = new Address($name);
                }
            }
        }

        return $this;
    }

    /**
     * Message virtual sender email.
     *
     * This email will be used as ReplyTo: and ReturnPath:
     *
     * @return null|Address[]
     */
    public function getSender(): ?array
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
        if (\is_array($this->sender) && \count($this->sender) > 0) {
            return $this->sender[0]->getAddress();
        }

        return null;
    }

    /**
     * Sets the value of sender.
     *
     * @param string|array<string, string> $sender
     * @return EmailManager
     * @throws \Exception
     */
    public function setSender($sender)
    {
        if (\is_string($sender)) {
            $this->sender = [new Address($sender)];
        } elseif (\is_array($sender)) {
            $this->sender = [];
            foreach ($sender as $email => $name) {
                if ($name instanceof Address) {
                    $this->sender[] = $name;
                } elseif (\is_string($email)) {
                    $this->sender[] = new Address($email, $name);
                } else {
                    $this->sender[] = new Address($name);
                }
            }
        } else {
            throw new \InvalidArgumentException('Sender should be string or array<string>');
        }

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
     * @return null|Address
     */
    public function getOrigin(): ?Address
    {
        $defaultSender = 'origin@roadiz.io';
        if (null !== $this->settingsBag && $this->settingsBag->get('email_sender')) {
            $defaultSender = $this->settingsBag->get('email_sender');
        }
        return $this->origin ?? new Address($defaultSender);
    }

    /**
     * @param string $origin
     * @return EmailManager
     */
    public function setOrigin(string $origin)
    {
        $this->origin = new Address($origin);
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
