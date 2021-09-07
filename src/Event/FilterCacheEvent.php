<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\Kernel;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
abstract class FilterCacheEvent extends Event
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Collection
     */
    private $messageCollection;

    /**
     * @var Collection
     */
    private $errorCollection;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->messageCollection = new ArrayCollection();
        $this->errorCollection = new ArrayCollection();
    }

    /**
     * @return Kernel
     */
    public function getKernel(): Kernel
    {
        return $this->kernel;
    }

    /**
     * @param string $message
     * @param string|null $classname
     * @param string|null $description
     */
    public function addMessage($message, $classname = null, $description = null)
    {
        $this->messageCollection->add([
            "clearer" => $classname,
            "description" => $description,
            "message" => $message,
        ]);
    }

    /**
     * @param string $message
     * @param string|null $classname
     * @param string|null $description
     */
    public function addError($message, $classname = null, $description = null)
    {
        $this->errorCollection->add([
            "clearer" => $classname,
            "description" => $description,
            "message" => $message,
        ]);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messageCollection->toArray();
    }
}
