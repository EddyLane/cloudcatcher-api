@card
Feature: DELETE delete_user_card single

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
      | john     | john  | john     |
      | fridge   | fridge | fridge   |
    And the user "fridge" is an admin
    And I set header "Content-type" with value "application/json"

  Scenario: Will return 200 when successful delete
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    And the following cards should exist for user "bob":
      | id | number              | cardType  | expYear  | expMonth  |
      | 1  | **** **** **** 4242 | 1         | 2014     | 7         |
    And the card with id 1 should have been persisted to stripe for user "bob"
    When I send a DELETE request to "/users/bob/cards/1"
    Then print response
    Then the response code should be 200
    And the response should contain json:
    """
    {
        "card_type_name":"Visa",
        "number":"**** **** **** 4242"
        ,"exp_month":7,
        "exp_year":2014,
        "default":true
    }
    """
    And no cards should exist in the system


  Scenario: Will return 403 when not user and not admin
    Given I am authenticating as "fridge" with "fridge" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/john/cards" with the generated token
    Given I am authenticating as "bob" with "bob" password
    And I send a DELETE request to "/users/john/cards/1"
    Then the response code should be 403
    And the response should contain json:
    """
    {
        "code":403,
        "message":"Access Denied"
    }
    """

  Scenario: Will return 200 when not user and is admin
    Given I am authenticating as "fridge" with "fridge" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    And I send a DELETE request to "/users/bob/cards/1"
    Then the response code should be 200
    And the response should contain json:
    """
    {
        "card_type_name":"Visa",
        "number":"**** **** **** 4242"
        ,"exp_month":7,
        "exp_year":2014,
        "default":true
    }
    """


  Scenario: Will return 404 when no user exists
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    And I send a DELETE request to "/users/missing/cards/1"
    Then the response code should be 404
    And the response should contain json:
    """
    {
        "code":404,
        "message":""
    }
    """

  Scenario: Will return 404 when no card exists
    Given I am authenticating as "bob" with "bob" password
    And I send a DELETE request to "/users/bob/cards/1"
    Then the response code should be 404
    And the response should contain json:
    """
    {
        "code":404,
        "message":""
    }
    """