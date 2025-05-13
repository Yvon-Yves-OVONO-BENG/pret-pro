<?php

namespace App\Entity;

use App\Repository\LotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LotRepository::class)]
class Lot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'lot')]
    private Collection $produits;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?int $vendu = null;

    #[ORM\ManyToOne(inversedBy: 'lots')]
    private ?User $enregistrePar = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixAchat = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixVente = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $enregistreLeAt = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureAt = null;

    #[ORM\ManyToOne(inversedBy: 'lots')]
    private ?TypeProduit $typeProduit = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
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

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setLot($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getLot() === $this) {
                $produit->setLot(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getVendu(): ?int
    {
        return $this->vendu;
    }

    public function setVendu(int $vendu): static
    {
        $this->vendu = $vendu;

        return $this;
    }

    public function getEnregistrePar(): ?User
    {
        return $this->enregistrePar;
    }

    public function setEnregistrePar(?User $enregistrePar): static
    {
        $this->enregistrePar = $enregistrePar;

        return $this;
    }

    public function getPrixAchat(): ?int
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?int $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getPrixVente(): ?int
    {
        return $this->prixVente;
    }

    public function setPrixVente(?int $prixVente): static
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function getEnregistreLeAt(): ?\DateTimeInterface
    {
        return $this->enregistreLeAt;
    }

    public function setEnregistreLeAt(?\DateTimeInterface $enregistreLeAt): static
    {
        $this->enregistreLeAt = $enregistreLeAt;

        return $this;
    }

    public function getHeureAt(): ?\DateTimeInterface
    {
        return $this->heureAt;
    }

    public function setHeureAt(?\DateTimeInterface $heureAt): static
    {
        $this->heureAt = $heureAt;

        return $this;
    }

    public function getTypeProduit(): ?TypeProduit
    {
        return $this->typeProduit;
    }

    public function setTypeProduit(?TypeProduit $typeProduit): static
    {
        $this->typeProduit = $typeProduit;

        return $this;
    }
}
