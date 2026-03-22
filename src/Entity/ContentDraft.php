<?php

namespace App\Entity;

use App\Repository\ContentDraftRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentDraftRepository::class)]
class ContentDraft
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'drafts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ContentProject $project = null;

    #[ORM\Column]
    private ?int $versionNumber = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, ReviewFeedback>
     */
    #[ORM\OneToMany(targetEntity: ReviewFeedback::class, mappedBy: 'draft', orphanRemoval: true)]
    private Collection $feedbacks;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->versionNumber = 1;
        $this->feedbacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?ContentProject
    {
        return $this->project;
    }

    public function setProject(?ContentProject $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getVersionNumber(): ?int
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(int $versionNumber): static
    {
        $this->versionNumber = $versionNumber;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    /**
     * @return Collection<int, ReviewFeedback>
     */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(ReviewFeedback $feedback): static
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setDraft($this);
        }

        return $this;
    }

    public function removeFeedback(ReviewFeedback $feedback): static
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getDraft() === $this) {
                $feedback->setDraft(null);
            }
        }

        return $this;
    }
}
