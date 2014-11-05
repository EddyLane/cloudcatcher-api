<?php

namespace Fridge\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * UserPreference
 *
 * @ORM\Table("fridge_user_preference")
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("all")
 */
class UserPreference
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="limitEpisodes", type="smallint")
     * @Serializer\Expose()
     */
    private $limitEpisodes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="downloadEpisodes", type="boolean")
     * @Serializer\Expose()
     */
    private $downloadEpisodes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deletePlayedEpisodes", type="boolean")
     * @Serializer\Expose()
     */
    private $deletePlayedEpisodes;


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
     * Set limitEpisodes
     *
     * @param integer $limitEpisodes
     * @return UserPreference
     */
    public function setLimitEpisodes($limitEpisodes)
    {
        $this->limitEpisodes = $limitEpisodes;

        return $this;
    }

    /**
     * Get limitEpisodes
     *
     * @return integer 
     */
    public function getLimitEpisodes()
    {
        return $this->limitEpisodes;
    }

    /**
     * Set downloadEpisodes
     *
     * @param boolean $downloadEpisodes
     * @return UserPreference
     */
    public function setDownloadEpisodes($downloadEpisodes)
    {
        $this->downloadEpisodes = $downloadEpisodes;

        return $this;
    }

    /**
     * Get downloadEpisodes
     *
     * @return boolean 
     */
    public function getDownloadEpisodes()
    {
        return $this->downloadEpisodes;
    }

    /**
     * Set deletePlayedEpisodes
     *
     * @param boolean $deletePlayedEpisodes
     * @return UserPreference
     */
    public function setDeletePlayedEpisodes($deletePlayedEpisodes)
    {
        $this->deletePlayedEpisodes = $deletePlayedEpisodes;

        return $this;
    }

    /**
     * Get deletePlayedEpisodes
     *
     * @return boolean 
     */
    public function getDeletePlayedEpisodes()
    {
        return $this->deletePlayedEpisodes;
    }
}
