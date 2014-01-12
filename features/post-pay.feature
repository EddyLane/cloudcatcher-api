Feature: POST post_pay

  Background:
    Given no payments exist in the system
    And the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
    And I set header "Content-type" with value "application/json"

  Scenario: CARD Will return a 403 when not logged in
   Given I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 12        | 2013     |
    When I send a POST request to "/payment/pays" with the generated token
    Then the response code should be 403
    And the response should contain json:
    """
        {
          "code": 403,
          "message": "User not logged in"
        }
    """
    And no payments should exist in the system

  Scenario: Will return 201 when payment successful
  Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4242 4242 4242 4242 | 123 | 12        | 2013     |
    When I send a POST request to "/payment/pays" with the generated token
    Then print response
    Then the response code should be 201
    And the response should contain json:
    """
        {
            "completed" : true
        }
    """
    And only the following payments should now exist in the system:
      | id | token     | completed |
      | 1  | { token } | true      |
    And a stripe id should be set for the user with username "bob"

  Scenario: Will return 400 when no token has been posted
   Given I am authenticating as "bob" with "bob" password
    When I send a POST request to "/payment/pays.json" with body:
    """
        {
        }
    """
    Then the response code should be 400
    And the response should contain json:
    """
        {
          "code": 400,
          "message": "Request parameter \"token\" is empty"
        }
    """
    And no payments should exist in the system

  Scenario: Will return 402 when a card is declined
    Given I am authenticating as "bob" with "bob" password
    And I generate a stripe token from the following card details:
      | number              | cvc | exp_month | exp_year |
      | 4000 0000 0000 0002 | 123 | 12        | 2013     |
    When I send a POST request to "/payment/pays" with the generated token
    Then the response code should be 402
    And the response should contain json:
    """
        {
          "code": 402,
          "message": "Your card was declined."
        }
    """
    And no payments should exist in the system