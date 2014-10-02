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
use Doctrine\ORM\EntityManager;

class DeleteAllGcmIdsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fridge:gcm:delete-gcms')
            ->setDescription('Delete all gcms')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var \Fridge\ApiBundle\Repository\GcmIdRepository $repository */
        $repository = $em->getRepository('FridgeApiBundle:GcmId');
        $repository->deleteAll();
    }

} 