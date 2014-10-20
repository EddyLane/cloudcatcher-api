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
     * @param $baseUrl
     * @param $secret
     * @param Logger $logger
     */
    public function __construct($baseUrl, $secret, Logger $logger)
    {
        $logger->debug(sprintf('Connecting to firebase base_url "%s" secret "%s"', $baseUrl, $secret));

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
            json_decode($this->getSerializer()->serialize($podcast, 'json')),
            true
        );
    }

} 