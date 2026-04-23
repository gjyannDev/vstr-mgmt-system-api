<?php

namespace App\Features\Visits\Services;

use App\Features\Visits\Repository\VisitResponseRepository;
use App\Services\ImageKitService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VisitResponseService
{
    use ApiResponse;

    public function __construct(private VisitResponseRepository $repo, private ImageKitService $imageKit) {}

    public function store(Request $request): JsonResponse
    {
        $identity = $request->user();

        $data = $request->all();

        // Expect kiosk identity for kiosk flows
        $tenantId = $identity->tenant_id ?? null;
        $locationId = $identity->location_id ?? null;

        if (! $tenantId || ! $locationId) {
            return $this->errorResponse('Invalid kiosk identity.', null, 403);
        }

        $isFinal = isset($data['is_final']) ? (bool) $data['is_final'] : false;

        try {
            $result = DB::transaction(function () use ($data, $tenantId, $locationId, $isFinal) {
                // ensure visitor
                $visitorId = $this->resolveVisitor($data['visitor'] ?? [], $tenantId, $locationId);

                // handle image upload if base64 provided
                if (empty($data['image_url']) && ! empty($data['image_base64'])) {
                    $uploaded = $this->imageKit->uploadBase64($data['image_base64']);
                    if ($uploaded) {
                        $this->repo->updateVisitor($visitorId, ['photo_url' => $uploaded]);
                    }
                }

                $sessionKey = $data['session_key'] ?? (string) Str::uuid();

                $visitPayload = [
                    'tenant_id' => $tenantId,
                    'location_id' => $locationId,
                    'visitor_id' => $visitorId,
                    'host_id' => $data['host_id'] ?? null,
                    'visit_type_id' => $data['visit_type_id'],
                    'purpose' => $data['purpose'] ?? null,
                    'status' => $isFinal ? 'checked_in' : 'pending',
                    'check_in_at' => $isFinal ? now() : null,
                    'session_key' => $sessionKey,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // create or update visit
                $existing = null;
                if (! empty($data['session_key'])) {
                    $existing = $this->repo->findBySessionKey($data['session_key']);
                }

                if ($existing) {
                    $this->repo->updateVisit($existing->id, $visitPayload);
                    $visitId = $existing->id;
                } else {
                    $visitId = $this->repo->createVisit($visitPayload);
                }

                // upsert form responses
                if (! empty($data['form_data']) && is_array($data['form_data'])) {
                    $this->repo->upsertResponses($visitId, $locationId, $data['form_data']);
                }

                // final submit: generate QR code
                if ($isFinal) {
                    $qr = $this->generateUniqueQrCode();
                    $this->repo->updateVisit($visitId, ['qr_code' => $qr]);
                }

                return $this->repo->findVisitAggregate($visitId);
            });

            if ($result === null) {
                return $this->errorResponse('Failed to save visit response.', null, 500);
            }

            $status = ($isFinal ? 201 : 200);

            $payload = [
                'visit' => $result['visit'],
                'visitor' => $result['visitor'],
                'form_data' => $result['form_data'],
            ];

            if (! empty($result['visit']->qr_code)) {
                $payload['qr_code'] = $result['visit']->qr_code;
                $payload['qr_payload'] = $this->buildQrPayload($result['visit']->qr_code);
            }

            return $this->successResponse($isFinal ? 'Visit created successfully.' : 'Visit saved successfully.', $payload, $status);
        } catch (\Throwable $e) {
            return $this->errorResponse('An unexpected error occurred.', $e->getMessage(), 500);
        }
    }

    private function resolveVisitor(array $visitor, string $tenantId, string $locationId): string
    {
        // if visitor id provided and exists, return it
        if (! empty($visitor['id'])) {
            return $visitor['id'];
        }

        $email = $visitor['email'] ?? null;
        $phone = $visitor['phone'] ?? null;

        // If no identifying info provided, create a new visitor record.
        if (empty($email) && empty($phone)) {
            $create = array_merge($visitor, ['tenant_id' => $tenantId, 'location_id' => $locationId]);
            return $this->repo->createVisitor($create);
        }

        $found = $this->repo->findVisitorByEmailPhone($tenantId, $locationId, $email, $phone);

        if ($found) {
            // optionally update profile
            $updates = array_filter([
                'full_name' => $visitor['full_name'] ?? null,
                'company' => $visitor['company'] ?? null,
                'photo_url' => $visitor['photo_url'] ?? null,
            ]);

            if (! empty($updates)) {
                $this->repo->updateVisitor($found->id, $updates);
            }

            return $found->id;
        }

        // create
        $create = array_merge($visitor, ['tenant_id' => $tenantId, 'location_id' => $locationId]);

        return $this->repo->createVisitor($create);
    }

    private function generateUniqueQrCode(): string
    {
        do {
            $token = Str::random(8);
            $exists = DB::table('visits')->where('qr_code', $token)->exists();
        } while ($exists);

        return $token;
    }

    private function buildQrPayload(string $qrCode): string
    {
        $host = config('app.url') ?? env('APP_URL');

        return rtrim($host, '/') . '/v/' . $qrCode;
    }

    public function showById(string $visitId): JsonResponse
    {
        $agg = $this->repo->findVisitAggregate($visitId);

        if (! $agg) {
            return $this->errorResponse('Visit not found.', null, 404);
        }

        $payload = [
            'visit' => $agg['visit'],
            'visitor' => $agg['visitor'],
            'form_data' => $agg['form_data'],
        ];

        if (! empty($agg['visit']->qr_code)) {
            $payload['qr_payload'] = $this->buildQrPayload($agg['visit']->qr_code);
        }

        return $this->successResponse('Visit fetched successfully.', $payload);
    }

    public function showByQr(string $qrCode): JsonResponse
    {
        $agg = $this->repo->findVisitByQr($qrCode);

        if (! $agg) {
            return $this->errorResponse('Visit not found for given QR code.', null, 404);
        }

        $payload = [
            'visit' => $agg['visit'],
            'visitor' => $agg['visitor'],
            'form_data' => $agg['form_data'],
            'qr_payload' => $this->buildQrPayload($qrCode),
        ];

        return $this->successResponse('Visit fetched successfully.', $payload);
    }
}
