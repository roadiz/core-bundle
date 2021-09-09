<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\Core\Events\FilterDocumentEvent;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\Core\Models\DocumentInterface;

final class DocumentTranslationUpdatedEvent extends FilterDocumentEvent
{
    protected ?DocumentTranslation $documentTranslation;

    public function __construct(DocumentInterface $document, ?DocumentTranslation $documentTranslation = null)
    {
        parent::__construct($document);
        $this->documentTranslation = $documentTranslation;
    }

    /**
     * @return DocumentTranslation|null
     */
    public function getDocumentTranslation(): ?DocumentTranslation
    {
        return $this->documentTranslation;
    }
}
