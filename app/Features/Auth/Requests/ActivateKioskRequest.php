<?php

namespace App\Features\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivateKioskRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true; // public endpoint
  }

  public function rules(): array
  {
    return [
      'code' => ['required', 'string', 'min:6', 'max:32'],
    ];
  }
}
