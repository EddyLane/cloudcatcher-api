Feature: GET get_user_invoices

  Background:
    Given the following users exist in the system:
      | username | email  | password |
      | bob      | bob    | bob      |
      | john     | john   | john     |
      | fridge   | fridge | fridge   |
    And the following subscriptions exist:
      | name  | description | price |
      | small | smallest    | 100   |
    And the user "fridge" is an admin
    And I set header "Content-type" with value "application/json"

  Scenario: Will return 200 and list of invoices
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    And I send a POST request to "/users/bob/subscriptions" with body:
    """
    {
        "subscription": "small"
    }
    """
    When I send a GET request to "/users/bob/invoices"
    Then the response code should be 200
    And the response should contain json:
    """
    [
        {
            "subscription":
                {
                    "name":"small",
                    "price":"100",
                    "description":"smallest"
                },
            "amount":100
        }
    ]
    """