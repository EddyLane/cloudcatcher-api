  Feature: POST auth

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
    And the default client exists
    And I set header "Content-type" with value "application/json"

  Scenario: Will return a 200 when user successfully logs in
    When I send a GET request to "/oauth/v2/token?username=bob&password=bob&grant_type=password" with the client id and secret
    Then print response
    Then the response code should be 200
