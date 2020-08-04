<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TournoiRepository")
 */
class Tournoi
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbr_equipe;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbr_terrain;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duree;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_create;

    /**
    * @ORM\OneToMany(targetEntity="Match", cascade={"persist", "remove"}, mappedBy="tournoi")
    */
    protected $matchs; 

    /**
    * @ORM\OneToMany(targetEntity="Terrain", cascade={"persist", "remove"}, mappedBy="tournoi")
    */
    protected $terrains; 

    /**
    * @ORM\OneToMany(targetEntity="Equipe", cascade={"persist", "remove"}, mappedBy="tournoi")
    */
    protected $equipes; 

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TypeTournoi", inversedBy="tournois")
     * @ORM\JoinColumn(nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbr_tour;

    /**
     * @ORM\Column(type="integer")
     */
    private $current_tour;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_debut;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_fin;


    /**
    * @ORM\OneToMany(targetEntity="Match2", cascade={"persist", "remove"}, mappedBy="tournoi")
    */
    protected $matchs2; 

    /**
    * @ORM\OneToMany(targetEntity="Terrain2", cascade={"persist", "remove"}, mappedBy="tournoi")
    */
    protected $terrains2;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $refresh;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etat;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbr_joueur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbrJoueur_equipe;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $actif; 

    public function __construct()
    {
        $this->matchs = new ArrayCollection();
        $this->terrains = new ArrayCollection();
        $this->equipes = new ArrayCollection();
        $this->current_tour = 1;
        $this->etat = 'en_attente';
        $this->date_create = new \Datetime();
        $this->matchs2 = new ArrayCollection();
        $this->terrains2 = new ArrayCollection();
        $this->actif = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNbrEquipe(): ?int
    {
        return $this->nbr_equipe;
    }

    public function setNbrEquipe(int $nbr_equipe): self
    {
        $this->nbr_equipe = $nbr_equipe;

        return $this;
    }

    public function getNbrTerrain(): ?int
    {
        return $this->nbr_terrain;
    }

    public function setNbrTerrain(int $nbr_terrain): self
    {
        $this->nbr_terrain = $nbr_terrain;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->date_create;
    }

    public function setDateCreate(\DateTimeInterface $date_create): self
    {
        $this->date_create = $date_create;

        return $this;
    }

    /**
     * @return Collection|Match[]
     */
    public function getMatchs(): Collection
    {
        return $this->matchs;
    }

    public function addMatch(Match $match): self
    {
        if (!$this->matchs->contains($match)) {
            $this->matchs[] = $match;
            $match->setTournoi($this);
        }

        return $this;
    }

    public function removeMatch(Match $match): self
    {
        if ($this->matchs->contains($match)) {
            $this->matchs->removeElement($match);
            // set the owning side to null (unless already changed)
            if ($match->getTournoi() === $this) {
                $match->setTournoi(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Terrain[]
     */
    public function getTerrains(): Collection
    {
        return $this->terrains;
    }

    public function addTerrain(Terrain $terrain): self
    {
        if (!$this->terrains->contains($terrain)) {
            $this->terrains[] = $terrain;
            $terrain->setTournoi($this);
        }

        return $this;
    }

    public function removeTerrain(Terrain $terrain): self
    {
        if ($this->terrains->contains($terrain)) {
            $this->terrains->removeElement($terrain);
            // set the owning side to null (unless already changed)
            if ($terrain->getTournoi() === $this) {
                $terrain->setTournoi(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Equipe[]
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): self
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes[] = $equipe;
            $equipe->setTournoi($this);
        }

        return $this;
    }

    public function removeEquipe(Equipe $equipe): self
    {
        if ($this->equipes->contains($equipe)) {
            $this->equipes->removeElement($equipe);
            // set the owning side to null (unless already changed)
            if ($equipe->getTournoi() === $this) {
                $equipe->setTournoi(null);
            }
        }

        return $this;
    }

    public function getType(): ?TypeTournoi
    {
        return $this->type;
    }

    public function setType(?TypeTournoi $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNbrTour(): ?int
    {
        return $this->nbr_tour;
    }

    public function setNbrTour(int $nbr_tour): self
    {
        $this->nbr_tour = $nbr_tour;

        return $this;
    }

    public function getCurrentTour(): ?int
    {
        return $this->current_tour;
    }

    public function setCurrentTour(int $current_tour): self
    {
        $this->current_tour = $current_tour;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    /**
     * @return Collection|Match2[]
     */
    public function getMatchs2(): Collection
    {
        return $this->matchs2;
    }

    public function addMatchs2(Match2 $matchs2): self
    {
        if (!$this->matchs2->contains($matchs2)) {
            $this->matchs2[] = $matchs2;
            $matchs2->setTournoi($this);
        }

        return $this;
    }

    public function removeMatchs2(Match2 $matchs2): self
    {
        if ($this->matchs2->contains($matchs2)) {
            $this->matchs2->removeElement($matchs2);
            // set the owning side to null (unless already changed)
            if ($matchs2->getTournoi() === $this) {
                $matchs2->setTournoi(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Terrain2[]
     */
    public function getTerrains2(): Collection
    {
        return $this->terrains2;
    }

    public function addTerrains2(Terrain2 $terrains2): self
    {
        if (!$this->terrains2->contains($terrains2)) {
            $this->terrains2[] = $terrains2;
            $terrains2->setTournoi($this);
        }

        return $this;
    }

    public function removeTerrains2(Terrain2 $terrains2): self
    {
        if ($this->terrains2->contains($terrains2)) {
            $this->terrains2->removeElement($terrains2);
            // set the owning side to null (unless already changed)
            if ($terrains2->getTournoi() === $this) {
                $terrains2->setTournoi(null);
            }
        }

        return $this;
    }

    public function getRefresh(): ?bool
    {
        return $this->refresh;
    }

    public function setRefresh(?bool $refresh): self
    {
        $this->refresh = $refresh;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getNbrJoueur(): ?int
    {
        return $this->nbr_joueur;
    }

    public function setNbrJoueur(int $nbr_joueur): self
    {
        $this->nbr_joueur = $nbr_joueur;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getNbrJoueurEquipe(): ?int
    {
        return $this->nbrJoueur_equipe;
    }

    public function setNbrJoueurEquipe(int $nbrJoueur_equipe): self
    {
        $this->nbrJoueur_equipe = $nbrJoueur_equipe;

        return $this;
    }

    public function getActif(): ?int
    {
        return $this->actif;
    }

    public function setActif(?int $actif): self
    {
        $this->actif = $actif;

        return $this;
    }
}
