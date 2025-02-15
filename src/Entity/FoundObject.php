<?php

namespace App\Entity;

use App\Enum\StatusFoundObjectEnum;
use App\Repository\FoundObjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoundObjectRepository::class)]
class FoundObject extends AbstractObject
{
   
    #[ORM\Column(enumType: StatusFoundObjectEnum::class)]
    private ?StatusFoundObjectEnum $status = null;

    public function getStatus(): ?StatusFoundObjectEnum
    {
        return $this->status;
    }

    public function setStatus(StatusFoundObjectEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    
   
}
