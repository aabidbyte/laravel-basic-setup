<?php

declare(strict_types=1);

namespace App\Http\Requests\Preferences;

use App\Constants\Preferences\FrontendPreferences;
use App\Services\I18nService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocaleRequest extends FormRequest
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
        $supportedLocales = array_keys(app(I18nService::class)->getSupportedLocales());

        return [
            FrontendPreferences::KEY_LOCALE => [
                'required',
                'string',
                Rule::in($supportedLocales),
            ],
        ];
    }
}
