<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="mooc_chapter",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="mooc_chapter_video_slug", columns="slug"),
 *         @ORM\UniqueConstraint(name="mooc_chapter_video_order_display_by_chapter", columns={"display_order", "chapter_id"})
 *     }
 * )
 *
 * @UniqueEntity(
 *     fields={"displayOrder", "chapter"},
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class Video extends BaseElement
{
    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     * @Assert\Length(min=7, max=200)
     */
    protected $youtubeUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     * @Assert\Length(min=7, max=200)
     */
    protected $youtubeThumbnailUrl;

    /**
     * @ORM\Column(length=800, nullable=true)
     *
     * @Assert\Length(min=5, max=800)
     */
    protected $content;

    /**
     * @var Chapter
     *
     * @ORM\ManyToOne(targetEntity="Chapter", inversedBy="videos")
     */
    protected $chapter;

    public function __construct(
        string $name = null,
        string $youtubeUrl = null,
        string $youtubeThumbnailUrl = null,
        string $content = null,
        int $displayOrder = null
    ) {
        $this->name = $name;
        $this->youtubeUrl = $youtubeUrl;
        $this->youtubeThumbnailUrl = $youtubeThumbnailUrl;
        $this->content = $content;
        $this->displayOrder = $displayOrder;
    }

    public function getYoutubeUrl(): ?string
    {
        return $this->youtubeUrl;
    }

    public function setYoutubeUrl(string $youtubeUrl): void
    {
        $this->youtubeUrl = $youtubeUrl;
    }

    public function getYoutubeThumbnailUrl(): ?string
    {
        return $this->youtubeThumbnailUrl;
    }

    public function setYoutubeThumbnailUrl(string $youtubeThumbnailUrl): void
    {
        $this->youtubeThumbnailUrl = $youtubeThumbnailUrl;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(Chapter $chapter): void
    {
        $this->chapter = $chapter;
    }

    public function detachChapter(): void
    {
        $this->chapter = null;
    }
}
