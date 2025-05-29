<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The constructor specifies ORM relations.
     */
    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(
            nullable: false,
            name: 'user_id',
            referencedColumnName: 'id',
            onDelete: 'CASCADE'
        )]
        private Admin $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ) {
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    /**
     * Get the id of this ResetPasswordRequest
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the user for this ResetPasswordRequest
     */
    public function getUser(): Admin
    {
        return $this->user;
    }
}
