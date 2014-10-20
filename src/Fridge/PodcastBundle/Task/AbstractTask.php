<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 20/10/2014
 * Time: 13:05
 */

namespace Fridge\PodcastBundle\Task;

use Fridge\ApiBundle\Client\GoogleFeedClient;
use Fridge\ApiBundle\Client\ItunesSearchClient;
use Fridge\FirebaseBundle\Client\FirebaseClient;
use JMS\Serializer\Serializer;
use Predis\Client;

abstract class AbstractTask
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
     * @param string $feed
     * @return array
     */
    protected function getGoogleFeedData ($feed)
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

        return json_decode((String) $googleFeedData, true)['responseData']['feed'];
    }

    /**
     * @param integer $itunesId
     * @return array
     */
    protected function getItunesLookupData ($itunesId)
    {
        if (!$itunesFeedData = $this->redis->get('itunes:' . $itunesId)) {

            $itunesFeedData = $this->itunesSearchClient->get('lookup', [
                'query' => [
                    'id' => $itunesId,
                    'kind' => 'podcast'
                ]
            ])->getBody();

            $this->redis->set('itunes:' . $itunesId, $itunesFeedData);

        }

        return json_decode((String) $itunesFeedData, true)['results'][0];
    }

    /**
     * @return FirebaseClient
     */
    protected function getFirebaseClient()
    {
        return $this->firebaseClient;
    }

    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        return $this->serializer;
    }

} 