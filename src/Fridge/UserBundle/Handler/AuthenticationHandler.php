<?php

namespace Fridge\UserBundle\Handler;

use Fridge\FirebaseBundle\Generator\TokenGeneratorInterface;
use Fridge\FirebaseBundle\Task\RefreshPodcast;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use JWT;

/**
 * Class AuthenticationHandler
 * @package Fridge\UserBundle\Handler
 */
class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var \FOS\RestBundle\View\ViewHandler
     */
    protected $viewHandler;

    /**
     * @var \Fridge\FirebaseBundle\Generator\TokenGeneratorInterface
     */
    protected $tokenGenerator;

    /**
     * @var \Fridge\FirebaseBundle\Task\RefreshPodcast
     */
    protected $task;

    /**
     * @param ViewHandler $viewHandler
     * @param TokenGeneratorInterface $tokenGenerator
     * @param RefreshPodcast $task
     */
    public function __construct(ViewHandler $viewHandler, TokenGeneratorInterface $tokenGenerator, RefreshPodcast $task)
    {
        $this->viewHandler = $viewHandler;
        $this->tokenGenerator = $tokenGenerator;
        $this->task = $task;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        $user->setFirebaseToken($this->tokenGenerator->generate($user));
        $view = View::create($user);
        $view->setStatusCode(200);
        $this->task->process($user);
        return $this->viewHandler->handle($view);
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $view = View::create($exception->getMessage());
        $view->setStatusCode(403);
        return $this->viewHandler->handle($view);
    }

}