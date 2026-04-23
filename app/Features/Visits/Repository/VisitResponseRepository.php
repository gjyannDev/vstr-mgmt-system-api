<?php

namespace App\Features\Visits\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VisitResponseRepository
{
    public function findBySessionKey(string $sessionKey): ?object
    {
        return DB::table('visits')->where('session_key', $sessionKey)->first();
    }

    public function createVisit(array $data): string
    {
        $visitId = (string) Str::uuid();
        $data['id'] = $visitId;

        DB::table('visits')->insert($data);

        return $visitId;
    }

    public function updateVisit(string $visitId, array $data): bool
    {
        return (bool) DB::table('visits')->where('id', $visitId)->update($data);
    }

    public function findVisitById(string $visitId): ?object
    {
        return DB::table('visits')->where('id', $visitId)->first();
    }

    public function upsertResponses(string $visitId, string $locationId, array $formData): void
    {
        $now = now();

        // Load visit to help resolve form fields by visit_type when names are provided
        $visit = DB::table('visits')->where('id', $visitId)->first();
        $visitTypeId = $visit->visit_type_id ?? null;

        // simple cache for name -> id lookups
        $fieldCache = [];

        $resolveFieldId = function ($raw) use ($locationId, $visitTypeId, &$fieldCache) {
            if (! $raw) {
                return null;
            }

            // already cached
            if (isset($fieldCache[$raw])) {
                return $fieldCache[$raw];
            }

            // try treat as id first
            $byId = DB::table('form_fields')->where('id', $raw)->first();
            if ($byId) {
                $fieldCache[$raw] = $byId->id;
                return $byId->id;
            }

            // fallback: lookup by name scoped to location and visit type
            $q = DB::table('form_fields')->where('name', $raw)->where('location_id', $locationId);
            if ($visitTypeId) {
                $q->where('visit_type_id', $visitTypeId);
            }

            $byName = $q->first();
            if ($byName) {
                $fieldCache[$raw] = $byName->id;
                return $byName->id;
            }

            // not found
            $fieldCache[$raw] = null;
            return null;
        };

        // Accept either map of form_field_id/name => value, or array of objects with form_field_id/value
        if (array_values($formData) === $formData) {
            // indexed array
            foreach ($formData as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                // entry may provide either 'form_field_id' (id or name) or 'name'
                $rawField = $entry['form_field_id'] ?? ($entry['name'] ?? null);
                $value = $entry['value'] ?? null;

                $fieldId = $resolveFieldId($rawField);

                if (! $fieldId) {
                    continue;
                }

                $existing = DB::table('visit_responses')
                    ->where('visit_id', $visitId)
                    ->where('form_field_id', $fieldId)
                    ->first();

                if ($existing) {
                    DB::table('visit_responses')
                        ->where('id', $existing->id)
                        ->update(['location_id' => $locationId, 'value' => (string) $value, 'updated_at' => $now]);
                } else {
                    DB::table('visit_responses')->insert([
                        'id' => (string) Str::uuid(),
                        'visit_id' => $visitId,
                        'form_field_id' => $fieldId,
                        'location_id' => $locationId,
                        'value' => (string) $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        } else {
            foreach ($formData as $rawField => $value) {
                $fieldId = $resolveFieldId($rawField);

                if (! $fieldId) {
                    continue;
                }

                $existing = DB::table('visit_responses')
                    ->where('visit_id', $visitId)
                    ->where('form_field_id', $fieldId)
                    ->first();

                if ($existing) {
                    DB::table('visit_responses')
                        ->where('id', $existing->id)
                        ->update(['location_id' => $locationId, 'value' => (string) $value, 'updated_at' => $now]);
                } else {
                    DB::table('visit_responses')->insert([
                        'id' => (string) Str::uuid(),
                        'visit_id' => $visitId,
                        'form_field_id' => $fieldId,
                        'location_id' => $locationId,
                        'value' => (string) $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function createVisitor(array $data): string
    {
        $visitorId = (string) Str::uuid();
        $now = now();

        $row = array_merge(
            [
                'id' => $visitorId,
                'tenant_id' => $data['tenant_id'] ?? null,
                'location_id' => $data['location_id'] ?? null,
                'full_name' => $data['full_name'] ?? 'Guest',
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'photo_url' => $data['photo_url'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $data
        );

        DB::table('visitors')->insert($row);

        return $visitorId;
    }

    public function findVisitorByEmailPhone(string $tenantId, string $locationId, ?string $email, ?string $phone): ?object
    {
        $query = DB::table('visitors')->where('tenant_id', $tenantId)->where('location_id', $locationId);

        if ($email) {
            $query->where('email', $email);
        }

        if ($phone) {
            $query->orWhere('phone', $phone);
        }

        return $query->first();
    }

    public function updateVisitor(string $visitorId, array $data): bool
    {
        return (bool) DB::table('visitors')->where('id', $visitorId)->update($data);
    }

    public function findVisitAggregate(string $visitId): ?array
    {
        $visit = $this->findVisitById($visitId);

        if (! $visit) {
            return null;
        }

        $visitor = DB::table('visitors')->where('id', $visit->visitor_id)->first();

        $responses = DB::table('visit_responses')
            ->where('visit_responses.visit_id', $visitId)
            ->join('form_fields', 'visit_responses.form_field_id', '=', 'form_fields.id')
            ->select('form_fields.id as form_field_id', 'form_fields.name as form_field_name', 'visit_responses.value')
            ->get();

        $formData = [];

        foreach ($responses as $r) {
            $key = $r->form_field_name ?? $r->form_field_id;
            $formData[$key] = $r->value;
        }

        return [
            'visit' => $visit,
            'visitor' => $visitor,
            'form_data' => $formData,
        ];
    }

    public function findVisitByQr(string $qrCode): ?array
    {
        $visit = DB::table('visits')->where('qr_code', $qrCode)->first();

        if (! $visit) {
            return null;
        }

        return $this->findVisitAggregate($visit->id);
    }
}
