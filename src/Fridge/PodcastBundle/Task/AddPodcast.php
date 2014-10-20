<?php

/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 20/10/2014
 * Time: 11:26
 */

namespace Fridge\PodcastBundle\Task;

use Fridge\ApiBundle\Client\GoogleFeedClient;
use Fridge\ApiBundle\Client\ItunesSearchClient;
use Fridge\FirebaseBundle\Client\FirebaseClient;
use Fridge\PodcastBundle\Entity\Podcast;
use Fridge\UserBundle\Entity\User;
use JMS\Serializer\Serializer;
use Predis\Client;

/**
 * Class AddPodcast
 * @package Fridge\PodcastBundle\Task
 */
class AddPodcast
{
    /**
     * @var \Fridge\FirebaseBundle\Client\FirebaseClient
     */
    private $firebaseClient;

    /**
     * @var \Fridge\ApiBundle\Client\GoogleFeedClient
     */
    private $googleFeedClient;

    /**
     * @var \Fridge\ApiBundle\Client\ItunesSearchClient
     */
    private $itunesSearchClient;

    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * @var \Predis\Client
     */
    private $redis;

    /**
     * @param FirebaseClient $firebaseClient
     * @param GoogleFeedClient $googleFeedClient
     * @param ItunesSearchClient $itunesSearchClient
     * @param Serializer $serializer
     * @param Client $redis
     */
    public function __construct (
        FirebaseClient $firebaseClient,
        GoogleFeedClient $googleFeedClient,
        ItunesSearchClient $itunesSearchClient,
        Serializer $serializer,
        Client $redis
    ) {
        $this->firebaseClient = $firebaseClient;
        $this->googleFeedClient = $googleFeedClient;
        $this->itunesSearchClient = $itunesSearchClient;
        $this->serializer = $serializer;
        $this->redis = $redis;
    }

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

        if (!$googleFeedData = $this->redis->get('feed:' . $feed)) {

            $googleFeedData = $this->googleFeedClient->get('load', [
                'query' => [
                    'q' => $feed,
                    'v' => '1.0',
                    'num' => -1,
                    'output' => 'json_xml'
                ]
            ])->getBody();

            $this->redis->setex('feed:' . $feed, 3600, $googleFeedData);
        }

        $googleFeedData = json_decode((String) $googleFeedData, true);

        if (!$itunesFeedData = $this->redis->get('itunes:' . $itunesId)) {

            $itunesFeedData = $this->itunesSearchClient->get('lookup', [
                'query' => [
                    'id' => $itunesId,
                    'kind' => 'podcast'
                ]
            ])->getBody();

            $this->redis->set('itunes:' . $itunesId, $itunesFeedData);

        }

        $itunesFeedData = json_decode((String) $itunesFeedData, true);

        $merged = array_merge(
            $googleFeedData['responseData']['feed'],
            $itunesFeedData['results'][0]
        );

        $podcast = Podcast::create($merged);

        $this->firebaseClient->getClient()->push(
            '/users/' . $user->getUsernameCanonical() . '/podcasts',
            json_decode($this->serializer->serialize($podcast, 'json')), true)
        ;

        return $podcast;
    }

} 