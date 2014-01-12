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
use Symfony\Component\HttpKernel\Exception\HttpException;

class CardController extends BaseController
{


    /**
     * @param $username
     * @param $id
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function deleteCardAction($username, $id)
    {
        $cardManager = $this->container->get('fridge.subscription.manager.card_manager');

        $card = $cardManager->find($id);

        if(!$card || !$card->belongsTo($this->getStripeProfile())) {
            throw new HttpException(403, 'Forbidden');
        }

        $cardManager->remove($card, true);
    }

    /**
     * @param $username
     * @return mixed
     */
    public function getCardsAction($username)
    {
        return $this->getUser()->getStripeProfile()->getCards();
    }

    /**
     * @RequestParam(name="token", description="Stripe token.")
     * @View(statusCode=201)
     */
    public function postCardAction($username, ParamFetcher $paramFetcher)
    {
        $cardManager = $this->container->get('fridge.subscription.manager.card_manager');
        $userManager = $this->container->get('fos_user.user_manager');

        $card = $cardManager->create($paramFetcher->get('token'));

        $user = $userManager->findUserByUsername($username);

        if(!$user) {
            throw new \HttpException(404, 'No user');
        }

        $user->getStripeProfile()->addCard($card);

        $userManager->updateUser($user, true);

        return $card;
    }

}