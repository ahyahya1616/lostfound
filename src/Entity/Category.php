<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, AbstractObject>
     */
    #[ORM\OneToMany(targetEntity: AbstractObject::class, mappedBy: 'category', orphanRemoval: true)]
    private Collection $objects;

    public function __construct()
    {
        $this->objects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, AbstractObject>
     */
    public function getObjects(): Collection
    {
        return $this->objects;
    }

    public function addObject(AbstractObject $object): static
    {
        if (!$this->objects->contains($object)) {
            $this->objects->add($object);
            $object->setCategory($this);
        }

        return $this;
    }

    public function removeObject(AbstractObject $object): static
    {
        if ($this->objects->removeElement($object)) {
            // set the owning side to null (unless already changed)
            if ($object->getCategory() === $this) {
                $object->setCategory(null);
            }
        }

        return $this;
    }
}
