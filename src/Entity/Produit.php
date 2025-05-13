<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixVente = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantiteSeuil = null;

    #[Vich\UploadableField(mapping:"produits_image", fileNameProperty:"photo")]
    /**
    *
    * @var File|null
     */
    private $photoFile;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToMany(targetEntity: LigneDeFacture::class, mappedBy: 'produit')]
    private Collection $ligneDeFactures;

    #[ORM\OneToMany(targetEntity: FournisseurProduit::class, mappedBy: 'produit')]
    private Collection $fournisseurProduits;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Lot $lot = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $supprime = null;

    #[ORM\OneToMany(targetEntity: LigneDeEnsemble::class, mappedBy: 'produit')]
    private Collection $ligneDeEnsembles;

    #[ORM\Column(nullable: true)]
    private ?bool $ensemble = null;

    #[ORM\OneToMany(targetEntity: LigneDeEnsemble::class, mappedBy: 'produitEnsemble', cascade: ['persist', 'remove'])]
    private Collection $produitLigneDeEnsembles;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEntreeAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePeremptionAt = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?User $enregistrePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $enregistreAt = null;

    #[ORM\OneToMany(targetEntity: LigneDeCommande::class, mappedBy: 'produit')]
    private Collection $ligneDeCommandes;

    #[ORM\Column]
    private ?bool $retire = null;

    public function __construct()
    {
        $this->ligneDeFactures = new ArrayCollection();
        $this->fournisseurProduits = new ArrayCollection();
        $this->ligneDeEnsembles = new ArrayCollection();
        $this->produitLigneDeEnsembles = new ArrayCollection();
        $this->ligneDeCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getPrixVente(): ?int
    {
        return $this->prixVente;
    }

    public function setPrixVente(int $prixVente): static
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function getQuantiteSeuil(): ?int
    {
        return $this->quantiteSeuil;
    }

    public function setQuantiteSeuil(int $quantiteSeuil): static
    {
        $this->quantiteSeuil = $quantiteSeuil;

        return $this;
    }

    /**
     * Set the photo file
     *
     * @param File|null $photoFile
     * @return void
     */
    public function setPhotoFile(?File $photoFile = null): void
    {
        $this->photoFile = $photoFile;

        if($photoFile !== null)
        {
            $this->setUpdateAt(new \DateTime());
        }
    }

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

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
            $ligneDeFacture->setProduit($this);
        }

        return $this;
    }

    public function removeLigneDeFacture(LigneDeFacture $ligneDeFacture): static
    {
        if ($this->ligneDeFactures->removeElement($ligneDeFacture)) {
            // set the owning side to null (unless already changed)
            if ($ligneDeFacture->getProduit() === $this) {
                $ligneDeFacture->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FournisseurProduit>
     */
    public function getFournisseurProduits(): Collection
    {
        return $this->fournisseurProduits;
    }

    public function addFournisseurProduit(FournisseurProduit $fournisseurProduit): static
    {
        if (!$this->fournisseurProduits->contains($fournisseurProduit)) {
            $this->fournisseurProduits->add($fournisseurProduit);
            $fournisseurProduit->setProduit($this);
        }

        return $this;
    }

    public function removeFournisseurProduit(FournisseurProduit $fournisseurProduit): static
    {
        if ($this->fournisseurProduits->removeElement($fournisseurProduit)) {
            // set the owning side to null (unless already changed)
            if ($fournisseurProduit->getProduit() === $this) {
                $fournisseurProduit->setProduit(null);
            }
        }

        return $this;
    }

    public function getLot(): ?Lot
    {
        return $this->lot;
    }

    public function setLot(?Lot $lot): static
    {
        $this->lot = $lot;

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

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function isSupprime(): ?bool
    {
        return $this->supprime;
    }

    public function setSupprime(?bool $supprime): static
    {
        $this->supprime = $supprime;

        return $this;
    }

    /**
     * @return Collection<int, LigneDeEnsemble>
     */
    public function getLigneDeEnsembles(): Collection
    {
        return $this->ligneDeEnsembles;
    }

    public function addLigneDeEnsemble(LigneDeEnsemble $ligneDeEnsemble): static
    {
        if (!$this->ligneDeEnsembles->contains($ligneDeEnsemble)) {
            $this->ligneDeEnsembles->add($ligneDeEnsemble);
            $ligneDeEnsemble->setProduit($this);
        }

        return $this;
    }

    public function removeLigneDeEnsemble(LigneDeEnsemble $ligneDeEnsemble): static
    {
        if ($this->ligneDeEnsembles->removeElement($ligneDeEnsemble)) {
            // set the owning side to null (unless already changed)
            if ($ligneDeEnsemble->getProduit() === $this) {
                $ligneDeEnsemble->setProduit(null);
            }
        }

        return $this;
    }

    public function isEnsemble(): ?bool
    {
        return $this->ensemble;
    }

    public function setEnsemble(bool $ensemble): static
    {
        $this->ensemble = $ensemble;

        return $this;
    }

    /**
     * @return Collection<int, LigneDeEnsemble>
     */
    public function getProduitLigneDeEnsembles(): Collection
    {
        return $this->produitLigneDeEnsembles;
    }

    public function addProduitLigneDeEnsemble(LigneDeEnsemble $produitLigneDeEnsemble): static
    {
        if (!$this->produitLigneDeEnsembles->contains($produitLigneDeEnsemble)) {
            $this->produitLigneDeEnsembles->add($produitLigneDeEnsemble);
            $produitLigneDeEnsemble->setProduitEnsemble($this);
        }

        return $this;
    }

    public function removeProduitLigneDeEnsemble(LigneDeEnsemble $produitLigneDeEnsemble): static
    {
        if ($this->produitLigneDeEnsembles->removeElement($produitLigneDeEnsemble)) {
            // set the owning side to null (unless already changed)
            if ($produitLigneDeEnsemble->getProduitEnsemble() === $this) {
                $produitLigneDeEnsemble->setProduitEnsemble(null);
            }
        }

        return $this;
    }

    public function getDateEntreeAt(): ?\DateTimeInterface
    {
        return $this->dateEntreeAt;
    }

    public function setDateEntreeAt(?\DateTimeInterface $dateEntreeAt): static
    {
        $this->dateEntreeAt = $dateEntreeAt;

        return $this;
    }

    public function getDatePeremptionAt(): ?\DateTimeInterface
    {
        return $this->datePeremptionAt;
    }

    public function setDatePeremptionAt(?\DateTimeInterface $datePeremptionAt): static
    {
        $this->datePeremptionAt = $datePeremptionAt;

        return $this;
    }

    public function serialize()
    {
        $this->photo = base64_encode($this->photo);
    }

    public function unserialize($serialized)
    {
        $this->photo = base64_decode($this->photo);

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

    public function getEnregistreAt(): ?\DateTimeInterface
    {
        return $this->enregistreAt;
    }

    public function setEnregistreAt(?\DateTimeInterface $enregistreAt): static
    {
        $this->enregistreAt = $enregistreAt;

        return $this;
    }

    /**
     * @return Collection<int, LigneDeCommande>
     */
    public function getLigneDeCommandes(): Collection
    {
        return $this->ligneDeCommandes;
    }

    public function addLigneDeCommande(LigneDeCommande $ligneDeCommande): static
    {
        if (!$this->ligneDeCommandes->contains($ligneDeCommande)) {
            $this->ligneDeCommandes->add($ligneDeCommande);
            $ligneDeCommande->setProduit($this);
        }

        return $this;
    }

    public function removeLigneDeCommande(LigneDeCommande $ligneDeCommande): static
    {
        if ($this->ligneDeCommandes->removeElement($ligneDeCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneDeCommande->getProduit() === $this) {
                $ligneDeCommande->setProduit(null);
            }
        }

        return $this;
    }

    public function isRetire(): ?bool
    {
        return $this->retire;
    }

    public function setRetire(bool $retire): static
    {
        $this->retire = $retire;

        return $this;
    }

}
