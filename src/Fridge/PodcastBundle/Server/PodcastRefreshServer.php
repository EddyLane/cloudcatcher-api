<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 21/10/2014
 * Time: 19:08
 */

namespace Fridge\PodcastBundle\Server;

use Fridge\PodcastBundle\Task\RefreshPodcasts;
use Fridge\UserBundle\Manager\UserManager;
use Monolog\Logger;
use PhpAmqpLib\Message\AMQPMessage;

class PodcastRefreshServer
{

    /**
     * @var \Fridge\PodcastBundle\Task\RefreshPodcasts
     */
    private $refreshPodcasts;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Fridge\UserBundle\Manager\UserManager
     */
    private $userManager;

    /**
     * @param RefreshPodcasts $refreshPodcasts
     * @param Logger $logger
     * @param UserManager $userManager
     */
    public function __construct(RefreshPodcasts $refreshPodcasts, Logger $logger, UserManager $userManager)
    {
        $this->refreshPodcasts = $refreshPodcasts;
        $this->logger = $logger;
        $this->userManager = $userManager;
    }

    /**
     * @param AMQPMessage $message
     * @return string
     */
    public function execute(AMQPMessage $message)
    {
        $this->logger->debug('Started refresh_podcast RPC task');
        $result = $this->refreshPodcasts->execute($this->userManager->find($message->body));
        return $result;
    }

}