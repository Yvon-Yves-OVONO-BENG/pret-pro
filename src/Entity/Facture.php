<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $nomClient = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $contactClient = null;

    #[ORM\Column]
    private ?int $netAPayer = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?User $caissiere = null;

    #[ORM\OneToMany(targetEntity: LigneDeFacture::class, mappedBy: 'facture')]
    private Collection $ligneDeFactures;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?EtatFacture $etatFacture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?ModePaiement $modePaiement = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFactureAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $annulee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $annuleeLeAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $avance = null;

    #[ORM\OneToMany(targetEntity: HistoriquePaiement::class, mappedBy: 'facture')]
    private Collection $historiquePaiements;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailClient = null;

    #[ORM\Column(length: 255)]
    private ?string $qrCode = null;

    #[ORM\Column(length: 255)]
    private ?string $adresseClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $remarques = null;

    public function __construct()
    {
        $this->ligneDeFactures = new ArrayCollection();
        $this->historiquePaiements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function setNomClient(string $nomClient): static
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    public function getContactClient(): ?string
    {
        return $this->contactClient;
    }

    public function setContactClient(string $contactClient): static
    {
        $this->contactClient = $contactClient;

        return $this;
    }

    public function getNetAPayer(): ?int
    {
        return $this->netAPayer;
    }

    public function setNetAPayer(int $netAPayer): static
    {
        $this->netAPayer = $netAPayer;

        return $this;
    }

    public function getCaissiere(): ?User
    {
        return $this->caissiere;
    }

    public function setCaissiere(?User $caissiere): static
    {
        $this->caissiere = $caissiere;

        return $this;
    }

    /**
     * @return Collection<int, LigneDeFacture>
     */
    public function getLigneDeFactures(): Collection
    {
        return $this->ligneDeFactures;
    }

    public function addLigneDeFacture(LigneDeFacture $ligneDeFacture): static
    {
        if (!$this->ligneDeFactures->contains($ligneDeFacture)) {
            $this->ligneDeFactures->add($ligneDeFacture);
            $ligneDeFacture->setFacture($this);
        }

        return $this;
    }

    public function removeLigneDeFacture(LigneDeFacture $ligneDeFacture): static
    {
        if ($this->ligneDeFactures->removeElement($ligneDeFacture)) {
            // set the owning side to null (unless already changed)
            if ($ligneDeFacture->getFacture() === $this) {
                $ligneDeFacture->setFacture(null);
            }
        }

        return $this;
    }

    public function getEtatFacture(): ?EtatFacture
    {
        return $this->etatFacture;
    }

    public function setEtatFacture(?EtatFacture $etatFacture): static
    {
        $this->etatFacture = $etatFacture;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getModePaiement(): ?ModePaiement
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?ModePaiement $modePaiement): static
    {
        $this->modePaiement = $modePaiement;

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

    public function getDateFactureAt(): ?\DateTimeInterface
    {
        return $this->dateFactureAt;
    }

    public function setDateFactureAt(\DateTimeInterface $dateFactureAt): static
    {
        $this->dateFactureAt = $dateFactureAt;

        return $this;
    }

    public function isAnnulee(): ?bool
    {
        return $this->annulee;
    }

    public function setAnnulee(?bool $annulee): static
    {
        $this->annulee = $annulee;

        return $this;
    }

    public function getAnnuleeLeAt(): ?\DateTimeInterface
    {
        return $this->annuleeLeAt;
    }

    public function setAnnuleeLeAt(?\DateTimeInterface $annuleeLeAt): static
    {
        $this->annuleeLeAt = $annuleeLeAt;

        return $this;
    }

    public function getAvance(): ?int
    {
        return $this->avance;
    }

    public function setAvance(?int $avance): static
    {
        $this->avance = $avance;

        return $this;
    }

    /**
     * @return Collection<int, HistoriquePaiement>
     */
    public function getHistoriquePaiements(): Collection
    {
        return $this->historiquePaiements;
    }

    public function addHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if (!$this->historiquePaiements->contains($historiquePaiement)) {
            $this->historiquePaiements->add($historiquePaiement);
            $historiquePaiement->setFacture($this);
        }

        return $this;
    }

    public function removeHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if ($this->historiquePaiements->removeElement($historiquePaiement)) {
            // set the owning side to null (unless already changed)
            if ($historiquePaiement->getFacture() === $this) {
                $historiquePaiement->setFacture(null);
            }
        }

        return $this;
    }

    public function getEmailClient(): ?string
    {
        return $this->emailClient;
    }

    public function setEmailClient(?string $emailClient): static
    {
        $this->emailClient = $emailClient;

        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(string $qrCode): static
    {
        $this->qrCode = $qrCode;

        return $this;
    }

    public function getAdresseClient(): ?string
    {
        return $this->adresseClient;
    }

    public function setAdresseClient(string $adresseClient): static
    {
        $this->adresseClient = $adresseClient;

        return $this;
    }

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): static
    {
        $this->remarques = $remarques;

        return $this;
    }


}
