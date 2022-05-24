<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;

interface RealmInterface
{
    public const TYPE_PLAIN_PASSWORD = 'plain_password';
    public const TYPE_ROLE = 'bearer_role';
    public const TYPE_USER = 'bearer_user';

    /**
     * Inheritance type to prevent cascading ancestors realms.
     */
    public const INHERITANCE_NONE = 'none';
    /**
     * Inheritance type retreived automatically from ancestors.
     */
    public const INHERITANCE_AUTO = 'auto';
    /**
     * Inheritance type only for Realm roots.
     */
    public const INHERITANCE_ROOT = 'root';

    public function getType(): string;
    public function getName(): string;
    public function getRole(): ?string;
    public function getUsers(): Collection;
    public function getSerializationGroup(): ?string;
}
