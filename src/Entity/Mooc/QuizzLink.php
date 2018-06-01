<?php

namespace AppBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class QuizzLink
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
    private $url;

    /**
     * @var Quizz
     *
     * @ORM\ManyToOne(targetEntity="Quizz", inversedBy="links", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $quizz;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getQuizz(): Quizz
    {
        return $this->quizz;
    }

    public function setQuizz(Quizz $quizz): void
    {
        $this->quizz = $quizz;
    }
}
