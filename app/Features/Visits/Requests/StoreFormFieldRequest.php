<?php

namespace App\Features\Visits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormFieldRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'label' => ['required', 'string', 'max:255'],
      'name' => ['required', 'string', 'max:255'],
      'type' => ['required', 'string', 'max:100'],
      'required' => ['boolean'],
      'options' => ['nullable', 'array'],
      'validation_rules' => ['nullable', 'array'],
      'placeholder' => ['nullable', 'string', 'max:255'],
      'is_system' => ['boolean'],
      'sort_order' => ['nullable', 'integer'],
    ];
  }
}
