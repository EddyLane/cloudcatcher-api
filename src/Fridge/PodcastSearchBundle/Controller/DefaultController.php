<?php

namespace Fridge\PodcastSearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('FridgePodcastSearchBundle:Default:index.html.twig', array('name' => $name));
    }
}
