<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @see https://github.com/thrace-project/form-bundle/blob/master/Validator/Constraint/RecaptchaValidator.php
 */
class RecaptchaValidator extends ConstraintValidator implements RecaptchaServiceInterface
{
    protected RequestStack $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param mixed $data
     * @param Constraint $constraint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @see \Symfony\Component\Validator\ConstraintValidator::validate()
     */
    public function validate($data, Constraint $constraint)
    {
        if ($constraint instanceof Recaptcha) {
            $propertyPath = $this->context->getPropertyPath();

            if (null === $this->requestStack->getCurrentRequest()) {
                $this->context->buildViolation('Request is not defined')
                    ->atPath($propertyPath)
                    ->addViolation();
            }

            $responseField = $this->requestStack->getCurrentRequest()->get($constraint->fieldName);

            if (empty($responseField)) {
                $this->context->buildViolation($constraint->emptyMessage)
                    ->atPath($propertyPath)
                    ->addViolation();
            } elseif (true !== $response = $this->check($constraint->privateKey, $responseField, $constraint->verifyUrl)) {
                $this->context->buildViolation($constraint->invalidMessage)
                    ->atPath($propertyPath)
                    ->addViolation();

                if (is_array($response)) {
                    foreach ($response as $errorCode) {
                        $this->context->buildViolation($errorCode)
                            ->atPath($propertyPath)
                            ->addViolation();
                    }
                } elseif (is_string($response)) {
                    $this->context->buildViolation($response)
                        ->atPath($propertyPath)
                        ->addViolation();
                }
            }
        }
    }

    /**
     * Makes a request to recaptcha service and checks if recaptcha field is valid.
     * Returns Google error-codes if recaptcha fails.
     *
     * @param string $privateKey
     * @param string $responseValue
     * @param string $verifyUrl
     * @return true|string|array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function check(
        string $privateKey,
        string $responseValue,
        string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify'
    ) {
        $data = [
            'secret' => $privateKey,
            'response' => $responseValue,
        ];

        $client = new Client();
        $response = $client->post($verifyUrl, [
            'form_params' => $data,
            'connect_timeout' => 10,
            'timeout' => 10,
            'headers' => [
                'Accept'     => 'application/json',
            ]
        ]);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);

        return (isset($jsonResponse['success']) && $jsonResponse['success'] === true) ?
            (true) :
            ($jsonResponse['error-codes']);
    }
}
