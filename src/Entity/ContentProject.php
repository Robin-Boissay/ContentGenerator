<?php

namespace App\Entity;

use App\Repository\ContentProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentProjectRepository::class)]
class ContentProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $initialBrief = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $targetAudience = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, ContentDraft>
     */
    #[ORM\OneToMany(targetEntity: ContentDraft::class, mappedBy: 'project', orphanRemoval: true)]
    private Collection $drafts;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->drafts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getInitialBrief(): ?string
    {
        return $this->initialBrief;
    }

    public function setInitialBrief(string $initialBrief): static
    {
        $this->initialBrief = $initialBrief;

        return $this;
    }

    public function getTargetAudience(): ?string
    {
        return $this->targetAudience;
    }

    public function setTargetAudience(?string $targetAudience): static
    {
        $this->targetAudience = $targetAudience;

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
     * @return Collection<int, ContentDraft>
     */
    public function getDrafts(): Collection
    {
        return $this->drafts;
    }

    public function addDraft(ContentDraft $draft): static
    {
        if (!$this->drafts->contains($draft)) {
            $this->drafts->add($draft);
            $draft->setProject($this);
        }

        return $this;
    }

    public function removeDraft(ContentDraft $draft): static
    {
        if ($this->drafts->removeElement($draft)) {
            // set the owning side to null (unless already changed)
            if ($draft->getProject() === $this) {
                $draft->setProject(null);
            }
        }

        return $this;
    }
}
