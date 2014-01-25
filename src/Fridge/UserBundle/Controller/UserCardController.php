<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 04/01/2014
 * Time: 23:01
 */

namespace Fridge\UserBundle\Controller;

use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use Fridge\SubscriptionBundle\Exception\InvalidTokenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UserCardController
 * @package Fridge\UserBundle\Controller
 */
class UserCardController extends BaseController
{
    /**
     * Delete card
     *
     * @param $username
     * @param $id
     * @return bool
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function deleteCardAction($username, $id)
    {
        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        $cardManager = $this->getCardManager();
        $card  = $cardManager->find($id);

        if (!$card || !$card->belongsTo($user->getStripeProfile())) {
            throw new ResourceNotFoundException();
        }

        if (($this->getUser()->getId() !== $user->getId()) && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $this->getCardManager()->remove($card, true);

        return $card;
    }

    /**
     * Read cards
     *
     * @param $username
     * @return mixed
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function getCardsAction($username)
    {
        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        if (($this->getUser()->getId() !== $user->getId()) && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $user->getStripeProfile()->getCards();
    }

    /**
     * Read card
     *
     * @param $username
     * @param $id
     * @return \Fridge\SubscriptionBundle\Entity\Card
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function getCardAction($username, $id)
    {
        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        $card  = $this->getCardManager()->find($id);

        if (!$card || !$card->belongsTo($user->getStripeProfile())) {
            throw new ResourceNotFoundException();
        }

        if (($this->getUser()->getId() !== $user->getId()) && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $card;
    }

    /**
     * @RequestParam(name="default", description="Make this card the default card")
     *
     * @param $username
     * @param $id
     * @return mixed
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function putCardAction($username, $id)
    {
        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        $card  = $this->getCardManager()->find($id);

        if (!$card || !$card->belongsTo($user->getStripeProfile())) {
            throw new ResourceNotFoundException();
        }

        if (($this->getUser()->getId() !== $user->getId()) && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $user->getStripeProfile()->setDefaultCard($card);

        $this->getUserManager()->updateUser($user, true);

        return $card;
    }

    /**
     * @return \Fridge\SubscriptionBundle\Manager\CardManager
     */
    protected function getCardManager()
    {
        return $this->container->get('fridge.subscription.manager.card_manager');
    }

    /**
     * Create card
     *
     * @RequestParam(name="token", description="Stripe token.")
     * @View(statusCode=201)
     *
     * @param $username
     * @param ParamFetcher $paramFetcher
     * @return \Fridge\SubscriptionBundle\Entity\Card
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @throws \Fridge\SubscriptionBundle\Exception\InvalidTokenException
     */
    public function postCardAction($username, ParamFetcher $paramFetcher)
    {
        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        if (($this->getUser()->getId() !== $user->getId()) && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        if ($this->getCardManager()->findOneBy(['token' => $paramFetcher->get('token')])) {
            throw new InvalidTokenException('Token already used');
        }

        $card = $this->getCardManager()->create($paramFetcher->get('token'));

        $user
            ->getStripeProfile()
            ->addCard($card)
        ;

        $this->getUserManager()->updateUser($user, true);

        return $card;
    }

}