<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 28/07/2014
 * Time: 21:20
 */

namespace Fridge\FirebaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshPodcastCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('fridge:refresh-podcast');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('fridge.firebase.task.refresh_podcast')->process();
    }

} 