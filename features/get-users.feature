@user
Feature: GET get_user

  Background:
    Given the following users exist in the system:
      | username | email  | password |
      | bob      | bob    | bob      |
      | tom      | tom    | tom      |
      | fridge   | fridge | fridge   |
    And the user "fridge" is an admin
    And I set header "Content-type" with value "application/json"

  Scenario: Will return 403 when not logged in
    When I send a GET request to "/users"
    Then the response code should be 403
    And the response should contain json:
    """
    {
        "code":403,
        "message":"Not authenticated"
    }
    """

  Scenario: Will return 403 when not admin
    Given I am authenticating as "bob" with "bob" password
    When I send a GET request to "/users"
    Then the response code should be 403
    And the response should contain json:
    """
    {
        "code":403,
        "message":"Access Denied"
    }
    """

  Scenario: Will return 200 when an admin
    Given I am authenticating as "fridge" with "fridge" password
    When I send a GET request to "/users"
    Then the response code should be 200
    And the response should contain json:
    """
    [
        {
            "username":"bob",
            "username_canonical":"bob",
            "email":"bob"
        },
        {
            "username":"tom",
            "username_canonical":"tom",
            "email":"tom"
        },
        {
            "username":"fridge",
            "username_canonical":"fridge",
            "email":"fridge"
        }
    ]
    """