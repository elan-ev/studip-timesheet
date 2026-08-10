Feature: Timesheet Contracts API
    In order to manage timesheet contracts
    As an authenticated StudIP user
    I want to fetch contracts via JSON:API
    I want to fetch a contract via JSON:API
    I want to create a contract via JSON:API
    I want to update a contract via JSON:API
    I want to delete a contract via JSON:API

    Background:
        Given I am logged into StudIP

    Scenario: Successfully create a contract
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "employee-id": "7e81ec247c151c02ffd479511e24cc03",
                    "institute-id": "2560f7c7674942a7dce8eeb238e15d93",
                    "type": "new",
                    "start-date": 1784620800,
                    "end-date": 1784642400,
                    "label": "NEW",
                    "hours-per-month": 80
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-contracts"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "contract_id"
        And the JSON response property "data.attributes.type" should equal or greater than "new"

    Scenario: Successfully fetch contracts
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-contracts"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a contract
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-contracts"

    Scenario: Successfully fetch a contract
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-contracts"

    Scenario: Successfully update a contract
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "type": "extension",
                    "start-date": 1784620800,
                    "end-date": 1784642400,
                    "label": "UPDATED",
                    "hours-per-month": 140
                }
            }
        }
        """
        When I send a "PATCH" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id}"
        Then the response status code should be 200
        And the JSON response property "data.attributes.type" should equal or greater than "extension"


    Scenario: Successfully delete the last
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id}"
        Then the response status code should be 204

