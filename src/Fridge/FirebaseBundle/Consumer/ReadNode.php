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
        $xmlReader = new \XMLReader();

        foreach ($data as $i => $podcast) {

            $new = 0;
            $heard = 0;
            $episodeIndex = 0;
            $latest = null;

            if (!$xmlReader->open($podcast['feed'])) {
                $this->logger->error(sprintf('Could not get feed "%s"', $podcast['feed']));
                continue;
            }


            while ($xmlReader->read() && $episodeIndex < 100) {

                if ($xmlReader->localName === 'pubDate' && is_null($latest)) {
                    $xmlReader->read();
                    $latest = $xmlReader->value;
                }

                if ($xmlReader->getAttribute('url')) {

                    $episodeIndex++;

                    if (isset($podcast['heard']) && in_array($xmlReader->getAttribute('url'), $podcast['heard'])) {
                        $heard++;
                    } else {
                        $new++;
                    }
                }
            }

            $this->logger->info(
                sprintf(
                    'For user "%s" we found %d new and %d heard episodes for podcast "%s"',
                    $msg->body,
                    $new,
                    $heard,
                    $podcast['name']
                )
            );

            $this->logger->info(sprintf('Latest episode for podcast "%s" is "%s"', $podcast['name'], $latest));

            $date = new \DateTime($latest);
            $this->client->getClient()->update(
                sprintf('/users/%s/podcasts/%s', $msg->body, $i),
                [
                    'newEpisodes' => $new,
                    'latest' => $date->format(\DateTime::ISO8601)
                ]
            );
        }

    }

}