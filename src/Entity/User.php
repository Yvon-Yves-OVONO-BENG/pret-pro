<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'Un compte existant utilise déjà ce nom de connexion')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[Assert\IdenticalTo(propertyPath:'password', message:'Les mots de passe doivent être identiques')]
    private $confirmPassword;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\OneToMany(targetEntity: Facture::class, mappedBy: 'caissiere')]
    private Collection $factures;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $etat = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?TypeUtilisateur $typeUtilisateur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'enregistrePar')]
    private Collection $produits;

    #[ORM\OneToMany(targetEntity: Lot::class, mappedBy: 'enregistrePar')]
    private Collection $lots;

    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'secretaire')]
    private Collection $commandes;

    #[ORM\OneToMany(targetEntity: ReponseQuestion::class, mappedBy: 'user')]
    private Collection $reponseQuestions;

    #[ORM\OneToMany(targetEntity: HistoriquePaiement::class, mappedBy: 'recuPar')]
    private Collection $historiquePaiements;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Genre $genre = null;

    #[ORM\Column]
    private ?bool $bloque = null;

    public function __construct()
    {
        $this->factures = new ArrayCollection();
        $this->produits = new ArrayCollection();
        $this->lots = new ArrayCollection();
        $this->commandes = new ArrayCollection();
        $this->reponseQuestions = new ArrayCollection();
        $this->historiquePaiements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getConfirmPassword(): string
    {
        return (string) $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): static
    {
        $this->contact = $contact;

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

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): static
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setCaissiere($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): static
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getCaissiere() === $this) {
                $facture->setCaissiere(null);
            }
        }

        return $this;
    }
    
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(?bool $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getTypeUtilisateur(): ?TypeUtilisateur
    {
        return $this->typeUtilisateur;
    }

    public function setTypeUtilisateur(?TypeUtilisateur $typeUtilisateur): static
    {
        $this->typeUtilisateur = $typeUtilisateur;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

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
            $produit->setEnregistrePar($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getEnregistrePar() === $this) {
                $produit->setEnregistrePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Lot>
     */
    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function addLot(Lot $lot): static
    {
        if (!$this->lots->contains($lot)) {
            $this->lots->add($lot);
            $lot->setEnregistrePar($this);
        }

        return $this;
    }

    public function removeLot(Lot $lot): static
    {
        if ($this->lots->removeElement($lot)) {
            // set the owning side to null (unless already changed)
            if ($lot->getEnregistrePar() === $this) {
                $lot->setEnregistrePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setSecretaire($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getSecretaire() === $this) {
                $commande->setSecretaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReponseQuestion>
     */
    public function getReponseQuestions(): Collection
    {
        return $this->reponseQuestions;
    }

    public function addReponseQuestion(ReponseQuestion $reponseQuestion): static
    {
        if (!$this->reponseQuestions->contains($reponseQuestion)) {
            $this->reponseQuestions->add($reponseQuestion);
            $reponseQuestion->setUser($this);
        }

        return $this;
    }

    public function removeReponseQuestion(ReponseQuestion $reponseQuestion): static
    {
        if ($this->reponseQuestions->removeElement($reponseQuestion)) {
            // set the owning side to null (unless already changed)
            if ($reponseQuestion->getUser() === $this) {
                $reponseQuestion->setUser(null);
            }
        }

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
            $historiquePaiement->setRecuPar($this);
        }

        return $this;
    }

    public function removeHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if ($this->historiquePaiements->removeElement($historiquePaiement)) {
            // set the owning side to null (unless already changed)
            if ($historiquePaiement->getRecuPar() === $this) {
                $historiquePaiement->setRecuPar(null);
            }
        }

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function isBloque(): ?bool
    {
        return $this->bloque;
    }

    public function setBloque(bool $bloque): static
    {
        $this->bloque = $bloque;

        return $this;
    }
}
