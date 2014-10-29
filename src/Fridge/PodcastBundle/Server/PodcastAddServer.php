<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 19/10/2014
 * Time: 17:05
 */

namespace Fridge\PodcastBundle\Server;

use Fridge\PodcastBundle\Task\AddPodcast;
use Monolog\Logger;
use PhpAmqpLib\Message\AMQPMessage;

class PodcastAddServer
{
    /**
     * @var \Fridge\PodcastBundle\Task\AddPodcast
     */
    private $addPodcast;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @param AddPodcast $addPodcast
     * @param Logger $logger]
     */
    public function __construct(AddPodcast $addPodcast, Logger $logger)
    {
        $this->addPodcast = $addPodcast;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $message
     * @return string
     */
    public function execute(AMQPMessage $message)
    {
        $this->logger->debug('Started add_podcast RPC task');

        $unserialized = unserialize($message->body);

        $result = $this->addPodcast->execute($unserialized['user'], $unserialized['feed'], $unserialized['itunesId']);

        return serialize($result);
    }

} 