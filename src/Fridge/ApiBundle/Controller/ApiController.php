<?php

namespace Fridge\ApiBundle\Controller;

use FOS\OAuthServerBundle\Controller\TokenController;
use Fridge\FirebaseBundle\Task\RefreshPodcast;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiController
 * @package Fridge\ApiBundle\Controller
 */
class ApiController extends TokenController
{
    /**
     * @var \Fridge\FirebaseBundle\Task\RefreshPodcast
     */
    private $refreshPodcastTask;

    /**
     * @param OAuth2 $server
     * @param RefreshPodcast $refreshPodcastTask
     */
    public function __construct(OAuth2 $server, RefreshPodcast $refreshPodcastTask)
    {
        parent::__construct($server);
        $this->refreshPodcastTask = $refreshPodcastTask;
    }

    /**
     * @inheritdoc
     */
    public function tokenAction(Request $request)
    {
        $response = parent::tokenAction($request);
    }


}