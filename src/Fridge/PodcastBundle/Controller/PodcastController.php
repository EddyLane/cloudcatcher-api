<?php

namespace Fridge\PodcastBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;

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
     */
    public function postPodcastAction(ParamFetcher $paramFetcher)
    {
        $client = $this->get('old_sound_rabbit_mq.podcast_get_server_rpc');

        $requestData = serialize([
            'user' => $this->getUser(),
            'feed' => $paramFetcher->get('feed'),
            'itunesId' => $paramFetcher->get('itunesId')
        ]);

        $client->addRequest($requestData, 'podcast_get', rand());

        return $this->view(unserialize($client->getReplies()), 201);
    }

}
