<?php
/**
 * Created by PhpStorm.
 * User: eddylane
 * Date: 16/09/2014
 * Time: 15:51
 */

namespace Fridge\ApiBundle\Notification;

use Fridge\ApiBundle\Client\GCMClient;
use Fridge\ApiBundle\Message\Message;
use GuzzleHttp\Client;

abstract class Notification
{
    protected $client;

    public function __construct(GCMClient $client)
    {
        $this->client = $client;
    }

    public abstract function execute(Message $message);
}