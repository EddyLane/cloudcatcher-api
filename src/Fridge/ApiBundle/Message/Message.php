<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 16/09/2014
 * Time: 20:49
 */

namespace Fridge\ApiBundle\Message;

/**
 * Class Message
 * @package Fridge\ApiBundle\Message
 */
class Message
{
    /**
     * @var array
     */
    private $recipientIds;

    /**
     * @var array
     */
    private $notifications;

    /**
     * @param array $recipientIds
     * @param array $notifications
     */
    public function __construct(array $recipientIds, array $notifications)
    {
        $this->recipientIds = $recipientIds;
        $this->notifications = $notifications;
    }

    /**
     * @return array
     */
    public function getRecipientIds()
    {
        return $this->recipientIds;
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

} 