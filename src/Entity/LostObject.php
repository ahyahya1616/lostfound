<?php

namespace App\Entity;

use App\Enum\StatusLostObjectEnum;
use App\Repository\LostObjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LostObjectRepository::class)]
class LostObject  extends AbstractObject 
{
    

    #[ORM\Column(enumType: StatusLostObjectEnum::class)]
    private ?StatusLostObjectEnum $status = null;

   
   
   
    public function getStatus(): ?StatusLostObjectEnum
    {
        return $this->status;
    }

    public function setStatus(StatusLostObjectEnum $status): static
    {
        $this->status = $status;

        return $this;
    }


}
