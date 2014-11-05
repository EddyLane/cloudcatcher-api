<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 21:07
 */

namespace Fridge\FirebaseBundle\Task;

use Fridge\UserBundle\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class RefreshPodcast
 * @package Fridge\FirebaseBundle\Task
 */
class RefreshPodcast
{
    /**
     * @var \OldSound\RabbitMqBundle\RabbitMq\Producer
     */
    private $producer;

    /**
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * @param User $user
     */
    public function process(User $user)
    {
        $this->producer->publish($user->getUsernameCanonical());
    }

}