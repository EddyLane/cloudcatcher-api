<?php

namespace Fridge\PodcastBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use PhpAmqpLib\Exception\AMQPTimeoutException;

/**
 * Class PodcastController
 * @package Fridge\PodcastBundle\Controller
 */
class PodcastController extends FOSRestController
{
    /**
     * Add a podcast to the users firebase via RFC
     *
     * @RequestParam(name="feed")
     * @RequestParam(name="itunesId")
     *
     * @param ParamFetcher $paramFetcher
     * @return \FOS\RestBundle\View\View
     * @throws \Exception
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    public function postPodcastAction(ParamFetcher $paramFetcher)
    {
        /** @var \OldSound\RabbitMqBundle\RabbitMq\RpcClient $client */
        $client = $this->get('old_sound_rabbit_mq.podcast_get_rpc');

        $requestData = serialize([
            'user' => $this->getUser(),
            'feed' => $paramFetcher->get('feed'),
            'itunesId' => $paramFetcher->get('itunesId'),
            'action' => 'post_podcast'
        ]);

        $client->addRequest($requestData, 'podcast_get', rand(), $expiration = 8);

        return $this->view($client->getReplies(), 201);
    }

    /**
     * Refreshes podcasts and updates users firebase
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getPodcastsAction()
    {
        /** @var \OldSound\RabbitMqBundle\RabbitMq\RpcClient $client */
        $client = $this->get('old_sound_rabbit_mq.podcast_get_rpc');

        $requestData = serialize([
            'user' => $this->getUser(),
            'action' => 'podcast_refresh'
        ]);

        $client->addRequest($requestData, 'podcast_refresh', rand(), $expiration = 3);

        return $this->view($client->getReplies(), 201);
    }

}
