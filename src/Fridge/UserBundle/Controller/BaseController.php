<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 06/01/2014
 * Time: 22:06
 */

namespace Fridge\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BaseController extends FOSRestController {

    /**
     * @param Request $request
     * @param SecurityContextInterface $securityContext
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function initialize(Request $request, SecurityContextInterface $securityContext)
    {
        if(!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new HttpException(403, 'Not authenticated');
        }
    }

    /**
     * @return mixed
     */
    protected function getStripeProfile()
    {
        return $this->getUser()->getStripeProfile();
    }

    /**
     * @return object
     */
    protected function getUserManager()
    {
        return $this->container->get('fos_user.user_manager');
    }

}