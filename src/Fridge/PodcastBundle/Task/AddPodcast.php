<?php

/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 20/10/2014
 * Time: 11:26
 */

namespace Fridge\PodcastBundle\Task;

use Fridge\PodcastBundle\Entity\Podcast;
use Fridge\UserBundle\Entity\User;

/**
 * Class AddPodcast
 * @package Fridge\PodcastBundle\Task
 */
class AddPodcast extends AbstractTask
{
    /**
     * Add a podcast to the users firebase
     *
     * @param User $user
     * @param $feed
     * @param $itunesId
     * @return Podcast
     */
    public function execute(User $user, $feed, $itunesId)
    {
        $podcast = Podcast::create(array_merge(
            $this->getGoogleFeedData($feed),
            $this->getItunesLookupData($itunesId)
        ));

        $this->getFirebaseClient()->addPodcast($user, $podcast);

        return $podcast;
    }

} 