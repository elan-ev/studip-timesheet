Feature: Timesheet Supervisors API
    In order to manage timesheet supervisors
    As an authenticated StudIP user
    I want to fetch supervisors via JSON:API
    I want to fetch a supervisor via JSON:API
    I want to create a supervisor via JSON:API
    I want to update a supervisor via JSON:API
    I want to delete a supervisor via JSON:API

    I want to fetch contract supervisors via JSON:API
    I want to fetch a contract supervisor via JSON:API
    I want to create a contract supervisor via JSON:API
    I want to update a contract supervisor via JSON:API
    I want to delete a contract supervisor via JSON:API

    Background:
        Given I am logged into StudIP
        And a contract saved as "contract_id_for_supervisor" exists

    Scenario: Successfully create a supervisor
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "contract-id": {contract_id_for_supervisor},
                    "user-id": "7e81ec247c151c02ffd479511e24cc03"
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-supervisors"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "supervisor_id"
        And the JSON response property "data.type" should equal or greater than "timesheet-contract-supervisors"

    Scenario: Successfully fetch supervisors
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-supervisors"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a supervisor
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-supervisors/{supervisor_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-contract-supervisors"

    Scenario: Successfully delete a supervisor
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-supervisors/{supervisor_id}"
        Then the response status code should be 204

    # CONTRACT-SUPERVISOR
    Scenario: Successfully create a contract supervisor
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "user-id": "7e81ec247c151c02ffd479511e24cc03"
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_supervisor}/supervisors"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "contract_supervisor_id"
        And the JSON response property "data.type" should equal or greater than "timesheet-contract-supervisors"

    Scenario: Successfully fetch contract supervisors
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_supervisor}/supervisors"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a contract supervisor
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_supervisor}/supervisors/{contract_supervisor_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-contract-supervisors"

    Scenario: Successfully delete a contract supervisor
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_supervisor}/supervisors/{contract_supervisor_id}"
        Then the response status code should be 204
