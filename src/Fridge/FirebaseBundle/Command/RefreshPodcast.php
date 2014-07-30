<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 21:20
 */

namespace Fridge\FirebaseBundle\Command;

use FOS\UserBundle\Model\UserManager;
use Fridge\FirebaseBundle\Task\RefreshPodcast as RefreshPodcastTask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshPodcast extends Command
{
    /**
     * @var \Fridge\FirebaseBundle\Task\RefreshPodcast
     */
    protected $task;

    /**
     * @var \FOS\UserBundle\Model\UserManager
     */
    protected $userManager;

    /**
     * @param RefreshPodcastTask $task
     * @param UserManager $userManager
     */
    public function __construct(RefreshPodcastTask $task, UserManager $userManager)
    {
        $this->task = $task;
        $this->userManager = $userManager;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('fridge:refresh-podcast')
            ->setDescription('Refresh the podcasts on firebase for a specific users canonical username')
            ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'username')
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->userManager->findUserByUsername($input->getArgument('username'));

        if (!$user) {
            $output->writeln(sprintf('<error>Cant find user with username canonical "%s"</error>', $input->getArgument('username')));
        } else {
            $output->writeln(sprintf('<info>Adding refresh task for user "%s" to queue</info>', $input->getArgument('username')));
            $this->task->process($user);
        }

    }

} 