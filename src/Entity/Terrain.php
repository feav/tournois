<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerrainRepository")
 */
class Terrain
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
     * @ORM\Column(type="boolean")
     */
    private $occupe;

    /**
     * @ORM\OneToOne(targetEntity=Match::class, cascade={"persist"}, mappedBy="terrain")
    */
    protected $match;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournoi", inversedBy="terrains")
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
     */
    private $tournoi;

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

    public function getOccupe(): ?bool
    {
        return $this->occupe;
    }

    public function setOccupe(bool $occupe): self
    {
        $this->occupe = $occupe;

        return $this;
    }

    public function getMatch(): ?Match
    {
        return $this->match;
    }

    public function setMatch(?Match $match): self
    {
        $this->match = $match;

        // set (or unset) the owning side of the relation if necessary
        $newTerrain = null === $match ? null : $this;
        if ($match->getTerrain() !== $newTerrain) {
            $match->setTerrain($newTerrain);
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
