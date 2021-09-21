<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Utils\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Extension that allow render documents Url
 */
class DocumentUrlExtension extends AbstractExtension
{
    protected DocumentUrlGeneratorInterface $documentUrlGenerator;
    protected bool $throwExceptions;

    /**
     * @param DocumentUrlGeneratorInterface $documentUrlGenerator
     * @param bool $throwExceptions Trigger exception if using filter on NULL values (default: false)
     */
    public function __construct(
        DocumentUrlGeneratorInterface $documentUrlGenerator,
        bool $throwExceptions = false
    ) {
        $this->throwExceptions = $throwExceptions;
        $this->documentUrlGenerator = $documentUrlGenerator;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('url', [$this, 'getUrl']),
        ];
    }

    /**
     * Convert an AbstractEntity to an Url.
     *
     * Compatible AbstractEntity:
     *
     * - Document
     *
     * @param  AbstractEntity|null $mixed
     * @param  array $criteria
     * @return string
     * @throws RuntimeError
     */
    public function getUrl(AbstractEntity $mixed = null, array $criteria = [])
    {
        if (null === $mixed) {
            if ($this->throwExceptions) {
                throw new RuntimeError("Twig “url” filter must be used with a not null object");
            } else {
                return "";
            }
        }

        if ($mixed instanceof Document) {
            try {
                $absolute = false;
                if (isset($criteria['absolute'])) {
                    $absolute = (boolean) $criteria['absolute'];
                }

                $this->documentUrlGenerator->setOptions($criteria);
                $this->documentUrlGenerator->setDocument($mixed);
                return $this->documentUrlGenerator->getUrl($absolute);
            } catch (InvalidArgumentException $e) {
                throw new RuntimeError($e->getMessage(), -1, null, $e);
            }
        }

        throw new RuntimeError("Twig “url” filter can be only used with a Document");
    }
}