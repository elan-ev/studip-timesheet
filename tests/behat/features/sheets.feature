Feature: Timesheet Sheets API
    In order to manage timesheet sheets
    As an authenticated StudIP user
    I want to fetch sheets via JSON:API
    I want to fetch a sheet via JSON:API
    I want to create a sheet via JSON:API
    I want to update a sheet via JSON:API
    I want to delete a sheet via JSON:API

    I want to fetch contract sheets via JSON:API
    I want to fetch a contract sheet via JSON:API
    I want to create a contract sheet via JSON:API
    I want to update a contract sheet via JSON:API
    I want to delete a contract sheet via JSON:API

    Background:
        Given I am logged into StudIP
        And a contract saved as "contract_id_for_sheet" exists

    Scenario: Successfully create a sheet
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "contract-id": {contract_id_for_sheet},
                    "year": 2026,
                    "month": 6,
                    "status": "open",
                    "workflow-config": "[\"test\": \"test\"]",
                    "frozen-hours-per-month": 20
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-sheets"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "sheet_id"
        And the JSON response property "data.attributes.status" should equal or greater than "open"

    Scenario: Successfully fetch sheets
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-sheets"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a sheet
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-sheets"

    Scenario: Successfully update a sheet
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "year": 2026,
                    "month": 7,
                    "status": "submitted",
                    "workflow-config": "[\"test\": \"EDITED\"]",
                    "frozen-hours-per-month": 100
                }
            }
        }
        """
        When I send a "PATCH" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id}"
        Then the response status code should be 201
        And the JSON response property "data.attributes.status" should equal or greater than "submitted"

    Scenario: Successfully delete a sheet
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id}"
        Then the response status code should be 204

    # CONTRACT-SHEETS
    Scenario: Successfully create a contract sheet
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "year": 2026,
                    "month": 6,
                    "status": "open",
                    "workflow-config": "[\"test\": \"test\"]",
                    "frozen-hours-per-month": 20
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_sheet}/sheets"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "contract_sheet_id"
        And the JSON response property "data.attributes.status" should equal or greater than "open"

    Scenario: Successfully fetch contract sheets
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_sheet}/sheets"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a contract sheet
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_sheet}/sheets/{contract_sheet_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-sheets"

    Scenario: Successfully update a contract sheet
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "year": 2026,
                    "month": 7,
                    "status": "submitted",
                    "workflow-config": "[\"test\": \"EDITED\"]",
                    "frozen-hours-per-month": 100
                }
            }
        }
        """
        When I send a "PATCH" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_sheet}/sheets/{contract_sheet_id}"
        Then the response status code should be 201
        And the JSON response property "data.attributes.status" should equal or greater than "submitted"
    Scenario: Successfully delete a contract sheet
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-contracts/{contract_id_for_sheet}/sheets/{contract_sheet_id}"
        Then the response status code should be 204



