<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
#[ORM\Entity]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "object_type", type: "string")]
#[ORM\DiscriminatorMap(["lost" => "LostObject", "found" => "FoundObject"])]
abstract class AbstractObject  
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'objects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected ?User $user = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $description = null;

    

    #[ORM\Column(length: 255)]
    protected ?string $location = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 9, scale: 6)]
    protected ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 9, scale: 6)]
    protected ?string $longitude = null;

    #[ORM\Column]
    protected ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Matches>
     */
    #[ORM\OneToMany(targetEntity: Matches::class, mappedBy: 'object')]
    protected Collection $matches;

    /**
     * @var Collection<int, ImageFile>
     */
    #[ORM\OneToMany(targetEntity: ImageFile::class, mappedBy: 'object', orphanRemoval: true)]
    private Collection $imageFiles;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'object', orphanRemoval: true)]
    private Collection $reports;

    #[ORM\ManyToOne(inversedBy: 'objects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    public function __construct()
    {
        $this->matches = new ArrayCollection();
        $this->imageFiles = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

 

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;
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
     * @return Collection<int, Matches>
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    

    /**
     * @return Collection<int, ImageFile>
     */
    public function getImageFiles(): Collection
    {
        return $this->imageFiles;
    }

    public function addImageFile(ImageFile $imageFile): static
    {
        if (!$this->imageFiles->contains($imageFile)) {
            $this->imageFiles->add($imageFile);
            $imageFile->setObject($this);
        }

        return $this;
    }

    public function removeImageFile(ImageFile $imageFile): static
    {
        if ($this->imageFiles->removeElement($imageFile)) {
            // set the owning side to null (unless already changed)
            if ($imageFile->getObject() === $this) {
                $imageFile->setObject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setObject($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getObject() === $this) {
                $report->setObject(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
