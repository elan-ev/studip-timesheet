Feature: Timesheet Permissions API
    In order to manage timesheet permissions
    As an authenticated StudIP user
    I want to fetch permissions via JSON:API
    I want to fetch a permission via JSON:API
    I want to create a permission via JSON:API
    I want to update a permission via JSON:API
    I want to delete a permission via JSON:API

    Background:
        Given I am logged into StudIP

    Scenario: Successfully create a permission
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "user-id": "7e81ec247c151c02ffd479511e24cc03",
                    "institute-id": "2560f7c7674942a7dce8eeb238e15d93",
                    "role": "admin"
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-permissions"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "permission_id"
        And the JSON response property "data.attributes.role" should equal or greater than "admin"

    Scenario: Successfully fetch permissions
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-permissions"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a permission
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-permissions/{permission_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-permissions"

    Scenario: Successfully fetch a permission
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-permissions/{permission_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-permissions"

    Scenario: Successfully update a permission
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "role": "supervisor"
                }
            }
        }
        """
        When I send a "PATCH" request to "/jsonapi.php/v1/timesheet-permissions/{permission_id}"
        Then the response status code should be 201
        And the JSON response property "data.attributes.role" should equal or greater than "supervisor"


    Scenario: Successfully delete the last
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-permissions/{permission_id}"
        Then the response status code should be 204

