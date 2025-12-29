<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreScheduledPostRequest extends FormRequest
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
            'account_id' => ['required', 'exists:bluesky_accounts,id'],
            'content' => ['required', 'string', 'min:10', 'max:300'],
            'publish_at' => ['required', 'date', 'after:now'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('publish_at')) {
            $this->merge([
                'publish_at' => Carbon::parse($this->input('publish_at'))->toISOString(),
            ]);
        }
    }
}
