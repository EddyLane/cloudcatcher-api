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
    private function getNewAndHeardResult(\SimpleXMLElement $xml)
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
        $googleFeedEntries = $this->getGoogleFeedData($podcast->getFeed());

        if (!$xml = simplexml_load_string($googleFeedEntries['xmlString'])) {
            $this->getLogger()->warning('Could not parse xmlString for ' . $podcast->getFeed());
            throw new ParseException('Could not parse XML');
        } else {
            $this->getLogger()->debug(sprintf('Parsed xmlString for "%s"', $podcast->getFeed()));
        }

        $entries = $googleFeedEntries['feed']['entries'];
        $latestEpisodeData = $entries[0];
        $latestDateTime = new \DateTime($latestEpisodeData['publishedDate']);
        $latestDateTime = $latestDateTime->format(\DateTime::ISO8601);

        if (!$podcast->getLatest() || ($latestDateTime !== $podcast->getLatest() && isset($xml->xpath('//enclosure')[0]))) {

            $newAndHeard = $this->getNewAndHeardResult($xml);

            $podcast
                ->setLatest($latestDateTime)
                ->setLatestEpisode($latestEpisodeData)
                ->setNewEpisodes($newAndHeard->getNew())
            ;

            $this->getLogger()->info(
                sprintf(
                    'For user "%s" we found %d new and %d heard episodes for podcast "%s". Latest episode was "%s"',
                    $user->getUsernameCanonical(),
                    $newAndHeard->getNew(),
                    $newAndHeard->getHeard(),
                    $podcast->getName(),
                    $podcast->getLatest()
                )
            );

            $this->getFirebaseClient()->updatePodcastLatest($user, $podcast);

            $this->emitGCM($user, $podcast, [
                'id' => rand(),
                'feed' => $podcast->getFeed(),
                'slug' => $podcast->getSlug(),
                'podcast' => $podcast->getName(),
                'content' => isset($latestEpisodeData['summary']) ? $latestEpisodeData['summary'] : '',
                'timestamp' => $podcast->getLatest(),
                'date' => $podcast->getLatest(),
                'title' => $latestEpisodeData['title'],
                'icon' => $podcast->getImageUrl100(),
                'media' => [
                    'url' => $xml->xpath('//enclosure')[0]->attributes()->url,
                ],
                'download' => $podcast->getAutoDownload()
            ]);

        }

    }

} 