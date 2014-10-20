<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 19/10/2014
 * Time: 17:05
 */

namespace Fridge\PodcastBundle\Server;

use Fridge\PodcastBundle\Task\AddPodcast;
use PhpAmqpLib\Message\AMQPMessage;

class PodcastAddServer
{
    /**
     * @var \Fridge\PodcastBundle\Task\AddPodcast
     */
    private $addPodcast;

    /**
     * @param AddPodcast $addPodcast
     */
    public function __construct(AddPodcast $addPodcast)
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
        $result = $this->addPodcast->execute($unserialized['user'], $unserialized['feed'], $unserialized['itunesId']);
        return serialize($result);
    }

    /**
     * @param AMQPMessage $message
     * @return string
     */
    public function execute(AMQPMessage $message)
    {
        $unserialized = unserialize($message->body);
        $result = $this->addPodcast->execute($unserialized['user'], $unserialized['feed'], $unserialized['itunesId']);
        return serialize($result);
    }

} 