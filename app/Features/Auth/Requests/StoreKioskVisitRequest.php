<?php

namespace App\Features\Auth\Requests;

use App\Models\Kiosk;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKioskVisitRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $identity = $this->user();

    if (! $identity instanceof Kiosk) {
      return [
        'visitor_id' => ['required', 'integer', Rule::in([-1])],
        'visit_type_id' => ['required', 'integer', Rule::in([-1])],
        'host_id' => ['nullable', 'integer', Rule::in([-1])],
        'purpose' => ['nullable', 'string', 'max:255'],
        'notes' => ['nullable', 'string'],
      ];
    }

    return [
      'visitor_id' => [
        'required',
        'integer',
        Rule::exists('visitors', 'id')->where(function (Builder $query) use ($identity) {
          $query
            ->where('tenant_id', $identity->tenant_id)
            ->where('location_id', $identity->location_id);
        }),
      ],
      'visit_type_id' => [
        'required',
        'integer',
        Rule::exists('visit_types', 'id')->where(function (Builder $query) use ($identity) {
          $query
            ->where('tenant_id', $identity->tenant_id)
            ->where('location_id', $identity->location_id)
            ->where('active', true);
        }),
      ],
      'host_id' => [
        'nullable',
        'integer',
        Rule::exists('hosts', 'id')->where(function (Builder $query) use ($identity) {
          $query
            ->where('tenant_id', $identity->tenant_id)
            ->where('location_id', $identity->location_id);
        }),
      ],
      'purpose' => ['nullable', 'string', 'max:255'],
      'notes' => ['nullable', 'string'],
    ];
  }
}
