<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TypeTournoiRepository")
 */
class TypeTournoi
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
    private $description;

    /**
    * @ORM\OneToMany(targetEntity="Tournoi", cascade={"persist"}, mappedBy="type")
    */
    protected $tournois;

    public function __construct()
    {
        $this->tournois = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Tournoi[]
     */
    public function getTournois(): Collection
    {
        return $this->tournois;
    }

    public function addTournois(Tournoi $tournois): self
    {
        if (!$this->tournois->contains($tournois)) {
            $this->tournois[] = $tournois;
            $tournois->setType($this);
        }

        return $this;
    }

    public function removeTournois(Tournoi $tournois): self
    {
        if ($this->tournois->contains($tournois)) {
            $this->tournois->removeElement($tournois);
            // set the owning side to null (unless already changed)
            if ($tournois->getType() === $this) {
                $tournois->setType(null);
            }
        }

        return $this;
    }
}
