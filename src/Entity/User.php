<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255,nullable:true)]
    private ?string $imagePathProfile = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true )]
    
    private ?string $fullName = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $token = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $authProvider = null;

    
    /**
     * @var Collection<int, LostObject>
     */
    #[ORM\OneToMany(targetEntity: LostObject::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $lostObjects;

    /**
     * @var Collection<int, FoundObject>
     */
    #[ORM\OneToMany(targetEntity: FoundObject::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $foundObjects;

    /**
     * @var Collection<int, Messages>
     */
    #[ORM\OneToMany(targetEntity: Messages::class, mappedBy: 'sender', orphanRemoval: true)]
    private Collection $SentMessages;

    /**
     * @var Collection<int, Messages>
     */
    #[ORM\OneToMany(targetEntity: Messages::class, mappedBy: 'receiver', orphanRemoval: true)]
    private Collection $ReceivedMessages;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $notifications;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'reportedBy', orphanRemoval: true)]
    private Collection $reports;

    public function __construct()
    {
        $this->lostObjects = new ArrayCollection();
        $this->foundObjects = new ArrayCollection();
        $this->SentMessages = new ArrayCollection();
        $this->ReceivedMessages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->isVerified = false;
        $this->createdAt= new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getImagePathProfile(): ?string
    {
        return $this->imagePathProfile;
    }


    public function setImagePathProfile(string $imagePathProfile): self
    {
        $this->imagePathProfile = $imagePathProfile;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getAuthProvider(): ?string
{
    return $this->authProvider;
}

public function setAuthProvider(?string $authProvider): static
{
    $this->authProvider = $authProvider;
    return $this;
}

    /**
     * @return Collection<int, LostObject>
     */
    public function getLostObjects(): Collection
    {
        return $this->lostObjects;
    }

    public function addLostObject(LostObject $lostObject): static
    {
        if (!$this->lostObjects->contains($lostObject)) {
            $this->lostObjects->add($lostObject);
            $lostObject->setUser($this);
        }

        return $this;
    }

    public function removeLostObject(LostObject $lostObject): static
    {
        if ($this->lostObjects->removeElement($lostObject)) {
            // set the owning side to null (unless already changed)
            if ($lostObject->getUser() === $this) {
                $lostObject->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FoundObject>
     */
    public function getFoundObjects(): Collection
    {
        return $this->foundObjects;
    }

    public function addFoundObject(FoundObject $foundObject): static
    {
        if (!$this->foundObjects->contains($foundObject)) {
            $this->foundObjects->add($foundObject);
            $foundObject->setUser($this);
        }

        return $this;
    }

    public function removeFoundObject(FoundObject $foundObject): static
    {
        if ($this->foundObjects->removeElement($foundObject)) {
            // set the owning side to null (unless already changed)
            if ($foundObject->getUser() === $this) {
                $foundObject->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Messages>
     */
    public function getSentMessages(): Collection
    {
        return $this->SentMessages;
    }

    public function addSentMessage(Messages $sentMessage): static
    {
        if (!$this->SentMessages->contains($sentMessage)) {
            $this->SentMessages->add($sentMessage);
            $sentMessage->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Messages $sentMessage): static
    {
        if ($this->SentMessages->removeElement($sentMessage)) {
            // set the owning side to null (unless already changed)
            if ($sentMessage->getSender() === $this) {
                $sentMessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Messages>
     */
    public function getReceivedMessages(): Collection
    {
        return $this->ReceivedMessages;
    }

    public function addReceivedMessage(Messages $receivedMessage): static
    {
        if (!$this->ReceivedMessages->contains($receivedMessage)) {
            $this->ReceivedMessages->add($receivedMessage);
            $receivedMessage->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Messages $receivedMessage): static
    {
        if ($this->ReceivedMessages->removeElement($receivedMessage)) {
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getReceiver() === $this) {
                $receivedMessage->setReceiver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
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
            $report->setReportedBy($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getReportedBy() === $this) {
                $report->setReportedBy(null);
            }
        }

        return $this;
    }
}
