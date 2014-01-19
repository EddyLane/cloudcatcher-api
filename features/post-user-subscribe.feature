@user @subscription
Feature: POST post_user_subscription

  Background:
    Given the following users exist in the system:
      | username | email  | password |
      | bob      | bob    | bob      |
      | tom      | tom    | tom      |
      | fridge   | fridge | fridge   |
    And the following subscriptions exist:
      | name   | description | price |
      | small  | smallest    | 100   |
    And the user "fridge" is an admin
    And I set header "Content-type" with value "application/json"

  Scenario: Will return a 201 when the user has a default card and successfully subscribes to a subscription
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    When I send a POST request to "/users/bob/subscriptions" with body:
    """
    {
        "subscription": "small"
    }
    """
    Then the response code should be 200
     And the response should contain json (with subscription_start and subscription_end replaced with todays date):
    """
    {
        "username":"bob",
        "email":"bob",
        "stripe_profile":{
            "subscription_start":"REPLACED",
            "subscription_end":"REPLACED",
            "subscription":{
                "name":"small",
                "price":"100",
                "description":"smallest"
            },
            "cards": [{
                "card_type_name":"Visa",
                "number":"**** **** **** 4242",
                "exp_month":7,
                "exp_year":2014,
                "id":1,
                "default": true
            }]
        }
    }
    """
    And the user "bob" should have a stripe subscription for "small"

  Scenario: Will return a 403 when the user has no cards
    Given I am authenticating as "tom" with "tom" password
    When I send a POST request to "/users/tom/subscriptions" with body:
    """
    {
        "subscription": "small"
    }
    """
    Then the response code should be 403
    And the response should contain json:
    """
    {
        "code":403,
        "message":"User has no selected card"
    }
    """
