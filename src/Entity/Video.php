<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VideoRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Video
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, options={"default" : "video"}, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Trick::class, inversedBy="videos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trick;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $provider;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $video_id;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if ($this->trick) {
            $this->trick->setUpdatedAt(new \DateTime());
        }
        $this->checkVideoId();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->checkVideoId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getVideoId(): ?string
    {
        return $this->video_id;
    }

    public function setVideoId(string $video_id): self
    {
        $this->video_id = $video_id;

        return $this;
    }

    public function checkVideoId(): self
    {
        if ($this->provider === 'youtube') {
            parse_str( parse_url( $this->video_id, PHP_URL_QUERY ), $vars );
            if (isset($vars['v'])) {
                $this->setVideoId($vars['v']);
            } elseif (isset(explode('youtu.be/', $this->video_id)[1])) {
                $this->setVideoId(explode('youtu.be/', $this->video_id)[1]);
            } elseif (isset(explode('embed/', $this->video_id)[1])) {
                $this->setVideoId(explode('embed/', $this->video_id)[1]);
            } 
        } elseif ($this->provider === 'dailymotion') {
            if (isset(explode('video/', $this->video_id)[1])) {
                $this->setVideoId(explode('video/', $this->video_id)[1]);
            } elseif (isset(explode('embed/video/', $this->video_id)[1])) {
                $this->setVideoId(explode('embed/video/', $this->video_id)[1]);
            } elseif (isset(explode('dai.ly/', $this->video_id)[1])) {
                $this->setVideoId(explode('dai.ly/', $this->video_id)[1]);
            }
        } elseif ($this->provider === 'vimeo') {
            if (isset(explode('vimeo.com/', $this->video_id)[1])) {
                $this->setVideoId(explode('vimeo.com/', $this->video_id)[1]);
            } elseif (isset(explode('player.vimeo.com/video/', $this->video_id)[1])) {
                $this->setVideoId(explode('player.vimeo.com/video/', $this->video_id)[1]);
            }
        }

        return $this;
    }

    public function getEmbedUrl(): string
    {
        if ($this->provider === 'youtube') {
            return 'https://www.youtube.com/embed/' . $this->video_id;
        } elseif ($this->provider === 'dailymotion') {
            return 'https://www.dailymotion.com/embed/video/' . $this->video_id;
        } elseif ($this->provider === 'vimeo') {
            return 'https://player.vimeo.com/video/' . $this->video_id;
        } else {
            return '';
        }
    }
}
