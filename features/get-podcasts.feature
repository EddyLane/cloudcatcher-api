Feature: GET get_podcasts

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
    And the default client exists
    And I set header "Content-type" with value "application/json"
    And I am authenticating as "bob" with "bob" password via oauth2

  Scenario: 200 get request
    When I send a GET request to "/api/v1/podcasts" with the access token and body
    Then print response



