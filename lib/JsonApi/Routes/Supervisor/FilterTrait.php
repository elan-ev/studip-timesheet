<?php
/**
 * Filter Trait for Supervisors
 *
 * @package   StudipTimesheet\JsonApi\Routes\Supervisor
 * @since     0.1.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */
namespace StudipTimesheet\JsonApi\Routes\Supervisor;

use JsonApi\Errors\BadRequestException;

trait FilterTrait
{
    private function validateFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        if (array_key_exists('user_id', $filtering)) {
            $userId = trim((string) $filtering['user_id']);
            if (strlen($userId) !== 32) {
                throw new BadRequestException('Invalid user ID format.');
            }
        }

        if (array_key_exists('contract_id', $filtering)) {
            $contractId = $filtering['contract_id'];
            if (!ctype_digit((string) $contractId)) {
                throw new BadRequestException('Contract ID must be a valid number.');
            }
        }
    }

    private function getFilters(): array
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];
        $filters = [];

        if (array_key_exists('user_id', $filtering)) {
            $filters['user_id'] = trim((string) $filtering['user_id']);
        }

        if (array_key_exists('contract_id', $filtering)) {
            $filters['contract_id'] = (int) $filtering['contract_id'];
        }

        return $filters;
    }

    private function buildWhereClause(array $filters): array
    {
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['contract_id'])) {
            $where[] = 'contract_id = ?';
            $params[] = $filters['contract_id'];
        }

        return [implode(' AND ', $where), $params];
    }
}