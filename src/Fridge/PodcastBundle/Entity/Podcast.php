<?php

namespace Fridge\PodcastBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Cocur\Slugify\Slugify;

/**
 * Podcast
 *
 * @ORM\Table()
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("all")
 */
class Podcast
{
    /**
     * @param array $podcastData
     * @return Podcast
     */
    public static function create(array $podcastData)
    {
        $podcast = new Podcast();
        $slugify = new Slugify();

        $date = new \DateTime($podcastData['entries'][0]['publishedDate']);

        $podcast
            ->setFeed($podcastData['feedUrl'])
            ->setName($podcastData['title'])
            ->setAmount(count($podcastData['entries']))
            ->setNewEpisodes(count($podcastData['entries']))
            ->setArtist($podcastData['artistName'])
            ->setImageUrl30($podcastData['artworkUrl30'])
            ->setImageUrl100($podcastData['artworkUrl100'])
            ->setExplicit($podcastData['collectionExplicitness'] !== 'notExplicit')
            ->setCountry($podcastData['country'])
            ->setSlug($slugify->slugify($podcastData['title']))
            ->setGenres($podcastData['genres'])
            ->setItunesId($podcastData['collectionId'])
            ->setLatest($date)
            ->setLatestEpisode($podcastData['entries'][0])
        ;

        return $podcast;
    }


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @Serializer\Expose()
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="feed", type="string", length=255)
     */
    private $feed;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="artist", type="string", length=255)
     */
    private $artist;

    /**
     * @var string
     *
     * @ORM\Column(name="image_url_30", type="string", length=255)
     */
    private $imageUrl30;

    /**
     * @var string
     *
     * @ORM\Column(name="image_url_100", type="string", length=255)
     */
    private $imageUrl100;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var boolean
     * @Serializer\Expose()
     * @ORM\Column(name="explicit", type="boolean")
     */
    private $explicit;

    /**
     * @var array
     * @Serializer\Expose()
     * @ORM\Column(name="genres", type="simple_array")
     */
    private $genres;

    /**
     * @var array
     * @Serializer\Expose()
     * @ORM\Column(name="heard", type="simple_array")
     */
    private $heard;

    /**
     * @var integer
     * @Serializer\Expose()
     * @Serializer\SerializedName("itunesId")
     * @ORM\Column(name="itunes_id", type="integer")
     */
    private $itunesId;

    /**
     * @var \DateTime
     * @Serializer\Expose()
     * @ORM\Column(name="latest", type="datetimetz")
     */
    private $latest;

    /**
     * @var \DateTime
     * @Serializer\Expose()
     * @Serializer\SerializedName("latestEpisode")
     * @ORM\Column(name="latest_episode", type="array")
     */
    private $latestEpisode;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     * @Serializer\Expose()
     * @Serializer\SerializedName("newEpisodes")
     * @ORM\Column(name="new_episodes", type="integer")
     */
    private $newEpisodes;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return Podcast
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set feed
     *
     * @param $feed
     * @return $this
     */
    public function setFeed($feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * @return string
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Set artist
     *
     * @param string $artist
     * @return Podcast
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return string 
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set imageUrl30
     *
     * @param string $imageUrl30
     * @return Podcast
     */
    public function setImageUrl30($imageUrl30)
    {
        $this->imageUrl30 = $imageUrl30;

        return $this;
    }

    /**
     * Get imageUrl30
     *
     * @return string 
     */
    public function getImageUrl30()
    {
        return $this->imageUrl30;
    }

    /**
     * Set imageUrl100
     *
     * @param string $imageUrl100
     * @return Podcast
     */
    public function setImageUrl100($imageUrl100)
    {
        $this->imageUrl100 = $imageUrl100;

        return $this;
    }

    /**
     * Get imageUrl100
     *
     * @return string 
     */
    public function getImageUrl100()
    {
        return $this->imageUrl100;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Podcast
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set explicit
     *
     * @param boolean $explicit
     * @return Podcast
     */
    public function setExplicit($explicit)
    {
        $this->explicit = $explicit;

        return $this;
    }

    /**
     * Get explicit
     *
     * @return boolean 
     */
    public function getExplicit()
    {
        return $this->explicit;
    }

    /**
     * Set genres
     *
     * @param array $genres
     * @return Podcast
     */
    public function setGenres($genres)
    {
        $this->genres = $genres;

        return $this;
    }

    /**
     * Get genres
     *
     * @return array 
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * Set heard
     *
     * @param array $heard
     * @return Podcast
     */
    public function setHeard($heard)
    {
        $this->heard = $heard;

        return $this;
    }

    /**
     * Get heard
     *
     * @return array 
     */
    public function getHeard()
    {
        return $this->heard;
    }

    /**
     * Set itunesId
     *
     * @param integer $itunesId
     * @return Podcast
     */
    public function setItunesId($itunesId)
    {
        $this->itunesId = $itunesId;

        return $this;
    }

    /**
     * Get itunesId
     *
     * @return integer 
     */
    public function getItunesId()
    {
        return $this->itunesId;
    }

    /**
     * Set latest
     *
     * @param \DateTime $latest
     * @return Podcast
     */
    public function setLatest($latest)
    {
        $this->latest = $latest;

        return $this;
    }

    /**
     * Get latest
     *
     * @return \DateTime 
     */
    public function getLatest()
    {
        return $this->latest;
    }

    /**
     * @param $latestEpisode
     * @return $this
     */
    public function setLatestEpisode($latestEpisode)
    {
        $this->latestEpisode = $latestEpisode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatestEpisode()
    {
        return $this->latestEpisode;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Podcast
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set newEpisodes
     *
     * @param integer $newEpisodes
     * @return Podcast
     */
    public function setNewEpisodes($newEpisodes)
    {
        $this->newEpisodes = $newEpisodes;

        return $this;
    }

    /**
     * Get newEpisodes
     *
     * @return integer 
     */
    public function getNewEpisodes()
    {
        return $this->newEpisodes;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Podcast
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get artwork
     * @Serializer\VirtualProperty
     * @return array
     */
    public function getArtwork()
    {
        return [
            '30' => $this->getImageUrl30(),
            '100' => $this->getImageUrl100()
        ];
    }
}
