<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

class TablePrefixSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    protected string $tablesPrefix;

    /**
     * @param string $tablesPrefix
     */
    public function __construct(string $tablesPrefix = '')
    {
        $this->tablesPrefix = $tablesPrefix;
    }


    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /*
         * Prefix tables
         */
        if (!empty($this->tablesPrefix) && $this->tablesPrefix !== '') {
            // the $metadata is all the mapping info for this class
            $metadata = $eventArgs->getClassMetadata();
            $metadata->table['name'] = $this->tablesPrefix.'_'.$metadata->table['name'];

            /*
             * Prefix join tables
             */
            foreach ($metadata->associationMappings as $key => $association) {
                if (!empty($association['joinTable']['name'])) {
                    $metadata->associationMappings[$key]['joinTable']['name'] =
                        $this->tablesPrefix.'_'.$association['joinTable']['name'];
                }
            }
        }
    }
}
