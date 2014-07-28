<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 21:22
 */

namespace Fridge\FirebaseBundle\Consumer;

use Fridge\FirebaseBundle\Client\FirebaseClient;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class ReadNode implements ConsumerInterface
{
    private $logger;

    private $client;

    public function __construct(FirebaseClient $client, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->client = $client;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $this->client->getClient()->get('/users/' . $msg->body);
        $this->logger->info(sprintf('Node processed: "%s". data: %s', $msg->body, json_encode($data)));
    }
}