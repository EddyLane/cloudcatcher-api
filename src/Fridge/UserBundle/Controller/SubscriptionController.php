<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 04/01/2014
 * Time: 23:01
 */

namespace Fridge\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class SubscriptionController extends FOSRestController
{

    /**
     * @return array An array/ArrayCollection of subscription entities
     */
    public function getSubscriptionsAction()
    {
        $manager = $this->container->get('fridge.subscription.manager.subscription_manager');

        $subscriptions = $manager->findAll();

        return $subscriptions;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function getSubscriptionAction($id)
    {
        $manager = $this->container->get('fridge.subscription.manager.subscription_manager');

        $subscription = $manager->find($id);

        if (!$subscription) {
            throw new ResourceNotFoundException();
        }

        return $subscription;
    }

} 