<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 22:10
 */

namespace Fridge\FirebaseBundle\Client;

use Firebase\Firebase;
use Fridge\PodcastBundle\Entity\Podcast;
use Fridge\UserBundle\Entity\User;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use Monolog\Logger;

/**
 * Class FirebaseClient
 * @package Fridge\FirebaseBundle\Client
 */
class FirebaseClient
{
    /**
     * @var \Firebase\Firebase
     */
    protected $client;

    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * @param $baseUrl
     * @param $secret
     * @param Logger $logger
     * @param Serializer $serializer
     */
    public function __construct($baseUrl, $secret, Logger $logger, Serializer $serializer)
    {
        $logger->debug(sprintf('Connecting to firebase base_url "%s" secret "%s"', $baseUrl, $secret));

        $this->serializer = $serializer;

        $this->client = new Firebase([
            'base_url' => $baseUrl,
            'token' => $secret
        ], new Client());
    }

    /**
     * @return Firebase
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param User $user
     * @param Podcast $podcast
     * @return mixed
     */
    public function addPodcast(User $user, Podcast $podcast)
    {
        return $this->getClient()->push(
            sprintf('/users/%s/podcasts', $user->getUsernameCanonical()),
            json_decode($this->serializer->serialize($podcast, 'json')),
            true
        );
    }

    public function updatePodcastLatest(User $user, Podcast $podcast)
    {
        $this->getClient()->update(
            sprintf('/users/%s/podcasts/%s', $user->getUsernameCanonical(), $podcast->getFirebaseKey()),
            [
                'newEpisodes' => $podcast->getNewEpisodes(),
                'latest' => $podcast->getLatest(),
                'latestEpisode' => $podcast->getLatestEpisode()
            ]
        );
    }

} 