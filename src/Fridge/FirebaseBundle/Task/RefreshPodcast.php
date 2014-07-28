<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 21:07
 */

namespace Fridge\FirebaseBundle\Task;


use Fridge\UserBundle\Entity\User;

class RefreshPodcast
{
    private $producer;


    public function __construct($producer)
    {
        $this->producer = $producer;
    }

    public function process(User $user)
    {
        $this->producer->publish($user);
    }

}