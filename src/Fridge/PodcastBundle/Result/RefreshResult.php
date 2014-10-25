<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 20/10/2014
 * Time: 16:59
 */

namespace Fridge\PodcastBundle\Result;

/**
 * Class RefreshResult
 * @package Fridge\PodcastBundle\Result
 */
final class RefreshResult
{
    /**
     * @var int
     */
    private $new;

    /**
     * @var int
     */
    private $heard;

    /**
     * @param int $new
     * @param int $heard
     */
    public function __construct($new, $heard)
    {
        $this->new = $new;
        $this->heard = $heard;
    }

    /**
     * @return int
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * @return int
     */
    public function getHeard()
    {
        return $this->heard;
    }

} 