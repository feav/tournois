<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Terrain2Repository")
 */
class Terrain2
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournoi", inversedBy="terrains2")
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
     */
    private $tournoi;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Match2", cascade={"persist"}, mappedBy="terrain2")
    */
    protected $match2;

    public function __construct()
    {
        $this->occupe = false;
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

    public function getOccupe(): ?bool
    {
        return $this->occupe;
    }

    public function setOccupe(bool $occupe): self
    {
        $this->occupe = $occupe;

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

    public function getMatch2(): ?Match2
    {
        return $this->match2;
    }

    public function setMatch2(?Match2 $match2): self
    {
        $this->match2 = $match2;

        // set (or unset) the owning side of the relation if necessary
        $newTerrain2 = null === $match2 ? null : $this;
        if ($match2->getTerrain2() !== $newTerrain2) {
            $match2->setTerrain2($newTerrain2);
        }

        return $this;
    }
}
