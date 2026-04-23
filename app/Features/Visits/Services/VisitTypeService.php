<?php

namespace App\Features\Visits\Services;

use App\Features\Visits\Repository\VisitTypeRepository;
use App\Features\Visits\Requests\StoreVisitTypeRequest;
use App\Features\Visits\Requests\UpdateVisitTypeRequest;
use App\Features\Visits\Requests\StoreFormFieldRequest;
use App\Models\FormField;
use App\Models\VisitType;
use App\Models\Location;
use App\Models\Kiosk;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitTypeService
{
    use ApiResponse;

    public function __construct(private VisitTypeRepository $visitTypeRepo) {}

    public function index(Request $request, Location $location): JsonResponse
    {
        $search = $request->query('search');
        $requiresApproval = $request->query('requires_approval');
        $active = $request->query('active');
        $createdDate = $request->query('createdDate');

        $params = [
            'search' => is_string($search) ? $search : null,
            'requires_approval' => $requiresApproval,
            'active' => $active,
            'createdDate' => is_string($createdDate) ? $createdDate : null,
            'filter' => [
                'tenant_id' => $request->user()?->tenant_id,
                'location_id' => $location->id,
            ],
            'pageIndex' => (int) $request->query('pageIndex', 0),
            'pageSize' => (int) $request->query('pageSize', 10),
            'sort' => $request->query('sort', ['created_at' => 'desc']),
            'with' => $request->query('with', ['formFields']),
            'select' => $request->query('select', ['*']),
        ];

        $rows = $this->visitTypeRepo->list($params);

        $user = $request->user();

        // If kiosk client, return a simplified payload (no admin-only fields)
        if ($user instanceof Kiosk) {
            $assigned = $user->visit_type_id ?? null;

            // helper to transform a VisitType model into kiosk-safe array
            $transform = function ($vt) {
                return [
                    'id' => $vt->id,
                    'name' => $vt->name,
                    'description' => $vt->description ?? null,
                    'is_camera_active' => (bool) ($vt->is_camera_active ?? false),
                    'form_fields' => $vt->formFields->map(function ($f) {
                        return [
                            'id' => $f->id,
                            'label' => $f->label,
                            'name' => $f->name,
                            'type' => $f->type,
                            'required' => (bool) ($f->required ?? false),
                            'options' => $f->options ?? [],
                            'placeholder' => $f->placeholder ?? null,
                        ];
                    })->values()->toArray(),
                ];
            };

            if (! empty($assigned)) {
                $vt = $rows['rows']->firstWhere('id', $assigned);
                if (! $vt) {
                    return $this->successResponse('Visit types fetched successfully.', ['rows' => [], 'totalCount' => 0]);
                }

                $t = $transform($vt);
                return $this->successResponse('Visit types fetched successfully.', ['rows' => [$t], 'totalCount' => 1]);
            }

            $transformed = $rows['rows']->map(function ($vt) use ($transform) {
                return $transform($vt);
            })->values()->toArray();

            return $this->successResponse('Visit types fetched successfully.', ['rows' => $transformed, 'totalCount' => count($transformed)]);
        }

        return $this->successResponse('Visit types fetched successfully.', $rows);
    }

    public function store(StoreVisitTypeRequest $request, Location $location): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['location_id'] = $location->id;
        $formFields = $data['form_fields'] ?? [];
        unset($data['form_fields']);

        $created = DB::transaction(function () use ($data, $formFields) {
            $visitType = $this->visitTypeRepo->createVisitType($data);

            if (!empty($formFields)) {
                foreach ($formFields as &$ff) {
                    $ff['tenant_id'] = $data['tenant_id'];
                    $ff['location_id'] = $data['location_id'];
                }

                $visitType->formFields()->createMany($formFields);
            }

            return $visitType->fresh()->load('formFields');
        });

        return $this->successResponse('Visit type created successfully.', ['visit_type' => $created], 201);
    }

    public function show(Request $request, Location $location, VisitType $visitType): JsonResponse
    {
        if ((string) $visitType->location_id !== (string) $location->id) {
            return $this->errorResponse('Visit type does not belong to this location.', null, 404);
        }

        $visitType->load('formFields');

        $user = $request->user();

        if ($user instanceof Kiosk) {
            $vt = $visitType;
            $transformed = [
                'id' => $vt->id,
                'name' => $vt->name,
                'description' => $vt->description ?? null,
                'is_camera_active' => (bool) ($vt->is_camera_active ?? false),
                'form_fields' => $vt->formFields->map(function ($f) {
                    return [
                        'id' => $f->id,
                        'label' => $f->label,
                        'name' => $f->name,
                        'type' => $f->type,
                        'required' => (bool) ($f->required ?? false),
                        'options' => $f->options ?? [],
                        'placeholder' => $f->placeholder ?? null,
                    ];
                })->values()->toArray(),
            ];

            return $this->successResponse('Visit type fetched successfully.', ['visit_type' => $transformed]);
        }

        return $this->successResponse('Visit type fetched successfully.', ['visit_type' => $visitType]);
    }

    public function update(UpdateVisitTypeRequest $request, Location $location, VisitType $visitType): JsonResponse
    {
        if ((string) $visitType->location_id !== (string) $location->id) {
            return $this->errorResponse('Visit type does not belong to this location.', null, 404);
        }

        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['location_id'] = $location->id;
        $formFields = $data['form_fields'] ?? [];
        unset($data['form_fields']);

        $updated = DB::transaction(function () use ($visitType, $data, $formFields) {
            $visitType = $this->visitTypeRepo->updateVisitType($visitType, $data);

            $existing = $visitType->formFields()->get()->keyBy('id');
            $incomingIds = [];

            foreach ($formFields as $ff) {
                if (isset($ff['id']) && $existing->has($ff['id'])) {
                    $existingField = $existing->get($ff['id']);
                    $existingField->update($ff);
                    $incomingIds[] = $existingField->id;
                } else {
                    $ff['tenant_id'] = $data['tenant_id'];
                    $ff['location_id'] = $data['location_id'];
                    $new = $visitType->formFields()->create($ff);
                    $incomingIds[] = $new->id;
                }
            }

            if (!empty($incomingIds)) {
                $visitType->formFields()->whereNotIn('id', $incomingIds)->delete();
            } else {
                $visitType->formFields()->delete();
            }

            return $visitType->fresh()->load('formFields');
        });

        return $this->successResponse('Visit type updated successfully.', ['visit_type' => $updated]);
    }

    public function destroy(Location $location, VisitType $visitType): JsonResponse
    {
        if ((string) $visitType->location_id !== (string) $location->id) {
            return $this->errorResponse('Visit type does not belong to this location.', null, 404);
        }

        $this->visitTypeRepo->deleteVisitType($visitType);

        return $this->successResponse('Visit type deleted successfully.');
    }

    public function createFormField(StoreFormFieldRequest $request, Location $location, VisitType $visitType): JsonResponse
    {
        if ((string) $visitType->location_id !== (string) $location->id) {
            return $this->errorResponse('Visit type does not belong to this location.', null, 404);
        }

        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['location_id'] = $location->id;
        $data['visit_type_id'] = $visitType->id;

        $created = DB::transaction(function () use ($visitType, $data) {
            return $visitType->formFields()->create($data);
        });

        return $this->successResponse('Form field created successfully.', ['form_field' => $created], 201);
    }
}
