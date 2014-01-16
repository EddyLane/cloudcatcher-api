<?php

namespace Fridge\UserBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Fridge\SubscriptionBundle\Exception\NoCardsException;
use FOS\RestBundle\Request\ParamFetcher;

class UserController extends BaseController
{
    /**
     * Get currently authenticated user
     *
     * @return mixed
     */
    public function getMeAction()
    {
        return $this->getUser();

    }

    /**
     * Get users if admin
     *
     * @return mixed
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function getUsersAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $this->container->get('fridge.user.manager.user_manager')->findAll();

    }

    /**
     * Get a specific user
     *
     * @param $username
     * @return mixed
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function getUserAction($username)
    {
        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        if (($this->getUser()->getId() !== $user->getId()) && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $user;
    }

    /**
     * Subscribe to a subscription
     *
     * @param $username
     * @param ParamFetcher $paramFetcher
     * @return mixed
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     *
     * @RequestParam(name="subscription", description="Name of the subscription you wish to subscribe to")
     */
    public function postUserSubscriptionAction($username, ParamFetcher $paramFetcher)
    {
        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        if (($this->getUser()->getId() !== $user->getId()) && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $subscription = $this->getSubscriptionManager()->findOneBy(['name' => $paramFetcher->get('subscription')]);

        if (!$subscription) {
            throw new InvalidArgumentException();
        }

        if (!$user->getStripeProfile()->getDefaultCard()) {
            throw new NoCardsException();
        }

        $user->getStripeProfile()->setSubscription($subscription);

        $this->getUserManager()->updateUser($user, true);

        return $user;
    }

}
