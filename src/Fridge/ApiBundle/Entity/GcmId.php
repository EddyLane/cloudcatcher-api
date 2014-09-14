<?php

namespace Fridge\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fridge\UserBundle\Entity\User;

/**
 * GcmId
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class GcmId
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="gcm_id", type="string", length=1024)
     */
    private $gcmId;

    /**
     * @var \Fridge\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Fridge\UserBundle\Entity\User", cascade={"all"}, fetch="EAGER", inversedBy="gcmIds")
     */
    private $user;

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Fridge\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

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
     * Set gcmId
     *
     * @param string $gcmId
     * @return GcmId
     */
    public function setGcmId($gcmId)
    {
        $this->gcmId = $gcmId;

        return $this;
    }

    /**
     * Get gcmId
     *
     * @return string 
     */
    public function getGcmId()
    {
        return $this->gcmId;
    }
}
