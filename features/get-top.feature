Feature: GET podcast

  Background:
    Given I flush redis
    And the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
    And the default client exists
    And I set header "Content-type" with value "application/json"
    And I am authenticating as "bob" with "bob" password via oauth2


  Scenario: Will return the leaderboard
    Given the following leaderboard exists:
      | Score | Data                                                     |
      | 2     | { "name": "Top Podcast", "artwork": "Artwork" }          |
      | 1     | { "name": "Another Podcast", "artwork": "More Artwork" } |

    When I send a GET request to "/api/v1/leaderboard" with the access token and body
    Then print response
    Then the response code should be 200
    And the response should contain json:
    """
    [
        {
            "name": "Top Podcast",
            "artwork": "Artwork"
        },
        {
            "name": "Another Podcast",
            "artwork": "More Artwork"
        }
    ]
    """