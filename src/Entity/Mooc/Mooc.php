<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 *
 * @ORM\Table(
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="mooc_slug", columns="slug")
 *   }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Mooc
{
    use EntityTimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=2, max=400)
     */
    protected $description;

    /**
     * @var string|null
     *
     * @ORM\Column
     * @Gedmo\Slug(fields={"name"}, unique=true)
     */
    protected $slug;

    /**
     * @var Chapter[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Chapter", mappedBy="mooc", cascade={"all"})
     * @ORM\OrderBy({"displayOrder"="ASC"})
     *
     * @Assert\Valid
     */
    protected $chapters;

    public function __construct(string $name = null, string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->chapters = new ArrayCollection();
    }

    /**
     * Returns the primary key identifier.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return Chapter[]|Collection|iterable
     */
    public function getChapters(): iterable
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): void
    {
        if (!$this->chapters->contains($chapter)) {
            $chapter->setMooc($this);
            $this->chapters->add($chapter);
        }
    }

    public function removeChapter(Chapter $chapter): void
    {
        $chapter->detachMooc();
        $this->chapters->removeElement($chapter);
    }
}
