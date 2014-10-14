Feature: POST podcast

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
    And the default client exists
    And I set header "Content-type" with value "application/json"
    And I am authenticating as "bob" with "bob" password via oauth2

  Scenario: Will return a 201 when the user successfully adds a podcast to their firebase
    When I send a POST request to "/api/v1/podcasts" with the access token and body:
    """
    {
      "itunesId": 123456,
      "feed": "http://eddylane.co.uk/feed",
      "name": "Eddys Podcast",
      "artist": "Eddy Lane",
      "country": "United Kingdom",
      "slug": "eddys-podcast",
      "genres": [
        "comedy", "banter"
      ]
    }
    """
    Then print response
    Then the response code should be 201
    And the mock api server should have received a POST request to "/users/bob/podcasts.json" with JSON content:
    """
    {
      "itunesId": 123456,
      "feed": "http://eddylane.co.uk/feed",
      "name": "Eddys Podcast",
      "artist": "Eddy Lane",
      "country": "United Kingdom",
      "slug": "eddys-podcast",
      "genres": [
        "comedy", "banter"
      ]
    }
    """
