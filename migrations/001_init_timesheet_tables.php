<?php
/**
 * InitTimesheetTables
 *
 * Migration step to creates StudipTimesheet DB Tables.
 *
 * @package   StudipTimesheet
 * @since     0.0.1
 * @author    Farbod Zamnai <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 */
final class InitTimesheetTables extends Migration
{
    public function description()
    {
        return 'Creates database tables for StudipTimesheet plugin.';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE IF NOT EXISTS `timesheet_permissions` (
                `id`                            INT(11) NOT NULL AUTO_INCREMENT,
                `user_id`                       CHAR(32) NOT NULL,
                `institute_id`                  CHAR(32),
                `role`                          ENUM('superadmin','admin','supervisor') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `mkdate`                        INT(11) UNSIGNED NOT NULL,
                `chdate`                        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `timesheet_contracts` (
                `id`                            INT(11) NOT NULL AUTO_INCREMENT,
                `employee_id`                   CHAR(32) NOT NULL,
                `institute_id`                  CHAR(32) NOT NULL,
                `type`                          ENUM('new','extension','rehire') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `predecessor_id`                INT(11) DEFAULT NULL,
                `label`                         MEDIUMTEXT DEFAULT NULL,
                `hours_per_month`               INT(11) NOT NULL,
                `start_date`                    INT(11) NOT NULL,
                `end_date`                      INT(11) NOT NULL,
                `half_hours_first_month`        TINYINT(1) NOT NULL DEFAULT 0,
                `half_hours_last_month`         TINYINT(1) NOT NULL DEFAULT 0,
                `mkdate`                        INT(11) UNSIGNED NOT NULL,
                `chdate`                        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `timesheet_contract_supervisors` (
                `contract_id`                   INT(11) NOT NULL,
                `user_id`                       CHAR(32) NOT NULL,
                `mkdate`                        INT(11) UNSIGNED NOT NULL,
                `chdate`                        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`contract_id`, `user_id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `timesheet_sheets` (
                `id`                            INT(11) NOT NULL AUTO_INCREMENT,
                `contract_id`                   INT(11) NOT NULL,
                `year`                          INT(11) NOT NULL,
                `month`                         INT(11) NOT NULL,
                `status`                        ENUM('open','submitted','approved_confirm','approved_final','archived') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `is_suspended`                  TINYINT(1) NOT NULL DEFAULT 0,
                `workflow_config`               MEDIUMTEXT NOT NULL,
                `deleted_at`                    INT(11) DEFAULT NULL,
                `deleted_by`                    CHAR(32) DEFAULT NULL,
                `frozen_hours_per_month`        INT(11) NOT NULL,
                `mkdate`                        INT(11) UNSIGNED NOT NULL,
                `chdate`                        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_active_sheet` (`contract_id`,`year`,`month`,`deleted_at`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `timesheet_records` (
                `id`                            INT(11) NOT NULL AUTO_INCREMENT,
                `sheet_id`                      INT(11) NOT NULL,
                `date`                          INT(11) NOT NULL,
                `start_time`                    INT(11) NOT NULL,
                `end_time`                      INT(11) NOT NULL,
                `break_start`                   INT(11) NOT NULL,
                `break_duration`                INT(11) NOT NULL,
                `absence_type`                  ENUM('work','sick','vacation','holiday','maternity') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                `comment`                       MEDIUMTEXT DEFAULT NULL,
                `mkdate`                        INT(11) UNSIGNED NOT NULL,
                `chdate`                        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `timesheet_workflow_logs` (
                `id`                            INT(11) NOT NULL AUTO_INCREMENT,
                `sheet_id`                      INT(11) NOT NULL,
                `user_id`                       CHAR(32) NOT NULL,
                `action`                        ENUM('submit','approved_confirm','approve_final','reject','soft_delete','unsuspend','system_override','manual_unlock') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `performed_at`                  INT(11) NOT NULL,
                `comment`                       MEDIUMTEXT DEFAULT NULL,
                `mkdate`                        INT(11) UNSIGNED NOT NULL,
                `chdate`                        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`)
            )"
        );
    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS `timesheet_permissions`");
        $db->exec("DROP TABLE IF EXISTS `timesheet_contracts`");
        $db->exec("DROP TABLE IF EXISTS `timesheet_contract_supervisors`");
        $db->exec("DROP TABLE IF EXISTS `timesheet_sheets`");
        $db->exec("DROP TABLE IF EXISTS `timesheet_records`");
        $db->exec("DROP TABLE IF EXISTS `timesheet_workflow_logs`");
    }
}
