<?php

namespace Fridge\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Fridge\ApiBundle\Entity\GcmId;
use Fridge\SubscriptionBundle\Entity\StripeProfile;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fridge_user_user")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose()
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Fridge\SubscriptionBundle\Entity\StripeProfile", cascade={"all"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\JoinColumn(name="stripe_profile_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Expose()
     */
    protected $stripeProfile;

    /**
     * @ORM\ManyToMany(targetEntity="Fridge\UserBundle\Entity\Group")
     * @ORM\JoinTable(name="fridge_user_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @Serializer\Expose()
     */
    protected $firebaseToken;

    /**
     * @ORM\OneToMany(targetEntity="Fridge\ApiBundle\Entity\GcmId", mappedBy="user", cascade={"all"}, fetch="EAGER")
     * @Serializer\Expose()
     */
    protected $gcmIds;

    /**
     *
     */
    public function __construct()
    {
        $this->gcmIds = new ArrayCollection();
    }

    /**
     * @param $firebaseToken
     * @return $this
     */
    public function setFirebaseToken($firebaseToken)
    {
        $this->firebaseToken = $firebaseToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirebaseToken()
    {
        return $this->firebaseToken;
    }

    /**
     * @param StripeProfile $stripeProfile
     * @return $this
     */
    public function setStripeProfile(StripeProfile $stripeProfile)
    {
        $this->stripeProfile = $stripeProfile;

        return $this;
    }

    /**
     * @return StripeProfile
     */
    public function getStripeProfile()
    {
        if (!$this->stripeProfile) {
            $this->setStripeProfile(new StripeProfile);
        }

        return $this->stripeProfile;
    }

    /**
     * @param GcmId $gcmId
     */
    public function addGcmId(GcmId $gcmId)
    {
        $this->gcmIds->add($gcmId);
    }

    /**
     * @param GcmId $gcmId
     */
    public function removeGcmId(GcmId $gcmId)
    {
        $this->gcmIds->remove($gcmId);
    }

    /**
     * @return ArrayCollection
     */
    public function getGcmIds()
    {
        return $this->gcmIds;
    }

}