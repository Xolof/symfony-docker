<?php

namespace App\Entity;

use App\Repository\BadgerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BadgerRepository::class)]
class Badger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $continent;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 100)]
    private string $description;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    private string $imageFilename;

    /**
     * Gets the ID of the Badger entity.
     *
     * @return int|null The ID of the Badger, or null if not set
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the name of the Badger.
     *
     * @return string|null The name of the Badger, or null if not set
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the name of the Badger.
     *
     * @param string $name The name to set
     *
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the continent of the Badger.
     *
     * @return string|null The continent of the Badger, or null if not set
     */
    public function getContinent(): ?string
    {
        return $this->continent;
    }

    /**
     * Sets the continent of the Badger.
     *
     * @param string $continent The continent to set
     *
     * @return static
     */
    public function setContinent(string $continent): static
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * Gets the description of the Badger.
     *
     * @return string|null The description of the Badger, or null if not set
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets the description of the Badger.
     *
     * @param string $description The description to set
     *
     * @return static
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the filename of the image of the badger.
     */
    public function getImageFilename(): string
    {
        return $this->imageFilename;
    }

    /**
     * Set the filename of the image of the badger.
     */
    public function setImageFilename(string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }
}
