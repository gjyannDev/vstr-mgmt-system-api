<?php

namespace App\Features\Location\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $location = $this->route('location');
    $locationId = is_object($location) ? $location->id : $location;

    return [
      'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('locations', 'name')->ignore($locationId),
      ],
    ];
  }
}
