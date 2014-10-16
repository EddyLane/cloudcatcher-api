Feature: POST podcast

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
    And the default client exists
    And I set header "Content-type" with value "application/json"
    And I am authenticating as "bob" with "bob" password via oauth2

  Scenario: Will return a 201 when the user successfully adds a podcast to their firebase
   Given the mock API server will respond with the following responses:
    | Status | Body                                                                                                                                                                                                                                                                                                                              |
    | 200    | { "responseData": { "feed": { "feedUrl": "http://eddylane.co.uk/feed", "link": "eddylane.co.uk", "title": "Eddys Podcast", "entries": [{ "author": "", "categories": [], "content": "Some content", "contentSnippet": "Content", "link": "", "publishedData": "Thu, 16 Oct 2014 07:00:00 -0700", "title": "PodcastTitle" }] } } } |
    | 200    |                                                                                                                                                                                                                                                                                                                                   |
    When I send a POST request to "/api/v1/podcasts" with the access token and body:
    """
    {
      "feed": "http://eddylane.co.uk/feed"
    }
    """
    And print response
    Then the response code should be 201
    And the mock api server request at index 0 should have received a GET request to "/load?v=1.0&num=-1&output=json_xml&q=http%3A%2F%2Feddylane.co.uk%2Ffeed"
    And redis should have the following data stored under "http://eddylane.co.uk/feed":
    """
    {
        "responseData":{
            "feed":{
                "feedUrl":"http://eddylane.co.uk/feed",
                "link":"eddylane.co.uk",
                "title":"Eddys Podcast",
                "entries":[
                    {
                        "author":"",
                        "categories":[

                        ],
                        "content":"Some content",
                        "contentSnippet":"Content",
                        "link":"",
                        "publishedData":"Thu, 16 Oct 2014 07:00:00 -0700",
                        "title":"PodcastTitle"
                    }
                ]
            }
        }
    }
    """
    And redis key "http://eddylane.co.uk/feed" should have a ttl of 3600
    And the mock api server request at index 1 should have received a POST request to "/users/bob/podcasts.json" with JSON content:
    """
    {
        "feed": "http://eddylane.co.uk/feed",
        "name": "Eddys Podcast"
    }
    """
