<?php
/**
 * Filter Trait for Contracts
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.1.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */
namespace StudipTimesheet\JsonApi\Routes\Contract;

use JsonApi\Errors\BadRequestException;

trait FilterTrait
{
    private function validateFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        if (array_key_exists('search', $filtering)) {
            $search = trim((string) $filtering['search']);
            if (mb_strlen($search) > 100) {
                throw new BadRequestException('Search term is too long.');
            }
        }

        if (array_key_exists('type', $filtering)) {
            $type = (string) $filtering['type'];
            $allowedTypes = ['new', 'extension', 'rehire'];
            if (!in_array($type, $allowedTypes, true)) {
                throw new BadRequestException('Invalid contract type filter value.');
            }
        }

        if (array_key_exists('employee_id', $filtering)) {
            $employeeId = trim((string) $filtering['employee_id']);
            if (strlen($employeeId) !== 32) {
                throw new BadRequestException('Invalid employee ID format.');
            }
        }

        if (array_key_exists('institute_id', $filtering)) {
            $instituteId = trim((string) $filtering['institute_id']);
            if (strlen($instituteId) !== 32) {
                throw new BadRequestException('Invalid institute ID format.');
            }
        }

        if (array_key_exists('start_date', $filtering)) {
            $startDate = $filtering['start_date'];
            if (!ctype_digit((string) $startDate)) {
                throw new BadRequestException('Start date must be a valid timestamp.');
            }
        }

        if (array_key_exists('end_date', $filtering)) {
            $endDate = $filtering['end_date'];
            if (!ctype_digit((string) $endDate)) {
                throw new BadRequestException('End date must be a valid timestamp.');
            }
        }

        if (array_key_exists('hours_per_month', $filtering)) {
            $hours = $filtering['hours_per_month'];
            if (!ctype_digit((string) $hours)) {
                throw new BadRequestException('Hours per month must be a valid number.');
            }
        }

        if (array_key_exists('predecessor_id', $filtering)) {
            $predecessorId = $filtering['predecessor_id'];
            if (!ctype_digit((string) $predecessorId)) {
                throw new BadRequestException('Predecessor ID must be a valid number.');
            }
        }
    }

    private function getFilters(): array
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];
        $filters = [];

        if (array_key_exists('search', $filtering)) {
            $filters['search'] = trim((string) $filtering['search']);
        }

        if (array_key_exists('type', $filtering)) {
            $filters['type'] = (string) $filtering['type'];
        }

        if (array_key_exists('employee_id', $filtering)) {
            $filters['employee_id'] = trim((string) $filtering['employee_id']);
        }

        if (array_key_exists('institute_id', $filtering)) {
            $filters['institute_id'] = trim((string) $filtering['institute_id']);
        }

        if (array_key_exists('start_date', $filtering)) {
            $filters['start_date'] = (int) $filtering['start_date'];
        }

        if (array_key_exists('end_date', $filtering)) {
            $filters['end_date'] = (int) $filtering['end_date'];
        }

        if (array_key_exists('hours_per_month', $filtering)) {
            $filters['hours_per_month'] = (int) $filtering['hours_per_month'];
        }

        if (array_key_exists('predecessor_id', $filtering)) {
            $filters['predecessor_id'] = (int) $filtering['predecessor_id'];
        }

        return $filters;
    }

    private function buildWhereClause(array $filters): array
    {
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = 'label LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['type'])) {
            $where[] = 'type = ?';
            $params[] = $filters['type'];
        }

        if (!empty($filters['employee_id'])) {
            $where[] = 'employee_id = ?';
            $params[] = $filters['employee_id'];
        }

        if (!empty($filters['institute_id'])) {
            $where[] = 'institute_id = ?';
            $params[] = $filters['institute_id'];
        }

        if (isset($filters['start_date'])) {
            $where[] = 'start_date >= ?';
            $params[] = $filters['start_date'];
        }

        if (isset($filters['end_date'])) {
            $where[] = 'end_date <= ?';
            $params[] = $filters['end_date'];
        }

        if (isset($filters['hours_per_month'])) {
            $where[] = 'hours_per_month = ?';
            $params[] = $filters['hours_per_month'];
        }

        if (isset($filters['predecessor_id'])) {
            $where[] = 'predecessor_id = ?';
            $params[] = $filters['predecessor_id'];
        }

        return [implode(' AND ', $where), $params];
    }
}