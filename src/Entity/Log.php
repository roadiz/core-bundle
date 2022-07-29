<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Monolog\Logger;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\LogRepository")
 * @ORM\Table(name="log", indexes={
 *     @ORM\Index(columns={"datetime"}),
 *     @ORM\Index(columns={"node_source_id", "datetime"}, name="log_ns_datetime"),
 *     @ORM\Index(columns={"username", "datetime"}, name="log_username_datetime"),
 *     @ORM\Index(columns={"user_id", "datetime"}, name="log_user_datetime"),
 *     @ORM\Index(columns={"level", "datetime"}, name="log_level_datetime"),
 *     @ORM\Index(columns={"channel", "datetime"}, name="log_channel_datetime"),
 *     @ORM\Index(columns={"level"}),
 *     @ORM\Index(columns={"username"}),
 *     @ORM\Index(columns={"channel"})
 * })
 * @ORM\HasLifecycleCallbacks
 */
class Log extends AbstractEntity
{
    public const EMERGENCY = Logger::EMERGENCY;
    public const CRITICAL =  Logger::CRITICAL;
    public const ALERT =     Logger::ALERT;
    public const ERROR =     Logger::ERROR;
    public const WARNING =   Logger::WARNING;
    public const NOTICE =    Logger::NOTICE;
    public const INFO =      Logger::INFO;
    public const DEBUG =     Logger::DEBUG;
    public const LOG =       Logger::INFO;

    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", unique=false, onDelete="SET NULL")
     * @var User|null
     * @Serializer\Groups({"log_user"})
     * @SymfonySerializer\Groups({"log_user"})
     */
    protected ?User $user = null;
    /**
     * @ORM\Column(type="string", name="username", nullable=true)
     * @var string|null
     * @Serializer\Groups({"log_user"})
     * @SymfonySerializer\Groups({"log_user"})
     */
    protected ?string $username = null;
    /**
     * @ORM\Column(type="text", name="message")
     * @Serializer\Groups({"log"})
     * @SymfonySerializer\Groups({"log"})
     * @var string
     */
    protected string $message = '';
    /**
     * @ORM\Column(type="integer", name="level", nullable=false)
     * @Serializer\Groups({"log"})
     * @SymfonySerializer\Groups({"log"})
     * @var int
     */
    protected int $level = Log::DEBUG;
    /**
     * @ORM\Column(type="datetime", name="datetime", nullable=false)
     * @Serializer\Groups({"log"})
     * @SymfonySerializer\Groups({"log"})
     * @var \DateTime
     */
    protected \DateTime $datetime;
    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\NodesSources", inversedBy="logs")
     * @ORM\JoinColumn(name="node_source_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"log_sources"})
     * @SymfonySerializer\Groups({"log_sources"})
     */
    protected ?NodesSources $nodeSource = null;
    /**
     * @ORM\Column(type="string", name="client_ip", unique=false, nullable=true)
     * @Serializer\Groups({"log"})
     * @SymfonySerializer\Groups({"log"})
     * @var string|null
     */
    protected ?string $clientIp = null;
    /**
     * @ORM\Column(type="string", name="channel", unique=false, nullable=true)
     * @Serializer\Groups({"log"})
     * @SymfonySerializer\Groups({"log"})
     * @var string|null
     */
    protected ?string $channel = null;
    /**
     * @ORM\Column(type="json", name="additional_data", unique=false, nullable=true)
     * @Serializer\Groups({"log"})
     * @SymfonySerializer\Groups({"log"})
     * @var array|null
     */
    protected ?array $additionalData = null;

    /**
     * @param int    $level
     * @param string $message
     *
     * @throws \Exception
     */
    public function __construct(int $level, string $message)
    {
        $this->level = $level;
        $this->message = $message;
        $this->datetime = new \DateTime("now");
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Log
     */
    public function setUser(User $user): Log
    {
        $this->user = $user;
        $this->username = $user->getUsername();
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return \DateTime
     */
    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    /**
     * Get log related node-source.
     *
     * @return NodesSources|null
     */
    public function getNodeSource(): ?NodesSources
    {
        return $this->nodeSource;
    }

    /**
     * @param NodesSources|null $nodeSource
     * @return $this
     */
    public function setNodeSource(?NodesSources $nodeSource): Log
    {
        $this->nodeSource = $nodeSource;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    /**
     * @param string $clientIp
     * @return Log
     */
    public function setClientIp(?string $clientIp): Log
    {
        $this->clientIp = $clientIp;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAdditionalData(): ?array
    {
        return $this->additionalData;
    }

    /**
     * @param array|null $additionalData
     *
     * @return Log
     */
    public function setAdditionalData(?array $additionalData): Log
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * @param string|null $channel
     *
     * @return Log
     */
    public function setChannel(?string $channel): Log
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     *
     * @return Log
     */
    public function setUsername(?string $username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->datetime = new \DateTime("now");
    }
}
