<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

final class Message
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $uuid;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $createdAt;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $message;

    /**
     * @param $message
     */
    public function __construct($message = null)
    {
        $date = new \DateTime();
        $this->createdAt = $date->getTimestamp();
        $this->uuid = Uuid::v4()->toRfc4122();
        $this->message = $message;
    }

    /**
     * @return string|\Symfony\Component\Uid\UuidV4
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return \DateTime|string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed|string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
