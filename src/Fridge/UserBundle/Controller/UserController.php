<?php

namespace Fridge\UserBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * @param $username
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function getUserAction($username)
    {
        if($this->getUser()->getUsername() !== $username) {
            throw new HttpException(403);
        }

        return $this->container->get('fos_user.user_manager')->findUserByUsername($username);
    }

}
