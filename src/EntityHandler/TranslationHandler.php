<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use Symfony\Component\Cache\ResettableInterface;

/**
 * Handle operations with translations entities.
 */
class TranslationHandler extends AbstractHandler
{
    private ?Translation $translation = null;
    private ResettableInterface $resultCache;

    public function __construct(ObjectManager $objectManager, ResettableInterface $resultCache)
    {
        parent::__construct($objectManager);
        $this->resultCache = $resultCache;
    }

    /**
     * @return Translation
     */
    public function getTranslation()
    {
        if (null === $this->translation) {
            throw new \BadMethodCallException('Translation is null');
        }
        return $this->translation;
    }

    /**
     * @param Translation $translation
     *
     * @return $this
     */
    public function setTranslation(Translation $translation)
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * Set current translation as default one.
     *
     * @return $this
     */
    public function makeDefault()
    {
        $defaults = $this->objectManager
            ->getRepository(Translation::class)
            ->findBy(['defaultTranslation' => true]);

        /** @var Translation $default */
        foreach ($defaults as $default) {
            $default->setDefaultTranslation(false);
        }
        $this->objectManager->flush();
        $this->translation->setDefaultTranslation(true);
        $this->objectManager->flush();

        if ($this->objectManager instanceof EntityManagerInterface) {
            $this->resultCache->reset();
        }

        return $this;
    }
}
