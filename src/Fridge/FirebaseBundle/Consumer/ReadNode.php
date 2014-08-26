<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 21:22
 */

namespace Fridge\FirebaseBundle\Consumer;

use Fridge\FirebaseBundle\Client\FirebaseClient;
use GuzzleHttp\Client;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class ReadNode implements ConsumerInterface
{
    private $logger;
    private $xmlReader;
    private $client;

    public function __construct(FirebaseClient $client, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->client = $client;
        $this->xmlReader = new \XMLReader();
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $this->client->getClient()->get('/users/' . $msg->body . '/podcasts');
        $this->logger->info(sprintf('Node processing:', $msg->body));
        $guzzleClient = new Client();

        foreach ($data as $i => $podcast) {

            $new = 0;
            $heard = 0;

            $responseJson = $guzzleClient->get(
                'https://ajax.googleapis.com/ajax/services/feed/load',
                [
                    'query' => [
                        'q' => $podcast['feed'],
                        'v' => '1.0',
                        'output' => 'json_xml',
                        'num' => -1
                    ]
                ]
            )->json()['responseData'];

            $xml = simplexml_load_string($responseJson['xmlString']);
            foreach ($xml->xpath('//enclosure') as $episode) {
                if (isset($podcast['heard']) && in_array($episode->attributes()->url, $podcast['heard'])) {
                    $heard++;
                } else {
                    $new++;
                }
            }

            $date = new \DateTime($responseJson['feed']['entries'][0]['publishedDate']);
            $latest = $date->format(\DateTime::ISO8601);

            $this->logger->info(
                sprintf(
                    'For user "%s" we found %d new and %d heard episodes for podcast "%s". Latest episode was "%s"',
                    $msg->body,
                    $new,
                    $heard,
                    $podcast['name'],
                    $latest
                )
            );

            $this->client->getClient()->update(
                sprintf('/users/%s/podcasts/%s', $msg->body, $i),
                [
                    'newEpisodes' => $new,
                    'latest' => $latest
                ]
            );
        }

    }

}