<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 20/10/2014
 * Time: 14:35
 */

namespace Fridge\PodcastBundle\Task;


use Fridge\PodcastBundle\Entity\Podcast;
use Fridge\UserBundle\Entity\User;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class RefreshPodcasts
 * @package Fridge\PodcastBundle\Task
 */
class RefreshPodcasts extends AbstractTask
{
    /**
     * @var RefreshPodcast
     */
    private $refreshPodcast;

    /**
     * @param RefreshPodcast $refreshPodcast
     */
    public function setRefreshPodcastTask(RefreshPodcast $refreshPodcast)
    {
        $this->refreshPodcast = $refreshPodcast;
    }

    /**
     * @param User $user
     * @param Podcast $podcast
     */
    private function getRefreshPodcastResult(User $user, Podcast $podcast)
    {
        $this->refreshPodcast->execute($user, $podcast);
    }

    /**
     * @param User $user
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function execute(User $user)
    {

        $this->getLogger()->info(sprintf('Refreshing podcasts for: %s', $user->getUsername()));

        try {

            $podcastFirebase = $this
                ->getFirebaseClient()
                ->getClient()
                ->get(sprintf('/users/%s/podcasts', $user->getUsernameCanonical()))
            ;

        } catch (RequestException $e) {

            $this->getLogger()->error(
                sprintf(
                    'Curl failed to connect to firebase for user "%s". Request: "%s"',
                    $user->getUsernameCanonical(),
                    $e->getRequest()
                )
            );

            throw $e;
        }

        if (!$podcastFirebase || !is_array($podcastFirebase)) {
            $exception = new UsernameNotFoundException();
            $exception->setUsername($user->getUsernameCanonical());
            throw $exception;
        }

        $podcasts = $this->deserializePodcasts($podcastFirebase);

        foreach ($podcasts as $podcast) {
            $this->getRefreshPodcastResult($user, $podcast);
        }

        return $podcasts;

    }

} 