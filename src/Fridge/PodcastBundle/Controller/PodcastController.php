<?php

namespace Fridge\PodcastBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Fridge\PodcastBundle\Entity\Podcast;
use Redis;
use GuzzleHttp\Client;

class PodcastController extends FOSRestController
{

    /**
     * @RequestParam(name="feed")
     * @RequestParam(name="itunesId")
     *
     * @param ParamFetcher $paramFetcher
     * @return array
     */
    public function postPodcastAction(ParamFetcher $paramFetcher)
    {
        /** @var \Fridge\FirebaseBundle\Client\FirebaseClient $client */
        $firebaseClient = $this->get('fridge.firebase.client.firebase_client');

        /** @var \Fridge\ApiBundle\Client\GoogleFeedClient $googleFeedApi */
        $googleFeedClient = $this->get('fridge.api.client.google_feed');

        /** @var \Fridge\ApiBundle\Client\ItunesSearchClient $itunesClient */
        $itunesClient = $this->get('fridge.api.client.itunes_search');

        /** @var \JMS\Serializer\Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        /** @var \Redis $redis */
        $redis = $this->container->get('snc_redis.default');

        /** @var \Fridge\UserBundle\Entity\User $user */
        $user = $this->getUser();

        $requests = [];

        if (!$googleFeedData = $redis->get('feed:' . $paramFetcher->get('feed'))) {

            $googleFeedData = $googleFeedClient->get('load', [
                'query' => [
                    'q' => $paramFetcher->get('feed'),
                    'v' => '1.0',
                    'num' => -1,
                    'output' => 'json_xml'
                ]
            ])->getBody();

            $redis->setex('feed:' . $paramFetcher->get('feed'), 3600, $googleFeedData);
        }

        $googleFeedData = json_decode((String) $googleFeedData, true);

        if (!$itunesFeedData = $redis->get('itunes:' .$paramFetcher->get('itunesId'))) {

            $itunesFeedData = $itunesClient->get('lookup', [
                'query' => [
                    'id' => $paramFetcher->get('itunesId'),
                    'kind' => 'podcast'
                ]
            ])->getBody();

            $redis->set('itunes:' .$paramFetcher->get('itunesId'), $itunesFeedData);

        }

        $itunesFeedData = json_decode((String) $itunesFeedData, true);

        $merged = array_merge(
            $googleFeedData['responseData']['feed'],
            $itunesFeedData['results'][0]
        );

        $podcast = Podcast::create($merged);

        $serialized = $serializer->serialize($podcast, 'json');

        $firebaseClient->getClient()->push(
            '/users/' . $user->getUsernameCanonical() . '/podcasts',
            json_decode($serialized), true)
        ;

        return $this->view($podcast, 201);
    }

}
