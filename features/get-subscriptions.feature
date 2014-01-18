@subscription
Feature: GET get_subscriptions

  Scenario: Will return 200 and subscription collection
    Given the following subscriptions exist:
      | name   | description | price |
      | small  | smallest    | 100   |
      | middle | middlest    | 200   |
      | large  | largest     | 300   |
    When I send a GET request to "/subscriptions"
    Then the response code should be 200
    And the response should contain json:
    """
        [
            {
                "name":"small",
                "price":"100",
                "description":"smallest"
            },
            {   "name":"middle",
                "price":"200",
                "description":"middlest"
            },
            {
                "name":"large",
                "price":"300",
                "description":"largest"
            }
        ]
    """