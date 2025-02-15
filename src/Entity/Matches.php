<?php

namespace App\Entity;

use App\Repository\MatchesRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\StatusLostObjectEnum;
use App\Enum\StatusFoundObjectEnum;

#[ORM\Entity(repositoryClass: MatchesRepository::class)]
class Matches
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(inversedBy: 'matches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AbstractObject $object = null;



    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObject(): ?AbstractObject
    {
        return $this->object;
    }

    public function setObject(?AbstractObject $object): static
    {
        $this->object = $object;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->object = $user;

        return $this;
    }

    public function markObjectAsReturnedOrRetrieved(): void
{
    if ($this->object instanceof LostObject) {
        $this->object->setStatus(StatusLostObjectEnum::RETROUVE);
    } elseif ($this->object instanceof FoundObject) {
        $this->object->setStatus(StatusFoundObjectEnum::RENDU);
    } else {
        throw new \LogicException("Type d'objet non reconnu.");
    }
}


   
}
