<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendInvoiceRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'body' => [
                'required',
            ],
            'subject' => [
                'required',
            ],
            'from' => [
                'required',
            ],
            'to' => [
                'required',
            ],
            'cc' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $emails = is_array($value) ? $value : [$value];

                    foreach ($emails as $email) {
                        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $fail("The {$attribute} field must be a valid email address or an array of valid email addresses.");
                            break;
                        }
                    }
                },
            ],
            'bcc' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $emails = is_array($value) ? $value : [$value];

                    foreach ($emails as $email) {
                        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $fail("The {$attribute} field must be a valid email address or an array of valid email addresses.");
                            break;
                        }
                    }
                },
            ],
        ];
    }
}
