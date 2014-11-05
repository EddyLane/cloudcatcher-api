<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 29/10/14
 * Time: 18:55
 */

namespace Fridge\FirebaseBundle\Consumer;


use Fridge\PodcastBundle\Task\RefreshPodcasts as Task;
use Fridge\UserBundle\Manager\UserManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class RefreshPodcasts implements ConsumerInterface
{
    /**
     * @var \Fridge\PodcastBundle\Task\RefreshPodcasts
     */
    private $task;

    /**
     * @var \Fridge\UserBundle\Manager\UserManager
     */
    private $userManager;

    /**
     * @param Task $task
     * @param UserManager $userManager
     */
    public function __construct(Task $task, UserManager $userManager)
    {
        $this->task = $task;
        $this->userManager = $userManager;
    }

    /**
     * @param AMQPMessage $message
     */
    public function execute(AMQPMessage $message)
    {
        $user = $this->userManager->find($message->body);
        $this->task->execute($user);
    }

} 