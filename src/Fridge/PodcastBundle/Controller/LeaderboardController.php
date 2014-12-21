<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 16/11/14
 * Time: 17:22
 */

namespace Fridge\PodcastBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LeaderboardController extends Controller
{

    public function getLeaderboardAction()
    {
        $redis = $this->get('snc_redis.default');

        return array_map(function ($e) {
            return json_decode($e, true);
        }, $redis->zRevRange('top', 0, 9));

    }

} 