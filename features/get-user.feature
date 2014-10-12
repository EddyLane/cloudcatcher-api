@user
Feature: GET get_user_me

  Background:
    Given the following users exist in the system:
      | username | email  | password |
      | bob      | bob    | bob      |
      | tom      | tom    | tom      |
      | fridge   | fridge | fridge   |
    And the default client exists
    And the user "fridge" is an admin

  Scenario: Will return 403 when not logged in
    When I send a GET request to "/api/v1/users/bob"
    Then the response code should be 401
    And the response should contain json:
    """
    {"error":"access_denied","error_description":"OAuth2 authentication required"}
    """

  Scenario: Will return 403 when not user and not admin
    Given I am authenticating as "tom" with "tom" password via oauth2
    When I send a GET request to "/api/v1/users/bob"
    Then the response code should be 403
    And the response should contain json:
    """
    {"code":403,"message":"Access Denied"}
    """

  Scenario: Will return 200 when not user but is admin
    Given I am authenticating as "fridge" with "fridge" password via oauth2
    When I send a GET request to "/api/v1/users/bob" with access token
    Then the response code should be 200
    And the response should contain json:
    """
    {
        "username":"bob",
        "username_canonical":"bob",
        "email":"bob",
        "id":1,
        "gcm_ids":[]
    }
    """

  Scenario: Will return 200 when user
    Given I am authenticating as "bob" with "bob" password via oauth2
    When I send a GET request to "/api/v1/users/bob" with access token
    Then the response code should be 200
    And the response should contain json:
    """
    {
        "username":"bob",
        "username_canonical":"bob",
        "email":"bob",
        "id":1,
        "gcm_ids":[]
    }
    """