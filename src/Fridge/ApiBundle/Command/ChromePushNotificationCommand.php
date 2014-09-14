<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 21/08/2014
 * Time: 22:31
 */

namespace Fridge\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChromePushNotificationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fridge:chrome-push')
            ->setDescription('Push notification')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new \Google_Client();
        $client->setClientId('74283247353-0iqjuk1j4f0r56n5all44ugs286rg9bf.apps.googleusercontent.com');
        $client->setClientSecret('CFYCD3FbCD0RIvj7SLLrCq6g');
        $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
    }


} 