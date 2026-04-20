<?php

namespace App\Features\Location\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255', 'unique:locations,name'],
    ];
  }
}
