Feature: POST post_user_card

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
    And I set header "Content-type" with value "application/json"

  Scenario: Will return a 404 when no user exists
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/missing/cards" with the generated token
    And the response code should be 404
    And no cards should exist in the system

  Scenario: Will return a 403 when not logged in
    Given I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    And the response code should be 403
    And no cards should exist in the system

  Scenario: Will return a 201 when a card is successfully created and the user does not have a stripe profile
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 08        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    And the response should contain json:
    """
    {
        "card_type_name":"Visa",
        "number":"**** **** **** 4242",
        "card_type":1,
        "exp_month":8,
        "exp_year":2014,
        "id":1
    }
    """
    And the following cards should exist for user "bob":
      | id | number              | cardType  | expYear  | expMonth  |
      | 1  | **** **** **** 4242 | 1         | 2014     | 8         |
    And the card with id 1 should have been persisted to stripe for user "bob"
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 08        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    And the response should contain json:
    """
    {
        "card_type_name":"Visa",
        "number":"**** **** **** 4242",
        "card_type":1,
        "exp_month":8,
        "exp_year":2014,
        "id":2
    }
    """
    And the card with id 2 should have been persisted to stripe for user "bob"
