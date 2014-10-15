<?php

namespace Acceptance\Context\Data;

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\ORM\Query;
use ZfrStripe\Exception\NotFoundException;
use Behat\Gherkin\Node\PyStringNode;

require __DIR__. '/../../../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Data context.
 */
class DataContext extends BehatContext implements KernelAwareInterface
{
    protected $loader;
    protected $executor;
    protected $parameters;
    protected $kernel;


    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Gets the kernel
     *
     * @return KernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }
    /**
     * Gets an entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this
            ->getKernel()
            ->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }


    protected function removeAll($entity)
    {
        $this->getKernel()->getContainer()->set('doctrine', null);

        $em = $this->getEntityManager();
        $em
            ->createQuery('DELETE ' . $entity)
            ->execute()
        ;
        $em->flush();
    }

    public function getUserManager()
    {
        return $this->getKernel()->getContainer()->get('fos_user.user_manager');
    }

    public function getCardManager()
    {
        return $this->getKernel()->getContainer()->get('fridge.subscription.manager.card_manager');
    }

    public function getSubscriptionManager()
    {
        return $this->getKernel()->getContainer()->get('fridge.subscription.manager.subscription_manager');
    }

    /**
     * @Given /^no payments should exist in the system$/
     */
    public function noPaymentsShouldExistInTheSystem()
    {
        assertEmpty($this->getAllPayments());
    }

    /**
     * @Given /^only the following payments should now exist in the system:$/
     */
    public function onlyTheFollowingPaymentsShouldNowExistInTheSystem(TableNode $expectedTable)
    {
        $actual = $this->getAllPayments();

        $expected = [];
        foreach($expectedTable->getHash() as $paymentHash) {
            $expected[] = [
                'id' => (int) $paymentHash['id'],
                'token' => $this->getMainContext()->getSubcontext('webcontext')->stripeToken,
                'completed' => $paymentHash['completed'] === 'true' ? true : false
            ];
        }

        assertEquals($expected, $actual);
    }

    /**
     * @Given /^no users exist in the system$/
     * @Given /^No users exist in the system$/
     */
    public function noUsersExistInTheSystem()
    {
        $em = $this->getEntityManager();

        foreach($em->getRepository('FridgeUserBundle:User')->findAll() as $user) {
            try {
                $em->remove($user);
                $em->flush();
            }
            catch(\Exception $e) {}
        }
    }

    /**
     * @Given /^the following users exist in the system:$/
     */
    public function theFollowingUsersExistInTheSystem(TableNode $userTable)
    {
        $userManager = $this->getUserManager();
        $em = $this->getEntityManager();


//        foreach($em->getRepository('FridgeApiBundle:GcmId')->findAll() as $token) {
//                $em->remove($token);
//                $em->flush();
//        }
//
//        foreach($em->getRepository('FridgeApiBundle:AccessToken')->findAll() as $token) {
//            try {
//                $em->remove($token);
//                $em->flush();
//            }
//            catch(\Exception $e) {}
//        }
//
//        foreach($em->getRepository('FridgeUserBundle:User')->findAll() as $user) {
//            try {
//                $em->remove($user);
//                $em->flush();
//            }
//            catch(\Exception $e) {}
//        }
//
//        foreach($em->getRepository('FridgeSubscriptionBundle:StripeProfile')->findAll() as $user) {
//            try {
//                $em->remove($user);
//                $em->flush();
//            }
//            catch(\Exception $e) {}
//        }
//        foreach($em->getRepository('FridgeSubscriptionBundle:Card')->findAll() as $user) {
//            try {
//                $em->remove($user);
//                $em->flush();
//            }
//            catch(\Exception $e) {}
//        }

        $this->removeAll('FridgeApiBundle:GcmId');
        $this->removeAll('FridgeApiBundle:RefreshToken');
        $this->removeAll('FridgeApiBundle:AccessToken');
        $this->removeAll('FridgeUserBundle:User');
        $this->removeAll('FridgeUserBundle:User');
        $this->removeAll('FridgeSubscriptionBundle:StripeProfile');
        $this->removeAll('FridgeSubscriptionBundle:Card');

        $em->getConnection()->exec("ALTER TABLE fridge_user_user AUTO_INCREMENT = 1; ");
        $em->getConnection()->exec("ALTER TABLE fridge_subscription_stripe_profile AUTO_INCREMENT = 1; ");
        $em->getConnection()->exec("ALTER TABLE fridge_subscription_card AUTO_INCREMENT = 1; ");
        $em->flush();

        foreach($userTable->getHash() as $userHash) {
            $user = $userManager->createUser();
            $user->setPlainPassword($userHash['password']);
            $user->setUsername($userHash['username']);
            $user->setEmail($userHash['email']);
            if(isset($userHash['stripe_id'])) {
                $user->setStripeId($userHash['stripe_id']);
            }
            $user->setEnabled(true);
            $userManager->updatePassword($user);
            $userManager->updateUser($user, true);
        }
    }

    /**
     * @Given /^the user "([^"]*)" is an admin$/
     */
    public function theUserIsAnAdmin($username)
    {
        $userManager = $this->getUserManager();
        $user = $userManager->findUserByUsername($username);
        $user->addRole('ROLE_ADMIN');
        $userManager->updateUser($user, true);
    }

    /**
     * @Given /^a stripe id should be set for the user with username "([^"]*)"$/
     */
    public function aStripeIdShouldBeSetForTheUserWithUsername($username)
    {
        $userManager = $this->getUserManager();
        $user = $userManager->findUserBy(['username' => $username]);
        assertNotNull($user->getStripeId());
    }


    /**
     * @Given /^no cards should exist in the system$/
     */
    public function noCardsShouldExistInTheSystem()
    {
        $cards = $this->getEntityManager()->getRepository('FridgeSubscriptionBundle:Card')->findAll();
        assertEmpty($cards);
    }

    /**
     * @Given /^the following cards exist for user "([^"]*)":$/
     */
    public function theFollowingCardsExistForUserBob($username, TableNode $table)
    {
        $user = $this->getUserManager()->findUserByUsername($username);
        $stripeProfile = $user->getStripeProfile();
        $cardManager = $this->getCardManager();

        foreach($table->getHash() as $cardData) {

            $card = $cardManager->create();

            $card
                ->setNumber($cardData['number'])
                ->setExpMonth($cardData['expMonth'])
                ->setExpYear($cardData['expYear'])
                ->setCardType($cardData['cardType'])
                ->setToken($cardData['stripeId'])
            ;

            $stripeProfile->addCard($card);
        }

        $this->getUserManager()->updateUser($user, true);
    }

    /**
     * @Given /^the following cards should exist for user "([^"]*)":$/
     */
    public function theFollowingCardsShouldExistForUserBob($username, TableNode $table)
    {
        $profile = $this->getUserManager()->findUserByUsername($username)->getStripeProfile();

        $this->getEntityManager()->refresh($profile);

        $cards = $profile->getCards();

        assertEquals($table->getHash(), array_map(function($card) {
            return [
                'id' => $card->getId(),
                'cardType' => $card->getCardType(),
                'expMonth' => $card->getExpMonth(),
                'expYear' => $card->getExpYear(),
                'number' => $card->getNumber()
            ];
        }, $cards->toArray()));
    }

    /**
     * @Given /^exactly (\d+) stripe profile should have been created for user "([^"]*)"$/
     */
    public function exactlyStripeProfileShouldHaveBeenCreatedForUser($profileAmount, $username)
    {
        $user = $this->getUserManager()->findUserByUsername($username);
        assertNotNull($user->getStripeProfile()->getStripeId());
        $profiles = $this->getEntityManager()->getRepository('FridgeSubscriptionBundle:StripeProfile')->findAll();
        assertEquals($profileAmount, count($profiles));
    }

    /**
     * @Given /^the following subscriptions exist:$/
     */
    public function theFollowingSubscriptionsExist(TableNode $table)
    {
        $subscriptionManager  = $this->getSubscriptionManager();


        foreach($subscriptionManager->findAll() as $subscription) {

            try {
                $subscriptionManager->remove($subscription, true);
            }
            catch(\Exception $e) {}
        }

        $this->removeAll('FridgeSubscriptionBundle:StripeProfile');
        $this->removeAll('FridgeSubscriptionBundle:Subscription');

        $em = $this->getEntityManager();
        $em->getConnection()->exec("ALTER TABLE fridge_subscription AUTO_INCREMENT = 1; ");
        $em->flush();

        foreach($table->getHash() as $subscriptionData) {
            $subscription = $subscriptionManager->create();
            $subscription
                ->setName($subscriptionData['name'])
                ->setDescription($subscriptionData['description'])
                ->setPrice($subscriptionData['price'])
            ;
            $subscriptionManager->save($subscription, true);
        }
    }

    /**
     * @Given /^the default client exists$/
     */
    public function theDefaultClientExists()
    {
        $clientManager = $this->getKernel()->getContainer()->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        $client->setRedirectUris(['http://app.angular-symfony-stripe.local:8080/app_dev.php/']);
        $client->setAllowedGrantTypes([
            'password',
            'refresh_token',
            'authorization_code',
            'token',
            'client_credentials'
        ]);
        $clientManager->updateClient($client);
        $this->getMainContext()->client = $client;
    }

    /**
     * @Given /^redis should have the following data stored under "([^"]*)":$/
     */
    public function redisShouldHaveTheFollowingDataStoredUnderSomething($key, PyStringNode $string)
    {
        $redis = $this->getKernel()->getContainer()->get('snc_redis.default');

        assertEquals($string->getRaw(), $redis->get($key));


    }

}

