<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 22:10
 */

namespace Fridge\FirebaseBundle\Client;

use Firebase\Firebase;
use GuzzleHttp\Client;
use Monolog\Logger;

class FirebaseClient
{
    protected $client;

    public function __construct($baseUrl, $secret, Logger $logger)
    {
        $logger->debug(sprintf('Connecting to firebase base_url "%s" secret "%s"', $baseUrl, $secret));

        $this->client = new Firebase([
            'base_url' => $baseUrl,
            'token' => $secret
        ], new Client());
    }

    public function getClient()
    {
        return $this->client;
    }

} 