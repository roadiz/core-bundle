<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\CustomForm\CustomFormHelper;
use RZ\Roadiz\CoreBundle\Document\PrivateDocumentFactory;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Entity\CustomFormFieldAttribute;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Form\CustomFormsType;
use RZ\Roadiz\CoreBundle\Mailer\EmailManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CustomFormController extends AbstractController
{
    private EmailManager $emailManager;
    private PrivateDocumentFactory $privateDocumentFactory;
    private Settings $settingsBag;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;

    public function __construct(
        EmailManager $emailManager,
        PrivateDocumentFactory $privateDocumentFactory,
        Settings $settingsBag,
        LoggerInterface $logger,
        TranslatorInterface $translator
    ) {
        $this->emailManager = $emailManager;
        $this->privateDocumentFactory = $privateDocumentFactory;
        $this->settingsBag = $settingsBag;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @param int $customFormId
     *
     * @return Response
     * @throws \Twig\Error\RuntimeError
     */
    public function addAction(Request $request, int $customFormId)
    {
        /** @var CustomForm $customForm */
        $customForm = $this->getDoctrine()->getRepository(CustomForm::class)->find($customFormId);

        if (null !== $customForm && $customForm->isFormStillOpen()) {
            $mixed = $this->prepareAndHandleCustomFormAssignation(
                $request,
                $customForm,
                new RedirectResponse(
                    $this->generateUrl(
                        'customFormSentAction',
                        ["customFormId" => $customFormId]
                    )
                )
            );

            if ($mixed instanceof RedirectResponse) {
                $mixed->prepare($request);
                return $mixed->send();
            } else {
                return $this->render('@RoadizCore/customForm/customForm.html.twig', $mixed);
            }
        }

        throw new ResourceNotFoundException();
    }

    /**
     * @param Request $request
     * @param int     $customFormId
     *
     * @return Response
     */
    public function sentAction(Request $request, int $customFormId)
    {
        $assignation = [];
        $customForm = $this->getDoctrine()->getRepository(CustomForm::class)->find($customFormId);

        if (null !== $customForm) {
            $assignation['customForm'] = $customForm;

            return $this->render('@RoadizCore/customForm/customFormSent.html.twig', $assignation);
        }

        throw new ResourceNotFoundException();
    }

    /**
     * Send an answer form by Email.
     *
     * @param array $assignation
     * @param string|array|null $receiver
     * @return bool
     * @throws Exception
     */
    public function sendAnswer(
        array $assignation,
        $receiver
    ): bool {
        $defaultSender = $this->settingsBag->get('email_sender');
        $defaultSender = !empty($defaultSender) ? $defaultSender : 'sender@roadiz.io';
        $this->emailManager->setAssignation($assignation);
        $this->emailManager->setEmailTemplate('@RoadizCore/email/forms/answerForm.html.twig');
        $this->emailManager->setEmailPlainTextTemplate('@RoadizCore/email/forms/answerForm.txt.twig');
        $this->emailManager->setSubject($assignation['title']);
        $this->emailManager->setEmailTitle($assignation['title']);
        $this->emailManager->setSender($defaultSender);

        if (empty($receiver)) {
            $this->emailManager->setReceiver($defaultSender);
        } else {
            $this->emailManager->setReceiver($receiver);
        }

        // Send the message
        $this->emailManager->send();
        return true;
    }

    /**
     * Add a custom form answer into database.
     *
     * @param array         $data Data array from POST form
     * @param CustomForm    $customForm
     * @param EntityManagerInterface $em
     *
     * @return array $fieldsData
     * @deprecated Use \RZ\Roadiz\Utils\CustomForm\CustomFormHelper to transform Form to CustomFormAnswer.
     */
    public function addCustomFormAnswer(array $data, CustomForm $customForm, EntityManagerInterface $em)
    {
        $now = new DateTime('NOW');
        $answer = new CustomFormAnswer();
        $answer->setIp($data["ip"]);
        $answer->setSubmittedAt($now);
        $answer->setCustomForm($customForm);

        $fieldsData = [
            ["name" => "ip.address", "value" => $data["ip"]],
            ["name" => "submittedAt", "value" => $now],
        ];

        $em->persist($answer);

        foreach ($customForm->getFields() as $field) {
            $fieldAttr = new CustomFormFieldAttribute();
            $fieldAttr->setCustomFormAnswer($answer);
            $fieldAttr->setCustomFormField($field);

            if (isset($data[$field->getName()])) {
                $fieldValue = $data[$field->getName()];
                if ($fieldValue instanceof DateTime) {
                    $strDate = $fieldValue->format('Y-m-d H:i:s');

                    $fieldAttr->setValue($strDate);
                    $fieldsData[] = ["name" => $field->getLabel(), "value" => $strDate];
                } elseif (is_array($fieldValue)) {
                    $values = $fieldValue;
                    $values = array_map('trim', $values);
                    $values = array_map('strip_tags', $values);

                    $displayValues = implode(CustomFormHelper::ARRAY_SEPARATOR, $values);
                    $fieldAttr->setValue($displayValues);
                    $fieldsData[] = ["name" => $field->getLabel(), "value" => $displayValues];
                } else {
                    $fieldAttr->setValue(strip_tags($fieldValue));
                    $fieldsData[] = ["name" => $field->getLabel(), "value" => $fieldValue];
                }
            }
            $em->persist($fieldAttr);
        }

        $em->flush();

        return $fieldsData;
    }

    /**
     * @param Request    $request
     * @param CustomForm $customForm
     * @param boolean    $forceExpanded
     *
     * @return FormInterface
     */
    public function buildForm(
        Request $request,
        CustomForm $customForm,
        bool $forceExpanded = false
    ) {
        $defaults = $request->query->all();
        return $this->createForm(CustomFormsType::class, $defaults, [
            'recaptcha_public_key' => $this->settingsBag->get('recaptcha_public_key'),
            'recaptcha_private_key' => $this->settingsBag->get('recaptcha_private_key'),
            'request' => $request,
            'customForm' => $customForm,
            'forceExpanded' => $forceExpanded,
        ]);
    }

    /**
     * Prepare and handle a CustomForm Form then send a confirm email.
     *
     * * This method will return an assignation **array** if form is not validated.
     *     * customForm
     *     * fields
     *     * form
     * * If form is validated, **RedirectResponse** will be returned.
     *
     * @param Request          $request
     * @param CustomForm       $customFormsEntity
     * @param RedirectResponse $redirection
     * @param boolean          $forceExpanded
     * @param string|null      $emailSender
     *
     * @return array|RedirectResponse
     * @throws Exception
     */
    public function prepareAndHandleCustomFormAssignation(
        Request $request,
        CustomForm $customFormsEntity,
        RedirectResponse $redirection,
        bool $forceExpanded = false,
        ?string $emailSender = null
    ) {
        $assignation = [];
        $assignation['customForm'] = $customFormsEntity;
        $assignation['fields'] = $customFormsEntity->getFields();
        $helper = new CustomFormHelper(
            $this->getDoctrine()->getManager(),
            $customFormsEntity,
            $this->privateDocumentFactory
        );
        $form = $this->buildForm(
            $request,
            $customFormsEntity,
            $forceExpanded
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /*
                 * Parse form data and create answer.
                 */
                $answer = $helper->parseAnswerFormData($form, null, $request->getClientIp());

                /*
                 * Prepare field assignation for email content.
                 */
                $assignation["emailFields"] = [
                    ["name" => "ip.address", "value" => $answer->getIp()],
                    ["name" => "submittedAt", "value" => $answer->getSubmittedAt()->format('Y-m-d H:i:s')],
                ];
                $assignation["emailFields"] = array_merge($assignation["emailFields"], $answer->toArray(false));

                $msg = $this->translator->trans(
                    'customForm.%name%.send',
                    ['%name%' => $customFormsEntity->getDisplayName()]
                );

                $session = $request->getSession();
                if ($session instanceof Session) {
                    $session->getFlashBag()->add('confirm', $msg);
                }
                $this->logger->info($msg);

                $assignation['title'] = $this->translator->trans(
                    'new.answer.form.%site%',
                    ['%site%' => $customFormsEntity->getDisplayName()]
                );

                if (null !== $emailSender && false !== filter_var($emailSender, FILTER_VALIDATE_EMAIL)) {
                    $assignation['mailContact'] = $emailSender;
                } else {
                    $assignation['mailContact'] = $this->settingsBag->get('email_sender');
                }

                /*
                 * Send answer notification
                 */
                $receiver = array_filter(
                    array_map('trim', explode(',', $customFormsEntity->getEmail() ?? ''))
                );
                $receiver = array_map(function (string $email) {
                    return new Address($email);
                }, $receiver);
                $this->sendAnswer(
                    [
                        'mailContact' => $assignation['mailContact'],
                        'fields' => $assignation["emailFields"],
                        'customForm' => $customFormsEntity,
                        'title' => $this->translator->trans(
                            'new.answer.form.%site%',
                            ['%site%' => $customFormsEntity->getDisplayName()]
                        ),
                    ],
                    $receiver
                );

                return $redirection;
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $assignation['form'] = $form->createView();

        return $assignation;
    }
}
