<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: '`admin`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[Constraints\UniqueEntity(fields: ['email'], message: 'The email {{ value }} is already in use.')]
class Admin implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    private string $email;

    /**
     * The user roles
     *
     * @var array<string>
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * The raw password
     */
    #[Assert\PasswordStrength(
        [
            'minScore' => Assert\PasswordStrength::STRENGTH_WEAK,
        ]
    )]
    private ?string $rawPassword = null;

    /**
     * The hashed password
     */
    #[ORM\Column]
    private string $password;

    #[ORM\Column(options: ['default' => false])]
    private bool $isActive = false;

    /**
     * Get the ID of the admin
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the email of the admin
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the email of the admin
     *
     * @param string $email The email address
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Get the roles of the admin
     *
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Guarantee every user at least has ROLE_USER.
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Set the roles of the admin
     *
     * @param array<string> $roles The roles of the admin.
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get the hashed password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Get the raw password
     */
    public function getRawPassword(): ?string
    {
        return $this->rawPassword;
    }

    /**
     * Validate the raw password
     *
     * @param string $rawPassword The raw password to validate
     */
    public function validateRawPassword(string $rawPassword): static
    {
        $this->rawPassword = $rawPassword;

        return $this;
    }

    /**
     * Set the hashed password
     *
     * @param string $password The hashed password
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Erase temporary sensitive data
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here.
        // $this->plainPassword = null; //.
    }

    /**
     * Check if admin is active
     */
    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * Set the active status of the admin
     *
     * @param bool $isActive The active status
     */
    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
