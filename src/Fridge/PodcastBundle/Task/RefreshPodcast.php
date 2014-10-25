<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 20/10/2014
 * Time: 16:39
 */

namespace Fridge\PodcastBundle\Task;


use Fridge\ApiBundle\Message\Message;
use Fridge\PodcastBundle\Entity\Podcast;
use Fridge\PodcastBundle\Result\RefreshResult;
use Fridge\UserBundle\Entity\User;
use GuzzleHttp\Exception\ParseException;
use JMS\Serializer\Exception\XmlErrorException;

class RefreshPodcast extends AbstractTask
{

    /**
     * @param $xml
     * @return RefreshResult
     */
    private function getNewAndHeardResult($xml)
    {
        $new = 0;
        $heard = 0;
        foreach ($xml->xpath('//enclosure') as $episode) {
            if (isset($podcast['heard']) && in_array($episode->attributes()->url, $podcast->getHeard())) {
                $heard++;
            } else {
                $new++;
            }
        }
        return new RefreshResult($new, $heard);
    }


    /**
     * @param User $user
     * @param Podcast $podcast
     * @throws \GuzzleHttp\Exception\ParseException
     */
    public function execute(User $user, Podcast $podcast)
    {
        $googleFeedEntries = $this->getGoogleFeedData($podcast);

        if (!$xml = simplexml_load_string($googleFeedEntries['xmlString'])) {
            $this->getLogger()->warning('Could not parse xmlString for ' . $podcast->getFeed());
            throw new ParseException('Could not parse XML');
        }

        $latestEpisodeData = $googleFeedEntries['feed']['entries'][0];
        $latestDateTime = new \DateTime($latestEpisodeData['publishedDate']);

        if ((!$podcast->getLatest()) || $latestDateTime->format(\DateTime::ISO8601) !== $podcast->getLatest()->format(\DateTime::ISO8601) && isset($xml->xpath('//enclosure')[0])) {

            $newAndHeard = $this->getNewAndHeardResult($xml);

            $podcast->setLatest($latestDateTime);
            $podcast->setLatestEpisode($googleFeedEntries['feed']['entries'][0]);
            $podcast->setNewEpisodes($newAndHeard->getNew());

            $this->getLogger()->info(
                sprintf(
                    'For user "%s" we found %d new and %d heard episodes for podcast "%s". Latest episode was "%s"',
                    $user->getUsernameCanonical(),
                    $newAndHeard->getNew(),
                    $newAndHeard->getHeard(),
                    $podcast->getName(),
                    $podcast->getLatest()->format(\DateTime::ISO8601)
                )
            );

            $this->getFirebaseClient()->updatePodcastLatest($user, $podcast);

            $this->emitGCM($user, $podcast, [
                'id' => rand(),
                'feed' => $podcast->getFeed(),
                'slug' => $podcast->getSlug(),
                'podcast' => $podcast->getName(),
                'content' => $googleFeedEntries['feed']['entries'][0]['summary'],
                'timestamp' => $podcast->getLatest()->format(\DateTime::ISO8601),
                'date' => $podcast->getLatest()->format(\DateTime::ISO8601),
                'title' => $googleFeedEntries['feed']['entries'][0]['title'],
                'icon' => $podcast->getImageUrl100(),
                'media' => [
                    'url' => $xml->xpath('//enclosure')[0]->attributes()->url,
                ],
                'download' => $podcast->getAutoDownload()
            ]);

        }

    }

} 