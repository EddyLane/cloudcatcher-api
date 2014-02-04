Feature: POST post_user registration

  Background:
    Given no users exist in the system
    And I set header "Content-type" with value "application/json"

  Scenario: Will return a 201 when registration successful
    When I send a POST request to "/users" with body:
    """
    {
      "username": "bobtingtwo",
      "plainPassword": {
        "first": "bobbyTwo",
        "second": "bobbyTwo"
      },
      "email": "bobTwo@bob.com"
    }
    """
    Then the response code should be 201
    And the response should contain json:
    """
    {
      "username":"bobtingtwo",
      "username_canonical":"bobtingtwo",
      "email":"bobTwo@bob.com"
    }
    """

  Scenario: Will return a 400 when passwords do not match
    When I send a POST request to "/users" with body:
    """
    {
      "username": "bobtingtwo",
      "plainPassword": {
        "first": "bobbyTwo",
        "second": "bobbyThree"
      },
      "email": "bobTwo@bob.com"
    }
    """
    Then the response code should be 400
    And print response
    And the response should contain json:
    """
    {
      "code":400,
      "message":"Validation Failed",
      "errors":{
          "children":{
              "email":[],
              "username":[],
              "plainPassword":{
                  "children":{
                      "first":{
                          "errors":[
                              "fos_user.password.mismatch"
                          ]
                      },
                      "second":[]
                    }
                }
            }
        }
    }
    """