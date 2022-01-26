<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\CustomForm\CustomFormHelperFactory;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Mailer\EmailManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
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
    private Settings $settingsBag;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;
    private CustomFormHelperFactory $customFormHelperFactory;

    public function __construct(
        EmailManager $emailManager,
        Settings $settingsBag,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        CustomFormHelperFactory $customFormHelperFactory
    ) {
        $this->emailManager = $emailManager;
        $this->settingsBag = $settingsBag;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->customFormHelperFactory = $customFormHelperFactory;
    }

    /**
     * @param Request $request
     * @param int $customFormId
     * @return Response
     * @throws \Twig\Error\RuntimeError
     */
    public function addAction(Request $request, int $customFormId): Response
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
     * @param int $customFormId
     * @return Response
     */
    public function sentAction(Request $request, int $customFormId): Response
    {
        $assignation = [];
        /** @var CustomForm|null $customForm */
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
     * @param Response $response
     * @param boolean          $forceExpanded
     * @param string|null      $emailSender
     *
     * @return array|Response
     * @throws Exception
     */
    public function prepareAndHandleCustomFormAssignation(
        Request $request,
        CustomForm $customFormsEntity,
        Response $response,
        bool $forceExpanded = false,
        ?string $emailSender = null
    ) {
        $assignation = [];
        $assignation['customForm'] = $customFormsEntity;
        $assignation['fields'] = $customFormsEntity->getFields();
        $helper = $this->customFormHelperFactory->createHelper($customFormsEntity);
        $form = $helper->getForm(
            $request,
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
                $assignation["emailFields"] = array_merge(
                    $assignation["emailFields"],
                    $answer->toArray(false)
                );

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

                return $response;
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $assignation['form'] = $form->createView();
        return $assignation;
    }
}
