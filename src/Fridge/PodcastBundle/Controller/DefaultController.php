<?php

namespace Fridge\PodcastBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('FridgePodcastBundle:Default:index.html.twig', array('name' => $name));
    }
}
