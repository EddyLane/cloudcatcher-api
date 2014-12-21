<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 11/12/2013
 * Time: 21:51
 */

namespace Acceptance\Context\Web;


use Behat\Behat\Event\FeatureEvent;
use Behat\CommonContexts\WebApiContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Tests\Server;
use GuzzleHttp\Tests\Ring\Client\Server as RingServer;

class WebContext extends WebApiContext
{
    private $parameters;
    public $stripeToken;
    protected $stripePk;
    protected $stripeSk;
    protected $stripeClient;
    private $baseUrl;
    private $accessToken;

    public function __construct($parameters)
    {
        $this->stripePk = $parameters['stripe_pk'];
        $this->stripeSk = $parameters['stripe_sk'];
        $this->parameters = $parameters;
        $this->baseUrl = $parameters['base_url'];
        $this->stripeClient = new \ZfrStripe\Client\StripeClient($this->stripeSk);

        parent::__construct($parameters['base_url']);
    }

    /**
     * Sends HTTP request to specific relative URL.
     *
     * @param string $method request method
     * @param string $url    relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with access token$/
     */
    public function iSendARequest($method, $url)
    {
        $url = $this->baseUrl.ltrim($this->replacePlaceHolder($url), '/') . '?access_token=' . $this->accessToken;

        parent::getBrowser()->call($url, $method, $this->getHeaders());
    }

    /**
     * Adds Basic Authentication header to next request.
     *
     * @param string $username
     * @param string $password
     *
     * @Given /^I am authenticating as "([^"]*)" with "([^"]*)" password via oauth2$/
     */
    public function iAmAuthenticatingAs($username, $password)
    {
        $client = $this->getMainContext()->client;
        parent::iSendARequest('get', '/oauth/v2/token?username=' . $username . '&password=' . $password . '&grant_type=password&client_secret=' . $client->getSecret() . '&client_id=' . $client->getPublicId());
        $this->accessToken = json_decode(parent::getBrowser()->getLastResponse()->getContent(), true)['access_token'];
    }

    /**
     * @Given /^I generate a stripe token from the following card details:$/
     */
    public function iGenerateAStripeTokenFromTheFollowingCardDetails(TableNode $paymentDetailsTable)
    {
        $paymentDetailsHash = $paymentDetailsTable->getHash()[0];
        $stripeCurlParams = [
            'card[number]' => $paymentDetailsHash['number'],
            'card[exp_month]' => $paymentDetailsHash['exp_month'],
            'card[exp_year]' => $paymentDetailsHash['exp_year'],
            'card[cvc]' => $paymentDetailsHash['cvc'],
            'key' => $this->stripePk,
            '_method' => 'POST'
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.stripe.com/v1/tokens?' . http_build_query($stripeCurlParams),
            CURLOPT_USERAGENT => 'Stripe CURL'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $stripeResponse = json_decode($resp, true);

        if (isset($stripeResponse['id'])) {
            $this->stripeToken = $stripeResponse['id'];
        } else {
            throw new \Exception(json_encode($stripeResponse));
        }
    }

    /**
     * @Given /^the card with id (\d+) should have been persisted to stripe for user "([^"]*)"$/
     */
    public function theCardWithIdShouldHaveBeen($id, $username)
    {
        $dataContext =  $this->getMainContext()->getSubcontext('datacontext');
        $user = $dataContext->getUserManager()->findUserByUsername($username);
        $stripeProfile = $user->getStripeProfile();

        $dataContext->getEntityManager()->refresh($stripeProfile);

        $cards = $stripeProfile->getCards();

        $card = current(array_filter($cards->toArray(), function($card) use ($id) {
            return $card->getId() === (int) $id;
        }));

        $stripeResponse = $this->stripeClient->getCard([
            'id' => $card->getToken(),
            'customer' => $card->getStripeProfile()->getStripeId()
        ]);

        assertEquals($card->getToken(), $stripeResponse['id']);
    }

    /**
     * @Given /^the user "([^"]*)" should have a stripe subscription for "([^"]*)"$/
     */
    public function theUserShouldHaveAStripeSubscriptionFor($username, $subscriptionName)
    {
        $dataContext =  $this->getMainContext()->getSubcontext('datacontext');
        $user = $dataContext->getUserManager()->findUserByUsername($username);
        $stripeProfile = $user->getStripeProfile();

        $subscription = $dataContext->getSubscriptionManager()->findOneBy([
            'name' => $subscriptionName
        ]);

        $customerData = $this->stripeClient->getCustomer([
            'id' => $stripeProfile->getStripeId()
        ]);

        assertEquals($customerData['subscription']['plan']['id'], $subscription->getId());
    }

    /**
     * @When /^I send a POST request to "([^"]*)" with the generated token$/
     */
    public function iSendAPostRequestToWithTheGeneratedToken($url)
    {
        $url  = $this->parameters['base_url'] . ltrim($this->replacePlaceHolder($url), '/') . '?access_token=' . $this->accessToken;

//        $this->addHeader('Authorization: Bearer ' . $this->accessToken);

        $this->getBrowser()->call($url, 'POST', $this->getHeaders(), json_encode([
            'token' => $this->stripeToken
        ]));
    }

    /**
     * @Given /^the response should contain json with created_at replaced with todays date:$/
     */
    public function theResponseShouldContainJsonWithCreatedAtReplacedWithTodaysDate(PyStringNode $jsonString)
    {
        $etalon = json_decode($this->replacePlaceHolder($jsonString->getRaw()), true);
        $actual = json_decode($this->getBrowser()->getLastResponse()->getContent(), true);

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n".$this->replacePlaceHolder($jsonString->getRaw())
            );
        }

        $actualSubscriptionStart = new \DateTime($actual[0]['created_at']);
        $expectedSubscriptionStart = new \DateTime();

        assertEquals($actualSubscriptionStart->format('Y-m-d'), $expectedSubscriptionStart->format('Y-m-d'));

        $etalon[0]['created_at'] = true;
        $actual[0]['created_at'] = true;

        assertCount(count($etalon), $actual);
        foreach ($actual as $key => $needle) {
            assertArrayHasKey($key, $etalon);
            assertEquals($etalon[$key], $actual[$key]);
        }
    }

    /**
     * @Given /^the response should contain json \(with subscription_start and subscription_end replaced with todays date\):$/
     */
    public function theResponseShouldContainJsonWithSubscriptionStartAndSubscriptionEndReplacedWithTodaysDate(PyStringNode $jsonString)
    {
        $etalon = json_decode($this->replacePlaceHolder($jsonString->getRaw()), true);
        $actual = json_decode($this->getBrowser()->getLastResponse()->getContent(), true);

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n".$this->replacePlaceHolder($jsonString->getRaw())
            );
        }

        $actualSubscriptionStart = new \DateTime($actual['stripe_profile']['subscription_start']);
        $expectedSubscriptionStart = new \DateTime();

        $actualSubscriptionEnd = new \DateTime($actual['stripe_profile']['subscription_end']);
        $expectedSubscriptionEnd = new \DateTime();
        $expectedSubscriptionEnd->add(new \DateInterval('P1M'));

        assertEquals($actualSubscriptionStart->format('Y-m-d'), $expectedSubscriptionStart->format('Y-m-d'));
        assertEquals($actualSubscriptionEnd->format('Y-m-d'), $expectedSubscriptionEnd->format('Y-m-d'));

        $etalon['stripe_profile']['subscription_start'] = true;
        $actual['stripe_profile']['subscription_start'] = true;
        $etalon['stripe_profile']['subscription_end'] = true;
        $actual['stripe_profile']['subscription_end'] = true;

        assertCount(count($etalon), $actual);
        foreach ($actual as $key => $needle) {
            assertArrayHasKey($key, $etalon);
            assertEquals($etalon[$key], $actual[$key]);
        }
    }

    /**
     * @When /^I send a GET request to "([^"]*)" with the client id and secret$/
     */
    public function iSendAGetRequestToWithTheClientIdAndSecret($url)
    {
        /** @var \Fridge\ApiBundle\Entity\Client $client */
        $client = $this->getMainContext()->client;
        $url .= '&client_secret=' . $client->getSecret() . '&client_id=' . $client->getPublicId();
        parent::iSendARequest('get', $url);
    }

    /**
     * @Given /^wait for (\d+) seconds$/
     */
    public function waitForSeconds($seconds)
    {
        sleep($seconds);
    }

    /**
     * @Given /^the mock API server will respond with the following responses:$/
     */
    public function theMockApiServerWillRespondWithTheFollowingResponses(TableNode $table)
    {
        Server::flush();
        Server::start();

        Server::enqueue(array_map(function ($e) {

            return new Response(
                $e['Status'],
                ['Content-Type' => 'application/json'],
                Stream::factory($e['Body'])
            );

        }, $table->getHash()));

    }

    public static function startApi()
    {
        Server::flush();
        Server::enqueue([new \GuzzleHttp\Message\Response(200)]);
    }

    public static function stopApi()
    {
        Server::flush();
        Server::stop();
    }

    /**
     * @Given /^the mock api server request at index (\d+) should have received a ([^"]*) request to "([^"]*)"$/
     * */
    public function theMockApiServerShouldHaveReceivedAGetRequestTo($index, $method, $path)
    {
        /** @var \GuzzleHttp\Message\Request $request */
        $request = Server::received(true)[$index];
        assertEquals($method, $request->getMethod());


        $actualPath = $request->getPath();

        if (strlen($request->getQuery()) > 0) {
            $actualPath .= '?' . $request->getQuery();
        }

        assertEquals($path, $actualPath);


        //array_pop(Server::received((true)));
    }


    /**
     * @Given /^the mock api server request at index (\d+) should have received a ([^"]*) request to "([^"]*)" with JSON content:$/
     */
    public function theMockApiServerShouldHaveReceivedAPostRequestToWithJsonContent($index, $method, $path, PyStringNode $jsonString)
    {
        /** @var \GuzzleHttp\Message\Request $request */
        $request = Server::received(true)[$index];

        assertEquals($request->getMethod(), $method);

        assertEquals($request->getPath(), $path);

        $etalon = json_decode($jsonString->getRaw(), true);
        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n".$this->replacePlaceHolder($jsonString->getRaw())
            );
        }
        $actual = json_decode($request->getBody(), true);

        assertCount(count($etalon), $actual);
        foreach ($actual as $key => $needle) {
            assertArrayHasKey($key, $etalon);
            assertEquals($etalon[$key], $actual[$key]);
        }

    }

    /**
     * @When /^I send a POST request to "([^"]*)" with the access token and body:$/
     */
    public function iSendAPostRequestToWithTheAccessTokenAndBody($url, PyStringNode $body)
    {
        $url  = $this->parameters['base_url'] . ltrim($this->replacePlaceHolder($url), '/') . '?access_token=' . $this->accessToken;
        $body = $this->replacePlaceHolder(trim($body));

        $this->getBrowser()->call($url, 'POST', $this->getHeaders(), $body);
    }

    /**
     * @When /^I send a GET request to "([^"]*)" with the access token and body$/
     */
    public function iSendAPogetstRequestToWithTheAccessTokenAndBody($url)
    {
        $url  = $this->parameters['base_url'] . ltrim($this->replacePlaceHolder($url), '/') . '?access_token=' . $this->accessToken;
        $this->getBrowser()->call($url, 'GET', $this->getHeaders());
    }

    /** @BeforeFeature */
    public static function setupFeature(FeatureEvent $event)
    {
        if ($event->getFeature()->getTitle() === 'POST podcast') {
            self::startApi();
        }
    }

    /** @AfterFeature */
    public static function teardownFeature(FeatureEvent $event)
    {
        if ($event->getFeature()->getTitle() === 'POST podcast') {
            self::stopApi();
        }
    }



}
