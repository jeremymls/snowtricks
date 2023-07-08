<?php

namespace App\Entity\Model;

use Doctrine\ORM\Mapping as ORM;

trait Timestampable
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime();
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
