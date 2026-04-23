<?php

namespace App\Features\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexKioskRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'pageIndex' => ['nullable', 'integer', 'min:0'],
      'pageSize' => ['nullable', 'integer', 'min:1', 'max:200'],
      'search' => ['nullable', 'string', 'max:255'],
      'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
    ];
  }
}
