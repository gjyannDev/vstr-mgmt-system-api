<?php

namespace App\Features\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKioskRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255'],
      'location_id' => ['required', 'integer', 'exists:locations,id'],
      'status' => ['nullable', 'string', 'in:active,disabled'],
    ];
  }
}
