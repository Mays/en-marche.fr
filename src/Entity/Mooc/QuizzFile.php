<?php

namespace AppBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class QuizzFile
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $path;

    /**
     * @var Quizz
     *
     * @ORM\ManyToOne(targetEntity="Quizz", inversedBy="files", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $quizz;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getQuizz(): ?Quizz
    {
        return $this->quizz;
    }

    public function setQuizz(?Quizz $quizz): void
    {
        $this->quizz = $quizz;
    }
}
