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
    /**
     * @Serializer\Groups({"setting", "setting_group"})
     * @Serializer\Type("string")
     * @var string
     */
    #[ORM\Column(type: 'string', unique: true)]
    #[SymfonySerializer\Groups(['setting', 'setting_group'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private string $name = '';

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
     * @Serializer\Groups({"setting", "setting_group"})
     * @Serializer\Type("bool")
     */
    #[ORM\Column(type: 'boolean', name: 'in_menu', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['setting', 'setting_group'])]
    protected bool $inMenu = false;

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
     * @var Collection<Setting>
     * @Serializer\Groups({"setting_group"})
     */
    #[ORM\OneToMany(targetEntity: 'Setting', mappedBy: 'settingGroup')]
    #[SymfonySerializer\Groups(['setting_group'])]
    private Collection $settings;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
    }

    /**
     * @return Collection<Setting>
     */
    public function getSettings()
    {
        return $this->settings;
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
