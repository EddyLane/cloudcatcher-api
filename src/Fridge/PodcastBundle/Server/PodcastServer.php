<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 21/10/2014
 * Time: 19:08
 */

namespace Fridge\PodcastBundle\Server;

use Fridge\PodcastBundle\Task\AddPodcast;
use Fridge\PodcastBundle\Task\RefreshPodcasts;
use Fridge\UserBundle\Manager\UserManager;
use Monolog\Logger;
use PhpAmqpLib\Message\AMQPMessage;

class PodcastServer
{

    /**
     * @var \Fridge\PodcastBundle\Task\RefreshPodcasts
     */
    private $refreshPodcasts;

    /**
     * @var \Fridge\PodcastBundle\Task\AddPodcast
     */
    private $addPodcast;

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
     * @param AddPodcast $addPodcast
     * @param Logger $logger
     * @param UserManager $userManager
     */
    public function __construct(
        RefreshPodcasts $refreshPodcasts,
        AddPodcast $addPodcast,
        Logger $logger,
        UserManager $userManager
    )
    {
        $this->refreshPodcasts = $refreshPodcasts;
        $this->addPodcast = $addPodcast;
        $this->logger = $logger;
        $this->userManager = $userManager;
    }

    /**
     * @param AMQPMessage $message
     * @return \Fridge\PodcastBundle\Entity\Podcast|\Fridge\PodcastBundle\Entity\Podcast[]
     * @throws \Exception
     */
    public function execute(AMQPMessage $message)
    {
        $this->logger->debug('Started podcast RPC task');

        $request = unserialize($message->body);

        $user = $this->userManager->find($request['id']);

        switch($request['type']) {

            case 'refresh':
                $result = $this->refreshPodcasts->execute($user);
                break;

            case 'add':
                $result = $this->addPodcast->execute($user, $request['feed'], $request['itunesId']);
                break;

            default:
                throw new \Exception('Unexpected type: ' . $request['type']);

        }

        return $result;

    }

}