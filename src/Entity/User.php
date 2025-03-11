<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $fullName;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column]
    private string $password;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dob = null;  // ✅ Keep only DOB, remove age

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Post::class, cascade: ["remove"], orphanRemoval: true)]
    private Collection $posts;

    /**
     * @var Collection<int, PostReaction>
     */
    #[ORM\OneToMany(targetEntity: PostReaction::class, mappedBy: 'user')]
    private Collection $type;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->type = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $fullName): self { $this->fullName = $fullName; return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function getDob(): ?\DateTimeInterface { return $this->dob; }
    public function setDob(?\DateTimeInterface $dob): self { $this->dob = $dob; return $this; }
    public function getProfilePicture(): ?string { return $this->profilePicture; }
    public function setProfilePicture(?string $profilePicture): self { $this->profilePicture = $profilePicture; return $this; }
    public function getPosts(): Collection { return $this->posts; }
    public function getUserIdentifier(): string { return $this->email; }
    public function getRoles(): array { return ['ROLE_USER']; }
    public function eraseCredentials(): void {}

    public function getAge(): ?int
    {
        if (!$this->dob) {
            return null;
        }
        return $this->dob->diff(new \DateTime())->y;
    }

    /**
     * @return Collection<int, PostReaction>
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(PostReaction $type): static
    {
        if (!$this->type->contains($type)) {
            $this->type->add($type);
            $type->setUser($this);
        }

        return $this;
    }

    public function removeType(PostReaction $type): static
    {
        if ($this->type->removeElement($type)) {
            // set the owning side to null (unless already changed)
            if ($type->getUser() === $this) {
                $type->setUser(null);
            }
        }

        return $this;
    }
    
}
