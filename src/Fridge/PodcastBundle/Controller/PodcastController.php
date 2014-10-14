<?php

namespace Fridge\PodcastBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class PodcastController extends FOSRestController
{

    /**
     * @RequestParam(name="itunesId", requirements="\d+")
     * @RequestParam(name="feed")
     * @RequestParam(name="name")
     * @RequestParam(name="artist")
     * @RequestParam(name="country")
     * @RequestParam(name="slug")
     * @RequestParam(array=true, name="genres", requirements="[a-z]+")
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


        return $this->view($client->getClient()->getOptions(), 201);
    }

}
