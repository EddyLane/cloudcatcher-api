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
use Fridge\ApiBundle\Message\Message;
use Fridge\ApiBundle\Notification\GCMNotification;
use Fridge\FirebaseBundle\Client\FirebaseClient;
use Fridge\PodcastBundle\Entity\Podcast;
use Fridge\UserBundle\Entity\User;
use JMS\Serializer\Serializer;
use Monolog\Logger;
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
    protected $serializer;

    /**
     * @var \Predis\Client
     */
    private $redis;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Fridge\ApiBundle\Notification\GCMNotification
     */
    private $GCMNotification;

    /**
     * @param FirebaseClient $firebaseClient
     * @param GoogleFeedClient $googleFeedClient
     * @param ItunesSearchClient $itunesSearchClient
     * @param GCMNotification $GCMNotification
     * @param Serializer $serializer
     * @param Client $redis
     * @param Logger $logger
     */
    public function __construct (
        FirebaseClient $firebaseClient,
        GoogleFeedClient $googleFeedClient,
        ItunesSearchClient $itunesSearchClient,
        GCMNotification $GCMNotification,
        Serializer $serializer,
        Client $redis,
        Logger $logger
    ) {
        $this->firebaseClient = $firebaseClient;
        $this->googleFeedClient = $googleFeedClient;
        $this->itunesSearchClient = $itunesSearchClient;
        $this->serializer = $serializer;
        $this->redis = $redis;
        $this->logger = $logger;
        $this->GCMNotification = $GCMNotification;
    }


    /**
     * @param $feed
     * @return mixed
     */
    protected function getGoogleFeedData ($feed)
    {
        $this->getLogger()->debug(sprintf('Attempting to get redis data for "%s"', $feed));

        if (!$googleFeedData = $this->redis->get('feed:' . $feed)) {

            $this->getLogger()->debug(sprintf('Redis failure for "%s". Attempting to get live RSS data', $feed));

            $googleFeedData = $this->googleFeedClient->get('load', [
                'query' => [
                    'q' => $feed,
                    'v' => '1.0',
                    'num' => -1,
                    'output' => 'json_xml'
                ]
            ])->getBody();

            $this->redis->setex('feed:' . $feed, 3600, (String) $googleFeedData);

            $this->getLogger()->debug(sprintf('Got live RSS data for "%s" setting in redis', $feed));

        } else {

            $this->getLogger()->debug(sprintf('Redis success for "%s"', $feed));

        }

        return json_decode((String) $googleFeedData, true)['responseData'];
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


    protected function getFirebase(User $user)
    {
        return $this
            ->getFirebaseClient()
            ->getClient()
            ->get(sprintf('/users/%s/podcasts', $user->getUsernameCanonical()))
        ;
    }

    /**
     * @return FirebaseClient
     */
    protected function getFirebaseClient()
    {
        return $this->firebaseClient;
    }

    /**
     * @param array $podcasts
     * @return \Fridge\PodcastBundle\Entity\Podcast[]
     */
    protected function deserializePodcasts(array $podcasts)
    {
        $serializer = $this->getSerializer();
        $logger = $this->getLogger();

        return array_filter(array_map(function ($key) use ($serializer, $podcasts, $logger) {

            $podcastData = json_encode($podcasts[$key]);

            try {
                /** @var \Fridge\PodcastBundle\Entity\Podcast $podcast */
                $podcast = $serializer->deserialize($podcastData, 'Fridge\PodcastBundle\Entity\Podcast', 'json');
                $logger->debug(sprintf('Succesfully deserialized podcast at key "%s"', $key));
            }
            catch (\Exception $e) {
                $logger->error(sprintf('Failed deserialized podcast at key "%s". Error: "%s"', $key, $e->getMessage()));
                return false;
            }

            $podcast->setFirebaseKey($key);

            return $podcast;

        }, array_keys($podcasts)));
    }

    /**
     * @param User $user
     * @param Podcast $podcast
     * @param array $gcmData
     * @return bool
     */
    protected function emitGCM(User $user, Podcast $podcast, array $gcmData)
    {
        $clientIds = array_map(function ($id) {
            return $id->getGcmId();
        }, $user->getGcmIds()->toArray());

        if (count($clientIds) > 0) {

            $this->getLogger()->info(sprintf(
                'For user "%s" with id %d emitting GCMs for clients "%s". Podcast "%s"',
                $user->getUsername(),
                $user->getId(),
                implode($clientIds, ', '),
                $podcast->getName()
            ));

            $message = new Message(array_unique($clientIds), $gcmData);

            $this->getGcmNotification()->execute($message);

            return true;
        }

        return false;
    }

    /**
     * @param Podcast $podcast
     * @return mixed
     */
    protected function serializePodcast(Podcast $podcast)
    {
        return $this->getSerializer()->serialize($podcast, 'json');
    }

    /**
     * @param array $podcast
     * @return mixed
     */
    protected function deserializePodcast(array $podcast)
    {
        return $this->getSerializer()->deserialize($podcast, 'Fridge\PodcastBundle\Entity\Podcast', 'json');
    }

    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return GCMNotification
     */
    protected function getGcmNotification()
    {
        return $this->GCMNotification;
    }

} 