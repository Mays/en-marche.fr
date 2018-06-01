<?php

namespace AppBundle\Entity\Mooc;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Quizz extends BaseElement
{
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $typeForm;

    /**
     * @var Collection|QuizzLink[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Mooc\QuizzLink", mappedBy="quizz", cascade={"persist"}, orphanRemoval=true)
     */
    private $links;

    /**
     * @var Collection|QuizzFile[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Mooc\QuizzFile", mappedBy="quizz", cascade={"persist"}, orphanRemoval=true)
     */
    private $files;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getTypeForm(): ?string
    {
        return $this->typeForm;
    }

    public function setTypeForm(string $typeForm): void
    {
        $this->typeForm = $typeForm;
    }

    /**
     * @return Collection|QuizzLink[]
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(QuizzLink $link): void
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setQuizz($this);
        }
    }

    public function removeLink(QuizzLink $link): void
    {
        if ($this->links->contains($link)) {
            $this->links->removeElement($link);
            $link->setQuizz(null);
        }
    }

    /**
     * @return Collection|QuizzFile[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(QuizzFile $file): void
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setQuizz($this);
        }
    }

    public function removeFile(QuizzFile $file): void
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            $file->setQuizz(null);
        }
    }
}
