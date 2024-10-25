<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

final class CustomFormAnswerNotifyMessage implements AsyncMessage
{
    public function __construct(
        private readonly int $customFormAnswerId,
        private readonly string $title,
        private readonly string $senderAddress,
        private readonly string $locale
    ) {
    }

    public function getCustomFormAnswerId(): int
    {
        return $this->customFormAnswerId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSenderAddress(): string
    {
        return $this->senderAddress;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
