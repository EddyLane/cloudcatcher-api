Feature: POST podcast

  Background:
    Given the following users exist in the system:
      | username | email | password |
      | bob      | bob   | bob      |
    And the default client exists
    And I set header "Content-type" with value "application/json"
    And I am authenticating as "bob" with "bob" password via oauth2

  Scenario: Will return a 201 when the user successfully adds a podcast to their firebase and nothing is stored in redis
    Given I flush redis
    Given the mock API server will respond with the following responses:
      | Status | Body                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
      | 200    | { "responseData": { "feed": { "feedUrl": "http://eddylane.co.uk/feed", "link": "eddylane.co.uk", "title": "Eddys Podcast", "entries": [{ "author": "", "categories": [], "content": "Some content", "contentSnippet": "Content", "link": "", "publishedData": "Thu, 16 Oct 2014 07:00:00 -0700", "title": "PodcastTitle" }] } } }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
      | 200    | { "resultCount":1,"results": [{"wrapperType":"track", "kind":"podcast", "collectionId":940806858, "trackId":940806858, "artistName":"Unknown", "collectionName":"Angular Air", "trackName":"Angular Air", "collectionCensoredName":"Angular Air", "trackCensoredName":"Angular Air", "collectionViewUrl":"https://itunes.apple.com/us/podcast/angular-air/id940806858?mt=2&uo=4", "feedUrl":"http://angularair.podbean.com/feed/", "trackViewUrl":"https://itunes.apple.com/us/podcast/angular-air/id940806858?mt=2&uo=4", "artworkUrl30":"http://a2.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.30x30-50.jpg", "artworkUrl60":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.60x60-50.jpg", "artworkUrl100":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.100x100-75.jpg", "collectionPrice":0.00, "trackPrice":0.00, "trackRentalPrice":0, "collectionHdPrice":0, "trackHdPrice":0, "trackHdRentalPrice":0, "releaseDate":"2014-11-15T00:58:00Z", "collectionExplicitness":"explicit", "trackExplicitness":"explicit", "trackCount":1, "country":"USA", "currency":"USD", "primaryGenreName":"Software How-To", "contentAdvisoryRating":"Explicit", "radioStationUrl":"https://itunes.apple.com/station/idra.940806858", "artworkUrl600":"http://a5.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.600x600-75.jpg", "genreIds":["1480", "26", "1318"], "genres":["Software How-To", "Podcasts", "Technology"]}]} |
      | 200    |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
    When I send a POST request to "/api/v1/podcasts" with the access token and body:
    """
    {
      "feed": "http://eddylane.co.uk/feed",
      "itunesId": 940806858
    }
    """
    Then the response code should be 201
    And the mock api server request at index 0 should have received a GET request to "/load?v=1.0&num=-1&output=json_xml&q=http://eddylane.co.uk/feed"
    And the mock api server request at index 1 should have received a GET request to "/lookup?media=podcast&id=940806858&kind=podcast"
    And redis should have the following data stored under "feed:http://eddylane.co.uk/feed":
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
    And redis should have the following data stored under "itunes:940806858":
    """
    {
        "resultCount":1,
        "results":[
            {
                "wrapperType":"track",
                "kind":"podcast",
                "collectionId":940806858,
                "trackId":940806858,
                "artistName":"Unknown",
                "collectionName":"Angular Air",
                "trackName":"Angular Air",
                "collectionCensoredName":"Angular Air",
                "trackCensoredName":"Angular Air",
                "collectionViewUrl":"https://itunes.apple.com/us/podcast/angular-air/id940806858?mt=2&uo=4",
                "feedUrl":"http://angularair.podbean.com/feed/",
                "trackViewUrl":"https://itunes.apple.com/us/podcast/angular-air/id940806858?mt=2&uo=4",
                "artworkUrl30":"http://a2.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.30x30-50.jpg",
                "artworkUrl60":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.60x60-50.jpg",
                "artworkUrl100":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.100x100-75.jpg",
                "collectionPrice":0.00,
                "trackPrice":0.00,
                "trackRentalPrice":0,
                "collectionHdPrice":0,
                "trackHdPrice":0,
                "trackHdRentalPrice":0,
                "releaseDate":"2014-11-15T00:58:00Z",
                "collectionExplicitness":"explicit",
                "trackExplicitness":"explicit",
                "trackCount":1,
                "country":"USA",
                "currency":"USD",
                "primaryGenreName":"Software How-To",
                "contentAdvisoryRating":"Explicit",
                "radioStationUrl":"https://itunes.apple.com/station/idra.940806858",
                "artworkUrl600":"http://a5.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.600x600-75.jpg",
                "genreIds":[
                    "1480",
                    "26",
                    "1318"
                ],
                "genres":[
                    "Software How-To",
                    "Podcasts",
                    "Technology"
                ]
            }
        ]
    }
    """
    And redis key "feed:http://eddylane.co.uk/feed" should have a ttl of 3600
    And redis key "itunes:940806858" should have a ttl of -1
    And the mock api server request at index 2 should have received a POST request to "/users/bob/podcasts.json" with JSON content:
    """
    {
      "amount": 1,
      "artist": "Unknown",
      "feed": "http://angularair.podbean.com/feed/",
      "itunesId": 940806858,
      "country": "USA",
      "explicit": true,
      "genres": [
          "Software How-To",
          "Podcasts",
          "Technology"
      ],
      "heard": [],
      "latestEpisode": {
          "author":"",
          "categories":[
          ],
          "content":"Some content",
          "contentSnippet":"Content",
          "link":"",
          "publishedData":"Thu, 16 Oct 2014 07:00:00 -0700",
          "title":"PodcastTitle"
      },
      "name": "Angular Air",
      "newEpisodes": 1,
      "slug": "angular-air",
      "artwork": {
          "30":"http://a2.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.30x30-50.jpg",
          "100":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.100x100-75.jpg"
      }
    }
    """
    And the response should contain json:
    """
    {
      "amount": 1,
      "artist": "Unknown",
      "feed": "http://angularair.podbean.com/feed/",
      "itunesId": 940806858,
      "country": "USA",
      "explicit": true,
      "genres": [
          "Software How-To",
          "Podcasts",
          "Technology"
      ],
      "heard": [],
      "latestEpisode": {
          "author":"",
          "categories":[
          ],
          "content":"Some content",
          "contentSnippet":"Content",
          "link":"",
          "publishedData":"Thu, 16 Oct 2014 07:00:00 -0700",
          "title":"PodcastTitle"
      },
      "name": "Angular Air",
      "newEpisodes": 1,
      "slug": "angular-air",
      "artwork": {
          "30":"http://a2.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.30x30-50.jpg",
          "100":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.100x100-75.jpg"
      },
      "subscriptions": 1
    }
    """

  Scenario: Will return a 201 when the user successfully adds a podcast to their firebase and responses are in redis (MUST BE EXECUTED AFTER PREVIOUS)
    Given the mock API server will respond with the following responses:
      | Status | Body                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
      | 200    |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
    When I send a POST request to "/api/v1/podcasts" with the access token and body:
    """
    {
      "feed": "http://eddylane.co.uk/feed",
      "itunesId": 940806858
    }
    """
    And print response
    Then the response code should be 201
    And the mock api server request at index 0 should have received a POST request to "/users/bob/podcasts.json" with JSON content:
    """
    {
      "amount": 1,
      "artist": "Unknown",
      "feed": "http://angularair.podbean.com/feed/",
      "itunesId": 940806858,
      "country": "USA",
      "explicit": true,
      "genres": [
          "Software How-To",
          "Podcasts",
          "Technology"
      ],
      "heard": [],
      "latestEpisode": {
          "author":"",
          "categories":[
          ],
          "content":"Some content",
          "contentSnippet":"Content",
          "link":"",
          "publishedData":"Thu, 16 Oct 2014 07:00:00 -0700",
          "title":"PodcastTitle"
      },
      "name": "Angular Air",
      "newEpisodes": 1,
      "slug": "angular-air",
      "artwork": {
          "30":"http://a2.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.30x30-50.jpg",
          "100":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.100x100-75.jpg"
      }
    }
    """
    And the response should contain json:
    """
    {
      "amount": 1,
      "artist": "Unknown",
      "feed": "http://angularair.podbean.com/feed/",
      "itunesId": 940806858,
      "country": "USA",
      "explicit": true,
      "genres": [
          "Software How-To",
          "Podcasts",
          "Technology"
      ],
      "heard": [],
      "latestEpisode": {
          "author":"",
          "categories":[
          ],
          "content":"Some content",
          "contentSnippet":"Content",
          "link":"",
          "publishedData":"Thu, 16 Oct 2014 07:00:00 -0700",
          "title":"PodcastTitle"
      },
      "name": "Angular Air",
      "newEpisodes": 1,
      "slug": "angular-air",
      "artwork": {
          "30":"http://a2.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.30x30-50.jpg",
          "100":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.100x100-75.jpg"
      },
      "subscriptions": 2
    }
    """

    Scenario: Podcast inserted in leaderboard if does not exist
    Given I flush redis
    Given the mock API server will respond with the following responses:
      | Status | Body                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
      | 200    | { "responseData": { "feed": { "feedUrl": "http://eddylane.co.uk/feed", "link": "eddylane.co.uk", "title": "Eddys Podcast", "entries": [{ "author": "", "categories": [], "content": "Some content", "contentSnippet": "Content", "link": "", "publishedData": "Thu, 16 Oct 2014 07:00:00 -0700", "title": "PodcastTitle" }] } } }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
      | 200    | { "resultCount":1,"results": [{"wrapperType":"track", "kind":"podcast", "collectionId":940806858, "trackId":940806858, "artistName":"Unknown", "collectionName":"Angular Air", "trackName":"Angular Air", "collectionCensoredName":"Angular Air", "trackCensoredName":"Angular Air", "collectionViewUrl":"https://itunes.apple.com/us/podcast/angular-air/id940806858?mt=2&uo=4", "feedUrl":"http://angularair.podbean.com/feed/", "trackViewUrl":"https://itunes.apple.com/us/podcast/angular-air/id940806858?mt=2&uo=4", "artworkUrl30":"http://a2.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.30x30-50.jpg", "artworkUrl60":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.60x60-50.jpg", "artworkUrl100":"http://a4.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.100x100-75.jpg", "collectionPrice":0.00, "trackPrice":0.00, "trackRentalPrice":0, "collectionHdPrice":0, "trackHdPrice":0, "trackHdRentalPrice":0, "releaseDate":"2014-11-15T00:58:00Z", "collectionExplicitness":"explicit", "trackExplicitness":"explicit", "trackCount":1, "country":"USA", "currency":"USD", "primaryGenreName":"Software How-To", "contentAdvisoryRating":"Explicit", "radioStationUrl":"https://itunes.apple.com/station/idra.940806858", "artworkUrl600":"http://a5.mzstatic.com/us/r30/Podcasts3/v4/40/5f/35/405f35d7-a9a1-5c0c-fe4d-95321b06c778/mza_763499025885449747.600x600-75.jpg", "genreIds":["1480", "26", "1318"], "genres":["Software How-To", "Podcasts", "Technology"]}]} |
      | 200    |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
    When I send a POST request to "/api/v1/podcasts" with the access token and body:
    """
    {
      "feed": "http://eddylane.co.uk/feed",
      "itunesId": 940806858
    }
    """
    Then the response code should be 201
