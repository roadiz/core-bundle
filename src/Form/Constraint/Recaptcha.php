<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class Recaptcha extends Constraint
{
    public string $emptyMessage = 'you_must_show_youre_not_robot';
    public string $invalidMessage = 'recaptcha_is_invalid.try_again';
    public string $fieldName = 'g-recaptcha-response';
    public string $privateKey = '';
    public string $verifyUrl = '';

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
