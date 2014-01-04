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

class WebContext extends WebApiContext
{
    private $parameters;
    public $stripeToken;
    protected $stripePk;


    public function __construct($parameters)
    {
        $this->stripePk = $parameters['stripe_pk'];
        $this->parameters = $parameters;
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
     * @When /^I send a POST request to "([^"]*)" with the generated token$/
     */
    public function iSendAPostRequestToWithTheGeneratedToken($url)
    {
        $url  = $this->parameters['base_url'].'/'.ltrim($this->replacePlaceHolder($url), '/');

        $this->getBrowser()->call($url, 'POST', $this->getHeaders(), json_encode([
            'token' => $this->stripeToken
        ]));
    }


} 