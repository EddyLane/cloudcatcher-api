<?php

namespace Fridge\UserBundle\Controller;

use Fridge\ApiBundle\Entity\GcmId;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Fridge\SubscriptionBundle\Exception\NoCardsException;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use JWT;

/**
 * Class UserController
 * @package Fridge\UserBundle\Controller
 */
class UserController extends BaseController
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     */
    protected $securityContext;

    /**
     * @param Request $request
     * @param SecurityContextInterface $securityContext
     */
    public function initialize(Request $request, SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @return \Fridge\UserBundle\Entity\User
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function getUsersMeAction()
    {
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new HttpException(403, 'Not authenticated');
        }

        /** @var \Fridge\FirebaseBundle\Generator\TokenGenerator $firebaseGenerator */
        $firebaseGenerator = $this->get('fridge.firebase.generator.token_generator');

        /** @var \Fridge\UserBundle\Entity\User $user */
        $user = $this->getUser();

//        /** @var \Fridge\FirebaseBundle\Task\RefreshPodcast $task */
//        $task = $this->get('fridge.firebase.task.refresh_podcast');
//
//        $task->process($user);

        $user->setFirebaseToken($firebaseGenerator->generate($user));

        return $user;
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
     * @return \FOS\UserBundle\Model\UserInterface
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function getUserAction($username)
    {
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new HttpException(403, 'Not authenticated');
        }

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
     * @RequestParam(name="subscription", description="Name of the subscription you wish to subscribe to")
     *
     * @param $username
     * @param ParamFetcher $paramFetcher
     * @return \FOS\UserBundle\Model\UserInterface
     * @throws \Fridge\SubscriptionBundle\Exception\NoCardsException
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     */
    public function postUserSubscriptionAction($username, ParamFetcher $paramFetcher)
    {
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new HttpException(403, 'Not authenticated');
        }

        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        //if not the user and not admin access denied
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

    /**
     * @param Request $request
     * @return \FOS\UserBundle\Model\UserInterface|null|\Symfony\Component\Form\FormInterface|\Symfony\Component\HttpFoundation\Response
     */
    public function postUserAction(Request $request)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $form = $formFactory->createForm();

        $request->request->set($form->getName(), $request->request->all());
        $request->request->all();

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form->setData($user);

        $form->bind($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                return $user;
            }

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $user;
        }

        return $form;
    }

    /**
     * @param $username
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function getUserPaymentsAction($username)
    {
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new HttpException(403, 'Not authenticated');
        }

        $profile = $this->getUserManager()->findUserByUsername($username)->getStripeProfile();

        return $this->container->get('fridge.subscription.factory.operation_factory')->get('customer.charges.get')->getResult($profile);
    }

    /**
     * @param $username
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function getUserInvoicesAction($username)
    {
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new HttpException(403, 'Not authenticated');
        }

        $profile = $this->getUserManager()->findUserByUsername($username)->getStripeProfile();

        return $this->container->get('fridge.subscription.factory.operation_factory')->get('customer.invoices.get')->getResult($profile);
    }

    /**
     * @RequestParam(name="gcm_id", description="GCM Id.")
     * @param ParamFetcher $paramFetcher
     * @param $username
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function postUserGcmAction(ParamFetcher $paramFetcher, $username)
    {
        $user = $this->getUserManager()->findUserByUsername($username);

        $gcmId = new GcmId();
        $gcmId->setGcmId($paramFetcher->get('gcm_id'));
        $gcmId->setUser($user);
        $user->addGcmId($gcmId);

        $this->getUserManager()->updateUser($user);
        return $user;
    }

}
