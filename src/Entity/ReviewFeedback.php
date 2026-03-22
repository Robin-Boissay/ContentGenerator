<?php

namespace App\Entity;

use App\Repository\ReviewFeedbackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewFeedbackRepository::class)]
class ReviewFeedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ContentDraft $draft = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $critique = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $suggestions = null;

    #[ORM\Column]
    private ?bool $isApproved = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isApproved = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDraft(): ?ContentDraft
    {
        return $this->draft;
    }

    public function setDraft(?ContentDraft $draft): static
    {
        $this->draft = $draft;

        return $this;
    }

    public function getCritique(): ?string
    {
        return $this->critique;
    }

    public function setCritique(string $critique): static
    {
        $this->critique = $critique;

        return $this;
    }

    public function getSuggestions(): ?string
    {
        return $this->suggestions;
    }

    public function setSuggestions(?string $suggestions): static
    {
        $this->suggestions = $suggestions;

        return $this;
    }

    public function isApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setApproved(bool $isApproved): static
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
