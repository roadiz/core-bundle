<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Repository\SettingGroupRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Settings entity are a simple key-value configuration system.
 */
#[
    ORM\Entity(repositoryClass: SettingGroupRepository::class),
    ORM\Table(name: "settings_groups"),
    UniqueEntity(fields: ["name"])
]
class SettingGroup extends AbstractEntity
{
    #[ORM\Column(name: 'in_menu', type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['setting', 'setting_group'])]
    #[Serializer\Groups(['setting', 'setting_group'])]
    protected bool $inMenu = false;

    #[ORM\Column(type: 'string', unique: true)]
    #[SymfonySerializer\Groups(['setting', 'setting_group'])]
    #[Serializer\Groups(['setting', 'setting_group'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private string $name = '';

    /**
     * @var Collection<Setting>
     */
    #[ORM\OneToMany(mappedBy: 'settingGroup', targetEntity: Setting::class)]
    #[SymfonySerializer\Groups(['setting_group'])]
    #[Serializer\Groups(['setting_group'])]
    private Collection $settings;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return SettingGroup
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInMenu(): bool
    {
        return $this->inMenu;
    }

    /**
     * @param bool $newinMenu
     * @return SettingGroup
     */
    public function setInMenu(bool $newinMenu)
    {
        $this->inMenu = $newinMenu;

        return $this;
    }

    /**
     * @param Setting $setting
     * @return SettingGroup
     */
    public function addSetting(Setting $setting)
    {
        if (!$this->getSettings()->contains($setting)) {
            $this->settings->add($setting);
        }
        return $this;
    }

    /**
     * @return Collection<Setting>
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param Collection<Setting> $settings
     * @return SettingGroup
     */
    public function addSettings(Collection $settings)
    {
        foreach ($settings as $setting) {
            if (!$this->getSettings()->contains($setting)) {
                $this->settings->add($setting);
            }
        }
        return $this;
    }
}
