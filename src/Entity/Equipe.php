<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeRepository")
 */
class Equipe
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $joueurs;

    /**
     * @ORM\Column(type="boolean")
     */
    private $en_competition;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Match", inversedBy="equipes")
     */
    private $matchs;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournoi", inversedBy="equipes")
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
     */
    private $tournoi;

    public function __construct()
    {
        $this->matchs = new ArrayCollection();
        $this->en_competition = true;
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

    public function getJoueurs(): ?string
    {
        return $this->joueurs;
    }

    public function setJoueurs(string $joueurs): self
    {
        $this->joueurs = $joueurs;

        return $this;
    }

    public function getEnCompetition(): ?bool
    {
        return $this->en_competition;
    }

    public function setEnCompetition(bool $en_competition): self
    {
        $this->en_competition = $en_competition;

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
        }

        return $this;
    }

    public function removeMatch(Match $match): self
    {
        if ($this->matchs->contains($match)) {
            $this->matchs->removeElement($match);
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
}
