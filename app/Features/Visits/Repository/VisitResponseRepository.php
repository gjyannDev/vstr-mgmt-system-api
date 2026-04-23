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

        // Accept either map of form_field_id => value, or array of objects with form_field_id/value
        if (array_values($formData) === $formData) {
            // indexed array
            foreach ($formData as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $fieldId = $entry['form_field_id'] ?? null;
                $value = $entry['value'] ?? null;

                if (! $fieldId) {
                    continue;
                }

                DB::table('visit_responses')->updateOrInsert(
                    ['visit_id' => $visitId, 'form_field_id' => $fieldId],
                    ['location_id' => $locationId, 'value' => (string) $value, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        } else {
            foreach ($formData as $fieldId => $value) {
                DB::table('visit_responses')->updateOrInsert(
                    ['visit_id' => $visitId, 'form_field_id' => $fieldId],
                    ['location_id' => $locationId, 'value' => (string) $value, 'updated_at' => $now, 'created_at' => $now]
                );
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
