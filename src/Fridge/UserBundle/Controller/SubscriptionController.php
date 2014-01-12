<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 04/01/2014
 * Time: 23:01
 */

namespace Fridge\UserBundle\Controller;

class SubscriptionController extends BaseController
{

    /**
     * Query all Subscription entities
     *
     * @return View
     */
    public function getSubscriptionsAction()
    {
        $manager = $this->container->get('fridge.subscription.manager.subscription_manager');

        $subscriptions = $manager->findAll();

        return $subscriptions;
    }

} 