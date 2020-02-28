<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Match2Repository")
 */
class Match2
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $score;

    /**
     * @ORM\Column(type="integer")
     */
    private $duree;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_debut;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vainqueur;

    /**
     * @ORM\Column(type="integer")
     */
    private $num_tour;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_fin;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Equipe", mappedBy="matchs2")
     */
    protected $equipes;

     /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournoi", inversedBy="matchs2")
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
     */
    private $tournoi;

    /**
    * @ORM\OneToOne(targetEntity="App\Entity\Terrain", cascade={"persist"}, inversedBy="match2")
    * @ORM\JoinColumn(nullable=true)
    */
    protected $terrain;

    /**
    * @ORM\OneToOne(targetEntity="App\Entity\Terrain2", cascade={"persist"}, inversedBy="match2")
    * @ORM\JoinColumn(nullable=true)
    */
    protected $terrain2;

    public function __construct()
    {
        $this->etat = "en_attente";
        $this->equipes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(?string $score): self
    {
        $this->score = $score;

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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getVainqueur(): ?int
    {
        return $this->vainqueur;
    }

    public function setVainqueur(?int $vainqueur): self
    {
        $this->vainqueur = $vainqueur;

        return $this;
    }

    public function getNumTour(): ?int
    {
        return $this->num_tour;
    }

    public function setNumTour(int $num_tour): self
    {
        $this->num_tour = $num_tour;

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
            $equipe->addMatchs2($this);
        }

        return $this;
    }

    public function removeEquipe(Equipe $equipe): self
    {
        if ($this->equipes->contains($equipe)) {
            $this->equipes->removeElement($equipe);
            $equipe->removeMatchs2($this);
        }

        return $this;
    }

    public function getTournoi(): ?Tournoi
    {
        return $this->tournoi;
    }

    public function setTournoi(?Tournoi $tournoi): self
    {
        $this->tournoi = $tournoi;

        return $this;
    }

    public function getTerrain(): ?Terrain
    {
        return $this->terrain;
    }

    public function setTerrain(?Terrain $terrain): self
    {
        $this->terrain = $terrain;

        return $this;
    }

    public function getTerrain2(): ?Terrain2
    {
        return $this->terrain2;
    }

    public function setTerrain2(?Terrain2 $terrain2): self
    {
        $this->terrain2 = $terrain2;

        return $this;
    }
}
