<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 16/09/2014
 * Time: 20:46
 */

namespace Fridge\ApiBundle\Notification;

use Fridge\ApiBundle\Message\Message;

/**
 * Class GCMNotification
 * @package Fridge\ApiBundle\Notification
 */
class GCMNotification extends Notification
{
    /**
     * @param Message $message
     */
    public function execute(Message $message)
    {

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->client->getNotificationUrl());

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $this->client->getApiKey(),
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'registration_ids' => $message->getRecipientIds(),
            'data' => $message->getNotifications()
        ]));

        // Execute post
        curl_exec($ch);

        // Close connection
        curl_close($ch);

//        $request = $this->client->post(null, [
//            'body' => [
//                'registration_ids' => $message->getRecipientIds(),
//                'data' => [
//                    'testtting' => 'truesay'
//                ]
//            ]
//        ]);

    }

} 