<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_user_detail",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 * 
 * * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteUser",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers", excludeIf = "expr(not is_granted('ROLE_USER'))"),
 * )
 * 
 *
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"getUsers"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getUsers"})
     * @Assert\NotBlank(message="Le prénom de l'utilisateur est obligatoire")
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getUsers"})
     * @Assert\NotBlank(message="Le nom de l'utilisateur est obligatoire")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getUsers"})
     * @Assert\NotBlank(message="L'email de l'utilisateur est obligatoire")
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=Costumer::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"getUsers"})
     */
    private $costumer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCostumer(): ?Costumer
    {
        return $this->costumer;
    }

    public function setCostumer(?Costumer $costumer): self
    {
        $this->costumer = $costumer;

        return $this;
    }
}
