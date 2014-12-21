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
        return $this->view(
            $this->get('fridge.podcast.task.add_podcast')->execute($this->getUser(), $paramFetcher->get('feed'), $paramFetcher->get('itunesId')),
            201
        );

//        /** @var \OldSound\RabbitMqBundle\RabbitMq\RpcClient $client */
//        $client = $this->get('old_sound_rabbit_mq.podcast_rpc');
//
//        $id = rand();
//
//        $requestData = serialize([
//            'id' => $this->getUser()->getId(),
//            'feed' => $paramFetcher->get('feed'),
//            'itunesId' => $paramFetcher->get('itunesId'),
//            'type' => 'add'
//        ]);
//
//        $client->addRequest($requestData, 'podcast', $id);
//
//        return $this->view($client->getReplies()[$id], 201);
    }

    public function putPreferencesAction(ParamFetcher $paramFetcher)
    {

    }

    /**
     * Refreshes podcasts and updates users firebase
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getPodcastsAction()
    {
        /** @var \OldSound\RabbitMqBundle\RabbitMq\RpcClient $client */
        $client = $this->get('old_sound_rabbit_mq.podcast_rpc');
        $user = $this->getUser();

        $requestData = serialize([
            'type' => 'refresh',
            'id' => $user->getId()
        ]);

        $client->addRequest($requestData, 'podcast', $user->getId());

        return $this->view($client->getReplies()[$user->getId()], 200);
    }

}
