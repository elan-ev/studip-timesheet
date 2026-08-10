<?php
/**
 * Routes Trait
 *
 * Registers the routes for StudipTimesheet plugin
 *
 * @package   StudipTimesheet\JsonApi
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi;

use Slim\Routing\RouteCollectorProxy;

trait Routes
{
    public function registerAuthenticatedRoutes(RouteCollectorProxy $group)
    {
        // Contracts.
        $group->get('/timesheet-contracts', \StudipTimesheet\JsonApi\Routes\Contract\Index::class);
        $group->get('/timesheet-contracts/{id}', \StudipTimesheet\JsonApi\Routes\Contract\Show::class);
        $group->post('/timesheet-contracts', \StudipTimesheet\JsonApi\Routes\Contract\Create::class);
        $group->patch('/timesheet-contracts/{id}', \StudipTimesheet\JsonApi\Routes\Contract\Update::class);
        $group->delete('/timesheet-contracts/{id}', \StudipTimesheet\JsonApi\Routes\Contract\Delete::class);

        // Permissions.
        $group->get('/timesheet-permissions', \StudipTimesheet\JsonApi\Routes\Permission\Index::class);
        $group->get('/timesheet-permissions/{id}', \StudipTimesheet\JsonApi\Routes\Permission\Show::class);
        $group->post('/timesheet-permissions', \StudipTimesheet\JsonApi\Routes\Permission\Create::class);
        $group->patch('/timesheet-permissions/{id}', \StudipTimesheet\JsonApi\Routes\Permission\Update::class);
        $group->delete('/timesheet-permissions/{id}', \StudipTimesheet\JsonApi\Routes\Permission\Delete::class);

        // Sheets.
        $group->get('/timesheet-sheets', \StudipTimesheet\JsonApi\Routes\Sheet\Index::class);
        $group->get('/timesheet-sheets/{id}', \StudipTimesheet\JsonApi\Routes\Sheet\Show::class);
        $group->post('/timesheet-sheets', \StudipTimesheet\JsonApi\Routes\Sheet\Create::class);
        $group->patch('/timesheet-sheets/{id}', \StudipTimesheet\JsonApi\Routes\Sheet\Update::class);
        $group->delete('/timesheet-sheets/{id}', \StudipTimesheet\JsonApi\Routes\Sheet\Delete::class);

        // Contract-Sheets.
        $group->get('/timesheet-contracts/{id}/sheets', \StudipTimesheet\JsonApi\Routes\Sheet\ContractSheetsIndex::class);
        $group->get('/timesheet-contracts/{contract_id}/sheets/{id}', \StudipTimesheet\JsonApi\Routes\Sheet\ContractSheetShow::class);
        $group->post('/timesheet-contracts/{id}/sheets', \StudipTimesheet\JsonApi\Routes\Sheet\ContractSheetCreate::class);
        $group->patch('/timesheet-contracts/{contract_id}/sheets/{id}', \StudipTimesheet\JsonApi\Routes\Sheet\ContractSheetUpdate::class);
        $group->delete('/timesheet-contracts/{contract_id}/sheets/{id}', \StudipTimesheet\JsonApi\Routes\Sheet\ContractSheetDelete::class);

        // Supervisors.
        $group->get('/timesheet-supervisors', \StudipTimesheet\JsonApi\Routes\Supervisor\Index::class);
        $group->get('/timesheet-supervisors/{id}', \StudipTimesheet\JsonApi\Routes\Supervisor\Show::class);
        $group->post('/timesheet-supervisors', \StudipTimesheet\JsonApi\Routes\Supervisor\Create::class);
        $group->delete('/timesheet-supervisors/{id}', \StudipTimesheet\JsonApi\Routes\Supervisor\Delete::class);

        // In the contest of Supervisor, updating it does not make any sense!
        // $group->patch('/timesheet-supervisors/{id}', \StudipTimesheet\JsonApi\Routes\Supervisor\Update::class);
        
        // Contract-Supervisors.
        $group->get('/timesheet-contracts/{id}/supervisors', \StudipTimesheet\JsonApi\Routes\Supervisor\ContractSupervisorIndex::class);
        $group->get('/timesheet-contracts/{contract_id}/supervisors/{id}', \StudipTimesheet\JsonApi\Routes\Supervisor\ContractSupervisorShow::class);
        $group->post('/timesheet-contracts/{id}/supervisors', \StudipTimesheet\JsonApi\Routes\Supervisor\ContractSupervisorCreate::class);
        $group->delete('/timesheet-contracts/{contract_id}/supervisors/{id}', \StudipTimesheet\JsonApi\Routes\Supervisor\ContractSupervisorDelete::class);

        // In the contest of Supervisor, updating it does not make any sense!
        // $group->patch('/timesheet-contracts/{contract_id}/supervisors/{id}', \StudipTimesheet\JsonApi\Routes\Supervisor\ContractSupervisorUpdate::class);

        // Records.
        $group->get('/timesheet-records', \StudipTimesheet\JsonApi\Routes\Record\Index::class);
        $group->get('/timesheet-records/{id}', \StudipTimesheet\JsonApi\Routes\Record\Show::class);
        $group->post('/timesheet-records', \StudipTimesheet\JsonApi\Routes\Record\Create::class);
        $group->patch('/timesheet-records/{id}', \StudipTimesheet\JsonApi\Routes\Record\Update::class);
        $group->delete('/timesheet-records/{id}', \StudipTimesheet\JsonApi\Routes\Record\Delete::class);

        // Sheet-Records
        $group->get('/timesheet-sheets/{id}/records', \StudipTimesheet\JsonApi\Routes\Record\SheetRecordIndex::class);
        $group->get('/timesheet-sheets/{sheet_id}/records/{id}', \StudipTimesheet\JsonApi\Routes\Record\SheetRecordShow::class);
        $group->post('/timesheet-sheets/{id}/records', \StudipTimesheet\JsonApi\Routes\Record\SheetRecordCreate::class);
        $group->patch('/timesheet-sheets/{sheet_id}/records/{id}', \StudipTimesheet\JsonApi\Routes\Record\SheetRecordUpdate::class);
        $group->delete('/timesheet-sheets/{sheet_id}/records/{id}', \StudipTimesheet\JsonApi\Routes\Record\SheetRecordDelete::class);

        // Workflow Logs
        $group->get('/timesheet-workflowlogs', \StudipTimesheet\JsonApi\Routes\WorkflowLog\Index::class);
        $group->get('/timesheet-workflowlogs/{id}', \StudipTimesheet\JsonApi\Routes\WorkflowLog\Show::class);
        $group->post('/timesheet-workflowlogs', \StudipTimesheet\JsonApi\Routes\WorkflowLog\Create::class);
        $group->patch('/timesheet-workflowlogs/{id}', \StudipTimesheet\JsonApi\Routes\WorkflowLog\Update::class);
        $group->delete('/timesheet-workflowlogs/{id}', \StudipTimesheet\JsonApi\Routes\WorkflowLog\Delete::class);

        // Sheet-Workflow Logs
        $group->get('/timesheet-sheets/{id}/workflowlogs', \StudipTimesheet\JsonApi\Routes\WorkflowLog\SheetWorkflowLogIndex::class);
        $group->get('/timesheet-sheets/{sheet_id}/workflowlogs/{id}', \StudipTimesheet\JsonApi\Routes\WorkflowLog\SheetWorkflowLogShow::class);
        $group->post('/timesheet-sheets/{id}/workflowlogs', \StudipTimesheet\JsonApi\Routes\WorkflowLog\SheetWorkflowLogCreate::class);
        $group->patch('/timesheet-sheets/{sheet_id}/workflowlogs/{id}', \StudipTimesheet\JsonApi\Routes\WorkflowLog\SheetWorkflowLogUpdate::class);
        $group->delete('/timesheet-sheets/{sheet_id}/workflowlogs/{id}', \StudipTimesheet\JsonApi\Routes\WorkflowLog\SheetWorkflowLogDelete::class);
    }

    public function registerUnauthenticatedRoutes(RouteCollectorProxy $group)
    {
    }
}
