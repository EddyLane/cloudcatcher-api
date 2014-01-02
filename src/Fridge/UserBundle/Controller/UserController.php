<?php

namespace Fridge\UserBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\FOSRestController;

class UserController extends FOSRestController
{
    /**
     * Get currently authenticated user
     *
     * @return mixed
     */
    public function getMeAction()
    {
        $view = View::create($this->getUser());
        $view->setFormat('json');
        return $this->handleView($view);
    }

}
