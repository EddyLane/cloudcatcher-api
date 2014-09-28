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

    var $url = 'https://android.googleapis.com/gcm/send';
    var $serverApiKey = "AIzaSyCsBbe7FlGVekTvUWKic8-exIaexUhV5bw";
    var $devices = array();

    protected function configure()
    {
        $this
            ->setName('fridge:chrome-push')
            ->setDescription('Push notification')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $client = new \Google_Client();
//        $client->setClientId('74283247353-0iqjuk1j4f0r56n5all44ugs286rg9bf.apps.googleusercontent.com');
//        $client->setClientSecret('CFYCD3FbCD0RIvj7SLLrCq6g');
//        $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');


        /** @var \FOS\UserBundle\Model\UserManager $userManager */
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $bob = $userManager->findUserByUsername('bob');

        $clientIds = array_map(function ($id) {
            return $id->getGcmId();
        }, $bob->getGcmIds()->toArray());

        $this->devices = $clientIds;

        $this->send('TestMessage');

    }



    /*
        Constructor
        @param $apiKeyIn the server API key
    */
    function GCMPushMessage($apiKeyIn){
        $this->serverApiKey = $apiKeyIn;
    }

    /*
        Set the devices to send to
        @param $deviceIds array of device tokens to send to
    */
    function setDevices($deviceIds){

        if(is_array($deviceIds)){
            $this->devices = $deviceIds;
        } else {
            $this->devices = array($deviceIds);
        }

    }

    /*
        Send the message to the device
        @param $message The message to send
        @param $data Array of data to accompany the message
    */
    private function send($message, $data = false){

        $logger = $this->getContainer()->get('logger');

        if(!is_array($this->devices) || count($this->devices) == 0){
            $this->error("No devices set");
        }

        if(strlen($this->serverApiKey) < 8){
            $this->error("Server API Key not set");
        }

        $date = new \DateTime();
        $latest = new \DateTime('Wed, 17 Sep 2014 13:00:01 +0000');
        $fields = array(
            'registration_ids'  => array_unique($this->devices),
            'data'              => [
                'id' => rand(),
                'feed' => 'http://javascriptjabber.com/podcast.rss',
                'slug' => 'javascript-jabber',
                'podcast' => "JavaScript Jabber",
                'timestamp' => $date->format(\DateTime::ISO8601),
                'date' => $latest->format(\DateTime::ISO8601),
                'title' => 'JSJ Google Polymer with Rob Dodson and Eric Bidelman',
                'icon' => "http://a4.mzstatic.com/us/r30/Podcasts/v4/42/f8/13/42f813c0-0de4-0609-e2c5-9954f543eaf9/mza_3131735023717016958.100x100-75.jpg",
                'media' => [
                    'url' => ['http://traffic.libsyn.com/jsjabber/JSJ100.mp3']
                ],
                'download' => true
            ]
        );

        if(is_array($data)){
            foreach ($data as $key => $value) {
                $fields['data'][$key] = $value;
            }
        }

        $headers = array(
            'Authorization: key=' . $this->serverApiKey,
            'Content-Type: application/json'
        );

        $logger->debug('GOT ERE');


        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt( $ch, CURLOPT_URL, $this->url );

        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );

        // Execute post
        $result = curl_exec($ch);

        // Close connection
        curl_close($ch);

        $logger->debug('GOT DEREEEE');
        $logger->debug($result);


        return $result;
    }

    function error($msg){
        echo "Android send notification failed with error:";
        echo "\t" . $msg;
        exit(1);
    }


} 