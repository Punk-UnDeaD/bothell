<?php

namespace App\Entity;

use App\Repository\ProcessedTaskRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProcessedTaskRepository::class)
 */
class ProcessedTask
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="uid")
     */
    private int $user;

    /**
     * @ORM\Column(type="string", length=255)
     */

    private string $message;


    /**
     * @ORM\Column(type="float")
     */
    private float $processed;

    /**
     * @ORM\Column(type="float")
     */
    private float $created;


    public function __construct($user, $created, $message)
    {
        $this->user = $user;
        $this->message = $message;
        $this->processed = microtime(true);
        $this->created = $created;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?int
    {
        return $this->user;
    }

    public function setUser(int $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
