<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    * @ORM\OneToMany(targetEntity="App\Entity\Match2", cascade={"persist", "remove"}, mappedBy="terrain2")
    */
    protected $matchs2;

    public function __construct()
    {
        $this->occupe = false;
        $this->matchs2 = new ArrayCollection();
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
            $matchs2->setTerrain2($this);
        }

        return $this;
    }

    public function removeMatchs2(Match2 $matchs2): self
    {
        if ($this->matchs2->contains($matchs2)) {
            $this->matchs2->removeElement($matchs2);
            // set the owning side to null (unless already changed)
            if ($matchs2->getTerrain2() === $this) {
                $matchs2->setTerrain2(null);
            }
        }

        return $this;
    }
}
