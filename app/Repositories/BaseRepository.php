<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class BaseRepository
{
    protected Model $model;

    protected int $maxPageSize = 100;

    /**
     * @var array<int, string>
     */
    protected array $allowedOperators = ['=', '!=', '<>', '>', '>=', '<', '<=', 'like', 'not like'];

    public function all()
    {
        return $this->model->all();
    }

    public function find(string $id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);

        return $record;
    }

    public function delete(string $id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * Generic paginated finder for table-like data.
     *
     * Supported params:
     * - filter: array column => value|[operator, value]|[...values]
     * - pageIndex: int (0-based)
     * - pageSize: int
     * - sort: array column => asc|desc (default: created_at desc)
     * - with: array|string relationships for eager loading
     * - select: array|string selected columns
     *
     * @param  array<string, mixed>  $params
     * @return array{rows: Collection<int, Model>, totalCount: int}
     */
    public function findPaginatedData(array $params = []): array
    {
        $filter = $params['filter'] ?? [];
        $sort = $params['sort'] ?? ['created_at' => 'desc'];
        $with = $this->normalizeToArray($params['with'] ?? []);
        $select = $this->normalizeToArray($params['select'] ?? ['*']);

        $pageIndex = max(0, (int) ($params['pageIndex'] ?? 0));
        $pageSize = (int) ($params['pageSize'] ?? 10);
        $pageSize = min(max(1, $pageSize), $this->maxPageSize);

        $query = $this->model->newQuery();
        $this->applyFilters($query, is_array($filter) ? $filter : []);

        $totalCount = (clone $query)->count();

        if ($with !== []) {
            $query->with($with);
        }

        if ($select !== [] && $select !== ['*']) {
            $query->select($select);
        }

        $this->applySort($query, $sort);

        $rows = $query
            ->skip($pageIndex * $pageSize)
            ->take($pageSize)
            ->get();

        return [
            'rows' => $rows,
            'totalCount' => $totalCount,
        ];
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    protected function applyFilters(Builder $query, array $filter): void
    {
        foreach ($filter as $column => $value) {
            if (! is_string($column) || $column === '') {
                continue;
            }

            if (is_array($value)) {
                if (Arr::isList($value)) {
                    if (
                        count($value) === 2
                        && is_string($value[0])
                        && in_array(strtolower($value[0]), $this->allowedOperators, true)
                    ) {
                        $query->where($column, $value[0], $value[1]);
                    } elseif ($value !== []) {
                        $query->whereIn($column, $value);
                    }

                    continue;
                }

                if (array_key_exists('in', $value) && is_array($value['in'])) {
                    $query->whereIn($column, $value['in']);
                    continue;
                }

                if (array_key_exists('operator', $value) && array_key_exists('value', $value)) {
                    $operator = strtolower((string) $value['operator']);

                    if (in_array($operator, $this->allowedOperators, true)) {
                        $query->where($column, $operator, $value['value']);
                    }

                    continue;
                }
            }

            if ($value === null) {
                $query->whereNull($column);
                continue;
            }

            $query->where($column, $value);
        }
    }

    /**
     * @param  mixed  $sort
     */
    protected function applySort(Builder $query, mixed $sort): void
    {
        $hasSort = false;

        if (is_array($sort) && $sort !== []) {
            if (Arr::isList($sort)) {
                foreach ($sort as $entry) {
                    if (! is_string($entry) || $entry === '') {
                        continue;
                    }

                    $direction = str_starts_with($entry, '-') ? 'desc' : 'asc';
                    $column = ltrim($entry, '-');

                    if ($column === '') {
                        continue;
                    }

                    $query->orderBy($column, $direction);
                    $hasSort = true;
                }
            } else {
                foreach ($sort as $column => $direction) {
                    if (! is_string($column) || $column === '') {
                        continue;
                    }

                    $dir = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';
                    $query->orderBy($column, $dir);
                    $hasSort = true;
                }
            }
        }

        if (! $hasSort) {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * @param  mixed  $value
     * @return array<int, mixed>
     */
    protected function normalizeToArray(mixed $value): array
    {
        if (is_string($value)) {
            $parts = array_map('trim', explode(',', $value));

            return array_values(array_filter($parts, static fn(string $part): bool => $part !== ''));
        }

        if (! is_array($value)) {
            return [];
        }

        return $value;
    }
}
