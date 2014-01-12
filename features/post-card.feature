@card
Feature: POST post_user_card

  Background:
    Given the following users exist in the system:
      | username | email  | password |
      | bob      | bob    | bob      |
      | tom      | tom    | tom      |
      | fridge   | fridge | fridge   |
    And the user "fridge" is an admin
    And I set header "Content-type" with value "application/json"

  @security
  Scenario: Will return a 404 when no user exists
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/missing/cards" with the generated token
    And the response code should be 404
    And the response should contain json:
    """
    {
        "code":404,
        "message":""
    }
    """
    And no cards should exist in the system

  Scenario: Will return a 402 when a token has already been used
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    And I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 402
    And the response should contain json:
    """
    {
        "code":402,
        "message":"Invalid token"
    }
    """
    And the following cards should exist for user "bob":
    | id | number              | cardType  | expYear  | expMonth  |
    | 1  | **** **** **** 4242 | 1         | 2014     | 7         |

  Scenario: Will return a 403 when trying to add a card for another user (if not admin)
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/tom/cards" with the generated token
    And the response code should be 403
    And the response should contain json:
    """
    {
        "code":403,
        "message":"Access Denied"
    }
    """
    And no cards should exist in the system

  Scenario: Will return a 201 when trying to add a card for another user (if admin)
    Given I am authenticating as "fridge" with "fridge" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    And the response code should be 201
    And the response should contain json:
    """
    {
        "card_type_name":"Visa",
        "number":"**** **** **** 4242",
        "card_type":1,
        "exp_month":7,
        "exp_year":2014,
        "id":1
    }
    """
    And the following cards should exist for user "bob":
      | id | number              | cardType  | expYear  | expMonth  |
      | 1  | **** **** **** 4242 | 1         | 2014     | 7         |
    And the card with id 1 should have been persisted to stripe for user "bob"

  Scenario: Will return a 403 when not logged in
    Given I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 07        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    And the response code should be 403
    And the response should contain json:
    """
    {
        "code":403,
        "message":"Not authenticated"
    }
    """
    And no cards should exist in the system

  Scenario: Will return 400 when no card token has been posted
    Given I am authenticating as "bob" with "bob" password
    When I send a POST request to "/users/bob/cards" with body:
    """
        {
        }
    """
    Then the response code should be 400
    And the response should contain json:
    """
    {
        "code":400,
        "message":"Request parameter \"token\" is empty"
    }
    """
    And no cards should exist in the system

  Scenario: Will return 402 when invalid card token has been posted
    Given I am authenticating as "bob" with "bob" password
    When I send a POST request to "/users/bob/cards" with body:
    """
        {
        "token": 199
        }
    """
    Then the response code should be 402
    And the response should contain json:
    """
    {
        "code":402,
        "message":"Invalid token id: 199"
    }
    """
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
    And exactly 1 stripe profile should have been created for user "bob"
    And the following cards should exist for user "bob":
      | id | number              | cardType  | expYear  | expMonth  |
      | 1  | **** **** **** 4242 | 1         | 2014     | 8         |
    And the card with id 1 should have been persisted to stripe for user "bob"

  Scenario: Will return a 201 when a card is successfully created and the user does have a stripe profile
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    And the following cards should exist for user "bob":
      | id | number              | cardType  | expYear  | expMonth  |
      | 1  | **** **** **** 4242 | 1         | 2014     | 8         |
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 5555 5555 5555 4444 | 123 | 03        | 2014     |
    When I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    And the response should contain json:
    """
    {
        "card_type_name":"MasterCard",
        "number":"**** **** **** 4444",
        "card_type":2,
        "exp_month":3,
        "exp_year":2014,
        "id":2
    }
    """
    And exactly 1 stripe profile should have been created for user "bob"
    And the following cards should exist for user "bob":
      | id | number              | cardType  | expYear  | expMonth  |
      | 2  | **** **** **** 4444 | 2         | 2014     | 3         |
      | 1  | **** **** **** 4242 | 1         | 2014     | 8         |
    And the card with id 2 should have been persisted to stripe for user "bob"

  @card-type
  Scenario: Visa correctly saved
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
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

  @card-type
  Scenario: MasterCard correctly saved
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 5555 5555 5555 4444 | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 201
    Then the response should contain json:
    """
    {
        "card_type_name":"MasterCard",
        "number":"**** **** **** 4444",
        "card_type":2,
        "exp_month":8,
        "exp_year":2014,
        "id":1
    }
    """

  @card-type
  Scenario: American Express correctly saved
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 378282246310005     | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    Then print response
    Then the response code should be 201
    Then the response should contain json:
    """
    {
        "card_type_name":"American Express",
        "number":"**** ****** *0005",
        "card_type":3,
        "exp_month":8,
        "exp_year":2014,
        "id":1
    }
    """

  @card-type
  Scenario: Discover not supported
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 6011111111111117    | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 402
    Then the response should contain json:
    """
    {
        "code":402,
        "message":"Your card is not supported. Please use a Visa, MasterCard, or American Express card"
    }
    """
    And no cards should exist in the system


  @card-type
  Scenario: Diners Club not supported
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 30569309025904      | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 402
    Then the response should contain json:
    """
    {
        "code":402,
        "message":"Your card is not supported. Please use a Visa, MasterCard, or American Express card"
    }
    """
    And no cards should exist in the system


  @card-type
  Scenario: JCB not supported
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 3530111333300000    | 123 | 08        | 2014     |
    And I send a POST request to "/users/bob/cards" with the generated token
    Then the response code should be 402
    Then the response should contain json:
    """
    {
        "code":402,
        "message":"Your card is not supported. Please use a Visa, MasterCard, or American Express card"
    }
    """
    And no cards should exist in the system
