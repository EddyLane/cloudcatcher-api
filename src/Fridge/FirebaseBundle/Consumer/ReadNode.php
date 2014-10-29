<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 21:22
 */

namespace Fridge\FirebaseBundle\Consumer;

use Fridge\ApiBundle\Message\Message;
use Fridge\ApiBundle\Notification\GCMNotification;
use Fridge\FirebaseBundle\Client\FirebaseClient;
use Fridge\UserBundle\Manager\UserManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class ReadNode implements ConsumerInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \XMLReader
     */
    private $xmlReader;
    /**
     * @var \Fridge\FirebaseBundle\Client\FirebaseClient
     */
    private $client;
    /**
     * @var \Fridge\UserBundle\Manager\UserManager
     */
    private $userManager;
    /**
     * @var \Fridge\ApiBundle\Notification\GCMNotification
     */
    private $GCMNotification;
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var string
     */
    public static $googleFeedApi = 'https://ajax.googleapis.com/ajax/services/feed/load';

    public function __construct(
        FirebaseClient $client,
        LoggerInterface $logger,
        UserManager $userManager,
        GCMNotification $GCMNotification,
        \Predis\Client $redis
    )
    {
        $this->logger = $logger;
        $this->client = $client;
        $this->xmlReader = new \XMLReader();
        $this->userManager = $userManager;
        $this->GCMNotification = $GCMNotification;
        $this->redis = $redis;
    }

    public function execute(AMQPMessage $msg)
    {
        try {

            $this->logger->info(sprintf('Node processing: %s', $msg->body));

            try {
                $data = $this->client->getClient()->get('/users/' . $msg->body . '/podcasts');
            } catch (RequestException $e) {
                $this->logger->error(sprintf('Curl failed to connect to firebase for user "%s". Request: "%s"', $msg->body, $e->getRequest()));
                throw $e;
            }

            $guzzleClient = new Client();

            if (!$data || !is_array($data)) {
                return true;
            }

            foreach ($data as $i => $podcast) {

                $new = 0;
                $heard = 0;


                if (!$googleFeedData = $this->redis->get('feed:' . $podcast['feed'])) {

                    $options = [
                        'query' => [
                            'q' => $podcast['feed'],
                            'v' => '1.0',
                            'output' => 'json_xml',
                            'num' => -1
                        ]
                    ];

                    try {
                        $response = $guzzleClient->get(self::$googleFeedApi, $options);
                        $responseJson = $response->json()['responseData'];
                    } catch (RequestException $e) {
                        $this->logger->error(sprintf('Curl failed to url "%s" with feed "%s". Request: "%s"', self::$googleFeedApi, $podcast['feed'], $e->getRequest()));
                        throw $e;
                    }

                    $this->redis->setex('feed:' . $podcast['feed'], 3600, (String) $response->getBody());
                } else {

                    $responseJson = json_decode($googleFeedData, true)['responseData'];
                    
                }


                if (!$xml = simplexml_load_string($responseJson['xmlString'])) {
                    $this->logger->warning('Could not parse xmlString for ' . $podcast['feed']);
                    continue;
                }

                foreach ($xml->xpath('//enclosure') as $episode) {
                    if (isset($podcast['heard']) && in_array($episode->attributes()->url, $podcast['heard'])) {
                        $heard++;
                    } else {
                        $new++;
                    }
                }

                $date = new \DateTime($responseJson['feed']['entries'][0]['publishedDate']);
                $latest = $date->format(\DateTime::ISO8601);

                if ((!isset($podcast['latest']) || strcmp($latest, $podcast['latest']) !== 0) && isset($xml->xpath('//enclosure')[0])) {

                    $user = $this->userManager->findOneBy(['username' => $msg->body]);

                    $clientIds = array_map(function ($id) {
                        return $id->getGcmId();
                    }, $user->getGcmIds()->toArray());

                    if (count($clientIds) > 0) {

                        $this->logger->info(sprintf(
                            'For user "%s" with id %d emitting GCMs for clients "%s". Podcast "%s"',
                            $user->getUsername(),
                            $user->getId(),
                            implode($clientIds, ', '),
                            $podcast['name']
                        ));

                        $message = new Message(array_unique($clientIds), [
                            'id' => rand(),
                            'feed' => $podcast['feed'],
                            'slug' => $podcast['slug'],
                            'podcast' => $podcast['name'],
                            'content' => isset($responseJson['feed']['entries'][0]['summary']) ? $responseJson['feed']['entries'][0]['summary'] : '',
                            'timestamp' => $latest,
                            'date' => $latest,
                            'title' => $responseJson['feed']['entries'][0]['title'],
                            'icon' => $podcast['artwork']['100'],
                            'media' => [
                                'url' => $xml->xpath('//enclosure')[0]->attributes()->url,
                            ],
                            'download' => isset($podcast['autoDownload']) && $podcast['autoDownload']  === 1
                        ]);

                        $this->GCMNotification->execute($message);

                    }

                }

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
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Consumption failed: Exception "%s", Code: "%s", Message: "%s"', get_class($e), $e->getCode(), $e->getMessage()));
            //return false;
        }


    }

}