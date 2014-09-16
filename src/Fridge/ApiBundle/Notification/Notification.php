<?php
/**
 * Created by PhpStorm.
 * User: eddylane
 * Date: 16/09/2014
 * Time: 15:51
 */

namespace Fridge\ApiBundle\Notification;

use Monolog\Logger;
use GuzzleHttp\Client;

class Notification extends Client
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $notificationUrl;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param Logger $logger
     * @param $notificationUrl
     * @param $apiKey
     */
    public function __construct(Logger $logger, $notificationUrl, $apiKey)
    {
        $this->logger = $logger;
        $this->notificationUrl = $notificationUrl;
        $this->apiKey = $apiKey;

        parent::__construct([
            'base_url' => $this->notificationUrl,
            'headers' => [
                'Authorization: key=' . $apiKey,
                'Content-Type: application/json'
            ],
            'curl' => [
                CURLOPT_RETURNTRANSFER => true
            ]
        ]);
    }

    public function execute(array $recipients)
    {



    }


} 