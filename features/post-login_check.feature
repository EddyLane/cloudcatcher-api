Feature: POST login_check

  Background:
    Given the following users exist in the system:
    | username | email | password |
    | bob      | bob   | bob      |

  Scenario: Will return a 200 when user successfully logs in with json
    And I set header "Content-Type" with value "application/x-www-form-urlencoded"
    When I send a POST request to "/security/login" with body:
    """
    {
        "_username": "bob",
        "_password": "bob"
    }
    """
    Then print response
    And the response code should be 200
    And the response should contain json:
    """
    {
      "username": "bob",
      "email": "bob"
    }
    """

  Scenario: Will return a 200 when user successfully logs in
    And I set header "Content-Type" with value "application/x-www-form-urlencoded"
    When I send a POST request to "/security/login" with form data:
    """
    _username=bob&_password=bob&_remember_me=1
    """
    And the response code should be 200
    And the response should contain json:
    """
    {
      "username": "bob",
      "email": "bob"
    }
    """

  Scenario: Will return a 403 when the password is incorrect
    And I set header "Content-Type" with value "application/x-www-form-urlencoded"
    When I send a POST request to "/security/login" with form data:
    """
    _username=bob&_password=ooo&_remember_me=1
    """
    And the response code should be 403
    And the response should contain "Bad credentials"

  Scenario: Will return a 403 when the username is incorrect
    And I set header "Content-Type" with value "application/x-www-form-urlencoded"
    When I send a POST request to "/security/login" with form data:
    """
    _username=ooo&_password=bob&_remember_me=1
    """
    And the response code should be 403
    And the response should contain "Bad credentials"

  Scenario: Will return a 403 when the username and password is incorrect
    And I set header "Content-Type" with value "application/x-www-form-urlencoded"
    When I send a POST request to "/security/login" with form data:
    """
    _username=ooo&_password=ooo&_remember_me=1
    """
    And the response code should be 403
    And the response should contain "Bad credentials"