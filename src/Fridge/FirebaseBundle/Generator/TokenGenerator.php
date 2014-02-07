<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 06/02/2014
 * Time: 14:00
 */

namespace Fridge\FirebaseBundle\Generator;

use Symfony\Component\Security\Core\User\UserInterface;
use JWT;

/**
 * Class TokenGenerator
 * @package Fridge\FirebaseBundle\Generator
 */
class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @var string
     */
    protected $secret;
    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param string $secret
     * @param bool $debug
     */
    public function __construct($secret, $debug = false)
    {
        $this->secret = $secret;
        $this->debug = $debug;
    }

    /**
     * @param UserInterface $user
     * @return string
     */
    public function generate(UserInterface $user)
    {
        return JWT::encode([
            "iat" => time(),
            'debug' => $this->debug,
            'v' => 0,
            'd' => [ 'username' => $user->getUsernameCanonical() ]

        ], $this->secret);
    }

} 