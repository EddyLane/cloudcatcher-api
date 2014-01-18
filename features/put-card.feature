@card
Feature: PUT put_user_card

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
      | john     | john  | john     |
      | fridge   | fridge | fridge   |
    And the user "fridge" is an admin
    And I set header "Content-type" with value "application/json"

  Scenario: Will return a 201 when a card is successfully made default
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 5555 5555 5555 4444 | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    When I send a PUT request to "/users/bob/cards/1" with body:
    """
    {
        "card_type_name": "MasterCard",
        "default": true,
        "exp_month": 12,
        "exp_year": 2015,
        "id": 1,
        "number": "**** **** **** 4444"
    }
    """
    Then the response code should be 200
    And the response should contain json:
    """
    {
        "card_type_name":"MasterCard",
        "number":"**** **** **** 4444",
        "exp_month":8,
        "exp_year":2014,
        "id":1,
        "default": true
    }
    """
    And I send a GET request to "/users/bob/cards"
    Then the response should contain json:
    """
    [
        {
            "card_type_name":"Visa",
            "number":"**** **** **** 4242",
            "exp_month":8,
            "exp_year":2014,
            "id":2,
            "default": false
        },
        {
            "card_type_name":"MasterCard",
            "number":"**** **** **** 4444",
            "exp_month":8,
            "exp_year":2014,
            "id":1,
            "default": true
        }
    ]
    """