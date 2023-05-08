<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["application"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["application"])]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(["application"])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["application"])]
    private ?string $attachment = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[Groups(["application"])]
    private ?Client $creator = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        // @ORM\HasLifecycleCallbacks()
        //
        $this->created_at = $created_at;

        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function getCreator(): ?Client
    {
        return $this->creator;
    }

    public function setCreator(?Client $creator): self
    {
        $this->creator = $creator;

        return $this;
    }
}
