<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Common\Cache\FlushableCache;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Cache\ResettableInterface;

/**
 * Handle operations with translations entities.
 */
class TranslationHandler extends AbstractHandler
{
    private ?TranslationInterface $translation = null;

    /**
     * @return TranslationInterface
     */
    public function getTranslation(): TranslationInterface
    {
        if (null === $this->translation) {
            throw new \BadMethodCallException('Translation is null');
        }
        return $this->translation;
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation)
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

        /** @var TranslationInterface $default */
        foreach ($defaults as $default) {
            $default->setDefaultTranslation(false);
        }
        $this->objectManager->flush();
        $this->translation->setDefaultTranslation(true);
        $this->objectManager->flush();

        if ($this->objectManager instanceof EntityManagerInterface) {
            $cache = $this->objectManager->getConfiguration()->getResultCacheImpl();
            if ($cache instanceof FlushableCache) {
                $cache->flushAll();
            }
            if ($cache instanceof ResettableInterface) {
                $cache->reset();
            }
        }

        return $this;
    }
}
