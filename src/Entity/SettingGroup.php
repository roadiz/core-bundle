<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use JMS\Serializer\Annotation as Serializer;

/**
 * Settings entity are a simple key-value configuration system.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\SettingGroupRepository")
 * @ORM\Table(name="settings_groups")
 *
 */
class SettingGroup extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Groups({"setting", "setting_group"})
     * @Serializer\Type("string")
     * @var string
     */
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
     * @ORM\Column(type="boolean", name="in_menu", nullable=false, options={"default" = false})
     * @Serializer\Groups({"setting", "setting_group"})
     * @Serializer\Type("bool")
     */
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
     * @ORM\OneToMany(targetEntity="Setting", mappedBy="settingGroup")
     * @var Collection<Setting>
     * @Serializer\Groups({"setting_group"})
     */
    private Collection $settings;

    /**
     * @{inheritdoc}
     */
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
