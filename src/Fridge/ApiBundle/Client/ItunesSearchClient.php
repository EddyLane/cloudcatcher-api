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

class ItunesSearchClient extends Client
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     * @param $baseUrl
     */
    public function __construct(Logger $logger, $baseUrl)
    {
        $this->logger = $logger;

        parent::__construct([
            'base_url' => $baseUrl,
            'defaults' => [
                'query' => [
                    'media' => 'podcast'
                ]
            ]
        ]);

    }

} 