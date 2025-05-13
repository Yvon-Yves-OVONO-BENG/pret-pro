<?php

namespace App\Entity;

use App\Repository\HistoriquePaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriquePaiementRepository::class)]
class HistoriquePaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiquePaiements')]
    private ?Facture $facture = null;

    #[ORM\Column(nullable: true)]
    private ?int $montantAvance = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAvanceAt = null;

    #[ORM\ManyToOne(inversedBy: 'historiquePaiements')]
    private ?User $recuPar = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;

        return $this;
    }

    public function getMontantAvance(): ?int
    {
        return $this->montantAvance;
    }

    public function setMontantAvance(?int $montantAvance): static
    {
        $this->montantAvance = $montantAvance;

        return $this;
    }

    public function getDateAvanceAt(): ?\DateTimeInterface
    {
        return $this->dateAvanceAt;
    }

    public function setDateAvanceAt(?\DateTimeInterface $dateAvanceAt): static
    {
        $this->dateAvanceAt = $dateAvanceAt;

        return $this;
    }

    public function getRecuPar(): ?User
    {
        return $this->recuPar;
    }

    public function setRecuPar(?User $recuPar): static
    {
        $this->recuPar = $recuPar;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }
}
