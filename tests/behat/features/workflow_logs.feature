Feature: timesheet Workflow Logs API
    In order to manage timesheet workflow logs
    As an authenticated StudIP user
    I want to fetch workflow logs via JSON:API
    I want to fetch a workflow log via JSON:API
    I want to create a workflow log via JSON:API
    I want to update a workflow log via JSON:API
    I want to delete a workflow log via JSON:API

    I want to fetch sheet workflow logs via JSON:API
    I want to fetch a sheet workflow log via JSON:API
    I want to create a sheet workflow log via JSON:API
    I want to update a sheet workflow log via JSON:API
    I want to delete a sheet workflow log via JSON:API

    Background:
        Given I am logged into StudIP
        And a contract saved as "contract_id_for_workflowlog" with an active sheet saved as "sheet_id_for_workflowlog" exist

    Scenario: Successfully create a workflow log
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "user-id": "7e81ec247c151c02ffd479511e24cc03",
                    "sheet-id": {sheet_id_for_workflowlog},
                    "action": "submit",
                    "comment": "This is a test workflow log."
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-workflowlogs"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "workflowlog_id"
        And the JSON response property "data.type" should equal or greater than "timesheet-workflow-logs"

    Scenario: Successfully fetch workflow logs
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-workflowlogs"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a workflow log
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-workflowlogs/{workflowlog_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-workflow-logs"

    Scenario: Successfully update a workflow log
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "action": "approve_final",
                    "comment": "This is an EDITED test workflow log."
                }
            }
        }
        """
        When I send a "PATCH" request to "/jsonapi.php/v1/timesheet-workflowlogs/{workflowlog_id}"
        Then the response status code should be 201
        And the JSON response property "data.type" should equal or greater than "timesheet-workflow-logs"

    Scenario: Successfully delete a workflow log
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-workflowlogs/{workflowlog_id}"
        Then the response status code should be 204

    # Sheet-workflowlogs
    Scenario: Successfully create a sheet workflow log
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "user-id": "7e81ec247c151c02ffd479511e24cc03",
                    "sheet-id": {sheet_id_for_workflowlog},
                    "action": "submit",
                    "comment": "This is a second test workflow log."
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_workflowlog}/workflowlogs"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "sheet_workflowlog_id"
        And the JSON response property "data.type" should equal or greater than "timesheet-workflow-logs"

    Scenario: Successfully fetch sheet workflowlogs
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_workflowlog}/workflowlogs"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a sheet workflow log
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_workflowlog}/workflowlogs/{sheet_workflowlog_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-workflow-logs"

    Scenario: Successfully update a sheet workflow log
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "action": "approve_final",
                    "comment": "This is an EDITED second test workflow log."
                }
            }
        }
        """
        When I send a "PATCH" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_workflowlog}/workflowlogs/{sheet_workflowlog_id}"
        Then the response status code should be 201
        And the JSON response property "data.type" should equal or greater than "timesheet-workflow-logs"

    Scenario: Successfully delete a sheet workflow log
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_workflowlog}/workflowlogs/{sheet_workflowlog_id}"
        Then the response status code should be 204



