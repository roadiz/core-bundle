<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Doctrine\ORM\ORMException;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Translation\TranslationViewer;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TranslationMenuExtension extends AbstractExtension
{
    private RequestStack $requestStack;
    private TranslationViewer $translationViewer;

    /**
     * @param RequestStack $requestStack
     * @param TranslationViewer $translationViewer
     */
    public function __construct(RequestStack $requestStack, TranslationViewer $translationViewer)
    {
        $this->requestStack = $requestStack;
        $this->translationViewer = $translationViewer;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('menu', [$this, 'getMenuAssignation']),
        ];
    }

    /**
     * @param TranslationInterface|null $translation
     * @param bool $absolute
     *
     * @return array
     * @throws ORMException
     */
    public function getMenuAssignation(TranslationInterface $translation = null, bool $absolute = false)
    {
        if (null !== $translation) {
            $this->translationViewer->setTranslation($translation);
            return $this->translationViewer->getTranslationMenuAssignation($this->requestStack->getCurrentRequest(), $absolute);
        } else {
            return [];
        }
    }
}
