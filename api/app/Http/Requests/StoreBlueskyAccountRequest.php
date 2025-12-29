<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlueskyAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'handle' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9.-]+$/',
                Rule::unique('bluesky_accounts', 'handle'),
            ],
            'app_password' => ['required', 'string', 'min:16', 'max:128'],
            'label' => ['nullable', 'string', 'max:64'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('handle')) {
            $this->merge([
                'handle' => strtolower($this->input('handle')),
            ]);
        }
    }
}
