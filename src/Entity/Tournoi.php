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
     * @ORM\Column(type="integer")
     */
    private $nbr_terrain;

    /**
     * @ORM\Column(type="integer")
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

    public function __construct()
    {
        $this->matchs = new ArrayCollection();
        $this->terrains = new ArrayCollection();
        $this->equipes = new ArrayCollection();
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
}
