Feature: timesheet Records API
    In order to manage timesheet records
    As an authenticated StudIP user
    I want to fetch records via JSON:API
    I want to fetch a record via JSON:API
    I want to create a record via JSON:API
    I want to update a record via JSON:API
    I want to delete a record via JSON:API

    I want to fetch sheet records via JSON:API
    I want to fetch a sheet record via JSON:API
    I want to create a sheet record via JSON:API
    I want to update a sheet record via JSON:API
    I want to delete a sheet record via JSON:API

    Background:
        Given I am logged into StudIP
        And a contract saved as "contract_id_for_record" with an active sheet saved as "sheet_id_for_record" exist

    Scenario: Successfully create a record
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "sheet-id": {sheet_id_for_record},
                    "date": "24-07-2026",
                    "start-time": "08:00:00",
                    "end-time": "16:30:00",
                    "break-duration": 30,
                    "absence-type": "work",
                    "comment": "This is a test record."
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-records"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "record_id"
        And the JSON response property "data.type" should equal or greater than "timesheet-records"

    Scenario: Successfully fetch records
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-records"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a record
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-records/{record_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-records"

    Scenario: Successfully update a record
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "date": "24-07-2026",
                    "start-time": "09:00:00",
                    "end-time": "16:30:00",
                    "break-duration": 30,
                    "absence-type": "maternity",
                    "comment": "This is an EDITED test record."
                }
            }
        }
        """
        When I send a "PATCH" request to "/jsonapi.php/v1/timesheet-records/{record_id}"
        Then the response status code should be 201
        And the JSON response property "data.type" should equal or greater than "timesheet-records"

    Scenario: Successfully delete a record
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-records/{record_id}"
        Then the response status code should be 204

    # Sheet-records
    Scenario: Successfully create a sheet record
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "date": "24-07-2026",
                    "start-time": "08:00:00",
                    "end-time": "16:30:00",
                    "break-duration": 30,
                    "absence-type": "work",
                    "comment": "This is a test record."
                }
            }
        }
        """
        When I send a "POST" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_record}/records"
        Then the response status code should be 201
        And I save the JSON property "data.id" as "sheet_record_id"
        And the JSON response property "data.type" should equal or greater than "timesheet-records"

    Scenario: Successfully fetch sheet records
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_record}/records"
        Then the response status code should be 200
        And the JSON response property "meta.page.total" should equal or greater than "1"

    Scenario: Successfully fetch a sheet record
        Given I send a "GET" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_record}/records/{sheet_record_id}"
        Then the response status code should be 200
        And the JSON response property "data.type" should equal or greater than "timesheet-records"

    Scenario: Successfully update a sheet record
        Given I set the request body to:
        """
        {
            "data": {
                "attributes": {
                    "date": "24-07-2026",
                    "start-time": "09:00:00",
                    "end-time": "16:30:00",
                    "break-duration": 40,
                    "absence-type": "maternity",
                    "comment": "This is an EDITED test record."
                }
            }
        }
        """
        When I send a "PATCH" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_record}/records/{sheet_record_id}"
        Then the response status code should be 201
        And the JSON response property "data.type" should equal or greater than "timesheet-records"

    Scenario: Successfully delete a sheet record
        Given I send a "DELETE" request to "/jsonapi.php/v1/timesheet-sheets/{sheet_id_for_record}/records/{sheet_record_id}"
        Then the response status code should be 204



