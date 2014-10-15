<?php

namespace Fridge\PodcastBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
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
        $client = $this->get('fridge.firebase.client.firebase_client');

        /** @var \Fridge\UserBundle\Entity\User $user */
        $user = $this->getUser();


        $client->getClient()->push('/users/' . $user->getUsernameCanonical() . '/podcasts', $paramFetcher->all());

        /** @var \Redis $redis */
        $redis = $this->container->get('snc_redis.default');

        $redis->set('something', 'yes');

        return $this->view($client->getClient()->getOptions(), 201);
    }

}
