<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 12/01/2014
 * Time: 20:37
 */

namespace Fridge\UserBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fridge_user_group")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
} 