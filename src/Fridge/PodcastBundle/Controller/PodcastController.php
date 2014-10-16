<?php

namespace Fridge\PodcastBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Fridge\PodcastBundle\Entity\Podcast;
use Redis;

class PodcastController extends FOSRestController
{

    /**
     * @RequestParam(name="feed")
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

        /** @var \JMS\Serializer\Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        /** @var \Fridge\UserBundle\Entity\User $user */
        $user = $this->getUser();

        $response = $googleFeedClient->get('load', [
            'query' => [
                'q' => $paramFetcher->get('feed'),
                'v' => '1.0',
                'num' => -1,
                'output' => 'json_xml'
            ]
        ]);

        $podcastData = json_decode((String) $response->getBody(), true);

        /** @var \Redis $redis */
        $redis = $this->container->get('snc_redis.default');

        $redis->set($paramFetcher->get('feed'), (String) $response->getBody());
        $redis->expire($paramFetcher->get('feed'), 3600);

        $podcast = Podcast::factory($podcastData);

        $firebaseClient->getClient()->push(
            '/users/' . $user->getUsernameCanonical() . '/podcasts',
            json_decode($serializer->serialize($podcast, 'json')), true)
        ;

        return $this->view($podcast, 201);
    }

}
