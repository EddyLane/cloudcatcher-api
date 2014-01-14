@subscription
Feature: GET get_subscription

  Scenario: Will return 200 and subscription object
    Given the following subscriptions exist:
      | name   | description | price |
      | small  | smallest    | 100   |
    When I send a GET request to "/subscriptions/1"
    Then the response code should be 200
    And the response should contain json:
    """
    {
        "name":"small",
        "price":"100",
        "description":"smallest",
        "id":1
    }
    """

  Scenario: Will return 404 when trying to get a subscription which does not exist
    When I send a GET request to "/subscriptions/2"
    Then the response code should be 404
    And the response should contain json:
    """
    {
        "code":404,
        "message":""
    }
    """