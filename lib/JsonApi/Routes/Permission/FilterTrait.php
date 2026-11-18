<?php

namespace StudipTimesheet\JsonApi\Routes\Permission;

use JsonApi\Errors\BadRequestException;
use StudipTimesheet\Models\Permission;

trait FilterTrait
{
    /**
     * Get allowed filter parameters.
     *
     * @return string[]
     */
    protected function getAllowedFilterParameters(): array
    {
        return ['user_id', 'institute_id'];
    }

    /**
     * Validate filter parameters from the request.
     *
     * @param array $filter
     * @return void
     */
    protected function validateFilter(array $filter): void
    {
        $allowed = $this->getAllowedFilterParameters();
        foreach (array_keys($filter) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new BadRequestException("Filter parameter '{$key}' is not allowed.");
            }
        }
    }

    /**
     * Apply filters to fetch permissions.
     *
     * @param array $filter
     * @return Permission[]
     */
    protected function getFilter(array $filter): array
    {
        $this->validateFilter($filter);

        $query = Permission::get();

        if (isset($filter['user_id'])) {
            $query->where('user_id', $filter['user_id']);
        }

        if (isset($filter['institute_id'])) {
            $query->where('institute_id', $filter['institute_id']);
        }

        return $query->toArray();
    }
}