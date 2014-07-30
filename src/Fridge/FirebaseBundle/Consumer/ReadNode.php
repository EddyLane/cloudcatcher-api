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
        $data = $this->client->getClient()->get('/users/' . $msg->body . '/podcasts');
        $this->logger->info(sprintf('Node processing:', $msg->body));

        foreach ($data as $podcast) {

            $this->logger->info(sprintf('Trying to get %s', $podcast['feed']));
            $xmlReader = new \XMLReader();
            $new = 0;
            $heard = 0;

            if ($xmlReader->open($podcast['feed'])) {

                while ($xmlReader->read()) {

                    if ($xmlReader->getAttribute('url')) {
                        $this->logger->info('Episode:' . $xmlReader->getAttribute('url'));
                        if (isset($podcast['heard']) && in_array($xmlReader->getAttribute('url'), $podcast['heard'])) {
                            $heard++;
                        } else {
                            $new++;
                        }
                    }




                }
            }

            $new = 0;
            $heard = 0;
            $this->logger->info(sprintf('For user "%s" we found %d new and %d heard episodes for podcast "%s"', $msg->body, $new, $heard, $podcast['name']));

        }


    }
}