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
use GuzzleHttp\Exception\ParseException;

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
     * @return \Fridge\PodcastBundle\Entity\Podcast[]
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function execute(User $user)
    {
        $this->getLogger()->info(sprintf('Refreshing podcasts for: %s', $user->getUsername()));

        try {

            $podcastFirebase = $this->getFirebase($user);

        } catch (RequestException $e) {

            $this->getLogger()->error(
                sprintf(
                    'Curl failed to connect to firebase for user "%s". Request: "%s". Error: "%s"',
                    $user->getUsernameCanonical(),
                    $e->getRequest(),
                    $e->getMessage()
                )
            );

            throw $e;

        } catch (\Exception $e) {

            $this->getLogger()->error(
                sprintf(
                    'Unknown error getting firebase for user "%s". Error: "%s"',
                    $user->getUsernameCanonical(),
                    $e->getMessage()
                )
            );

            throw $e;
        }

        if (!$podcastFirebase || !is_array($podcastFirebase)) {
            $exception = new UsernameNotFoundException();
            $exception->setUsername($user->getUsernameCanonical());
            $this->getLogger()->error(sprintf('Failed firebase for user "%s"', $user->getUsernameCanonical()));
            throw $exception;
        }

        $this->getLogger()->debug(sprintf('Got firebase for user "%s"', $user->getUsernameCanonical()));


        $podcasts = $this->deserializePodcasts($podcastFirebase);

        $this->getLogger()->debug(sprintf('Successfully converted firebase to entities for user "%s"', $user->getUsernameCanonical()));

        foreach ($podcasts as $podcast) {
            try {
                $this->getRefreshPodcastResult($user, $podcast);
            }
            catch (ParseException $e) {
                continue;
            }
        }

        return array_values($podcasts);

    }

} 