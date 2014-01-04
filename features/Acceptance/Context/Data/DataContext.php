<?php

namespace Acceptance\Context\Data;

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\ORM\Query;

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
    private function getEntityManager()
    {
        return $this
            ->getKernel()
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ;
    }


    protected function removeAllUsers()
    {
        $em = $this->getEntityManager();
        $em
            ->createQuery('DELETE FridgeUserBundle:User')
            ->execute()
        ;
        $em->flush();
    }

    protected function getUserManager()
    {
        return $this->getKernel()->getContainer()->get('fos_user.user_manager');
    }

    /**
     * @return array
     */
    protected function getAllPayments()
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p.id, p.token, p.completed')
            ->from('UVdPaymentBundle:Payment', 'p')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY)
        ;
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
     * @Given /^the following users exist in the system:$/
     */
    public function theFollowingUsersExistInTheSystem(TableNode $userTable)
    {
        $this->removeAllUsers();

        $userManager = $this->getUserManager();

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
     * @Given /^a stripe id should be set for the user with username "([^"]*)"$/
     */
    public function aStripeIdShouldBeSetForTheUserWithUsername($username)
    {
        $userManager = $this->getUserManager();
        $user = $userManager->findUserBy(['username' => $username]);
        assertNotNull($user->getStripeId());
    }

}