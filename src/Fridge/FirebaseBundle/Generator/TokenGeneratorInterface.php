<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 06/02/2014
 * Time: 14:15
 */
namespace Fridge\FirebaseBundle\Generator;

use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Class TokenGenerator
 * @package Fridge\FirebaseBundle\Generator
 */
interface TokenGeneratorInterface
{
    /**
     * @param UserInterface $user
     * @return string
     */
    public function generate(UserInterface $user);
}