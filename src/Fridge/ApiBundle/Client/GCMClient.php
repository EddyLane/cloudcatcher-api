<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 16/09/2014
 * Time: 20:10
 */

namespace Fridge\ApiBundle\Client;

use Monolog\Logger;
use GuzzleHttp\Client;

class GCMClient extends Client
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
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getNotificationUrl()
    {
        return $this->notificationUrl;
    }

    /**
     * @param Logger $logger
     * @param $baseUrl
     * @param $apiKey
     */
    public function __construct(Logger $logger, $baseUrl, $apiKey)
    {
        $this->logger = $logger;
        $this->notificationUrl = $baseUrl;
        $this->apiKey = $apiKey;

        $this->logger->info('GCM client created with key: ' . $apiKey);

        parent::__construct([
            'base_url' => $this->notificationUrl,
            'defaults' => [
                'headers' => [
                    [ 'authorization' => 'key=' . $apiKey ],
                    [ 'content-type' => 'application/json' ]
                ]
            ],
            'config' => [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTQUOTE => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: key=' . $apiKey,
                    'Content-Type: application/json'
                ]
            ]
        ]);

        $this->logger->debug(print_r($this->getDefaultOptions(), true));


    }

} 