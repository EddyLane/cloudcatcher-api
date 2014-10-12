@card
Feature: GET get_user_cards

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
      | john     | john  | john     |
      | fridge   | fridge | fridge   |
    And the user "fridge" is an admin
    And I set header "Content-type" with value "application/json"

  Scenario: Will return 200 when logged in
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/api/v1/users/bob/cards" with the generated token
    And I send a GET request to "/api/v1/users/bob/cards"
    Then the response code should be 200
    And the response should contain json:
    """
        [
            {
                "card_type_name":"Visa",
                "number":"**** **** **** 4242",
                "exp_month":7,
                "exp_year":2014,
                "id":1,
                "default": true
            }
        ]
    """

  Scenario: Will return 403 when not the user
    Given I am authenticating as "bob" with "bob" password
    And I send a GET request to "/api/v1/users/john/cards"
    Then the response code should be 403
    And the response should contain json:
    """
    {
        "code":403,
        "message":"Access Denied"
    }
    """

  Scenario: Will return 404 when no user exists
    Given I am authenticating as "fridge" with "fridge" password
    And I send a GET request to "/api/v1/users/missing/cards"
    And the response code should be 404
    And the response should contain json:
    """
    {
        "code":404,
        "message":""
    }
    """

  Scenario: Will return 200 when not the user but is an admin
    Given I am authenticating as "fridge" with "fridge" password
    And I send a GET request to "/api/v1/users/john/cards"
    Then the response code should be 200
    And the response should contain json:
    """
    []
    """