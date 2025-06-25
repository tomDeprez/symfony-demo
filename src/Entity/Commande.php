<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?User $User = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'Commande')]
    private ?Statuts $statuts = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStatut(): ?Statuts
    {
        return $this->statuts;
    }

    public function setStatut(?Statuts $statuts): static
    {
        $this->statuts = $statuts;

        return $this;
    }
}
