<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 30/08/2014
 * Time: 22:50
 */

namespace Fridge\FirebaseBundle\Command;

use FOS\UserBundle\Model\UserManagerInterface;
use Fridge\FirebaseBundle\Task\RefreshPodcast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshAllUsers extends Command
{
    /**
     * @var \FOS\UserBundle\Model\UserManagerInterface
     */
    private $userManager;

    /**
     * @var \Fridge\FirebaseBundle\Task\RefreshPodcast
     */
    private $refreshPodcast;

    /**
     * @param UserManagerInterface $userManager
     * @param RefreshPodcast $refreshPodcast
     */
    public function __construct(UserManagerInterface $userManager, RefreshPodcast $refreshPodcast)
    {
        $this->userManager = $userManager;
        $this->refreshPodcast = $refreshPodcast;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fridge:refresh-all-podcasts')
            ->setDescription('Refresh all podcasts for all users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Fridge\UserBundle\Entity\User[] $users */
        $users = $this->userManager->findUsers();
        foreach($users as $user) {
            $output->writeln(sprintf('<info>Adding refresh task for user "%s" to queue</info>', $user->getUsername()));
            $this->refreshPodcast->process($user);
        }
    }

} 