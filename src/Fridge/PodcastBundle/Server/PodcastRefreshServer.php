<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 21/10/2014
 * Time: 19:08
 */

namespace Fridge\PodcastBundle\Server;


use Fridge\PodcastBundle\Task\RefreshPodcasts;
use PhpAmqpLib\Message\AMQPMessage;

class PodcastRefreshServer {
    /**
     * @var \Fridge\PodcastBundle\Task\RefreshPodcasts
     */
    private $addPodcast;

    /**
     * @param RefreshPodcasts $addPodcast
     */
    public function __construct(RefreshPodcasts $addPodcast)
    {
        $this->addPodcast = $addPodcast;
    }

    /**
     * @param $n
     * @return string
     */
    public function call($n)
    {
        $unserialized = unserialize($n);
        $result = $this->addPodcast->execute($unserialized['user']);
        return serialize($result);
    }

    /**
     * @param AMQPMessage $message
     * @return string
     */
    public function execute(AMQPMessage $message)
    {
        $unserialized = unserialize($message->body);
        $result = $this->addPodcast->execute($unserialized['user']);
        return serialize($result);
    }

}