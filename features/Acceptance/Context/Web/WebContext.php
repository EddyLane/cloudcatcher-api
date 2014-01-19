<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 11/12/2013
 * Time: 21:51
 */

namespace Acceptance\Context\Web;

use Behat\CommonContexts\WebApiContext;
use Behat\Gherkin\Node\TableNode;
use Buzz\Browser;
use Behat\Gherkin\Node\PyStringNode;

class WebContext extends WebApiContext
{
    private $parameters;
    public $stripeToken;
    protected $stripePk;
    protected $stripeSk;
    protected $stripeClient;

    public function __construct($parameters)
    {
        $this->stripePk = $parameters['stripe_pk'];
        $this->stripeSk = $parameters['stripe_sk'];
        $this->parameters = $parameters;

        $this->stripeClient = new \ZfrStripe\Client\StripeClient($this->stripeSk);

        parent::__construct($parameters['base_url']);
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

        $this->stripeToken = $stripeResponse['id'];
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
        $url  = $this->parameters['base_url'].'/'.ltrim($this->replacePlaceHolder($url), '/');

        $this->getBrowser()->call($url, 'POST', $this->getHeaders(), json_encode([
            'token' => $this->stripeToken
        ]));
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
     * @Given /^wait for (\d+) seconds$/
     */
    public function waitForSeconds($seconds)
    {
        sleep($seconds);
    }
}
