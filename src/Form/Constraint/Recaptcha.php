<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;

class Recaptcha extends Constraint
{
    /**
     * @var Request
     */
    public $request;
    /**
     * @var string
     */
    public $emptyMessage = 'you_must_show_youre_not_robot';
    /**
     * @var string
     */
    public $invalidMessage = 'recaptcha_is_invalid.try_again';
    /**
     * @var string
     */
    public $privateKey;
    /**
     * @var string
     */
    public $fieldName = 'g-recaptcha-response';
    /**
     * @var string
     */
    public $verifyUrl;

    /**
     * @param Request $request
     * @param array $options
     */
    public function __construct(Request $request, array $options)
    {
        parent::__construct($options);
        $this->request = $request;
    }

    /**
     * @return string[]
     */
    public function getRequiredOptions()
    {
        return [
            'privateKey',
            'verifyUrl',
        ];
    }
}
