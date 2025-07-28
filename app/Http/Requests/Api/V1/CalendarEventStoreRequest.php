<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CalendarEventStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  public function authorize(): bool
    {
        return Auth::check(); 
    }

    protected function prepareForValidation()
    {
        $userId = Auth::id();
        
        $this->merge([
            'user_id' => $userId
        ]);
    }

 

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['nullable'],
            'description' => ['required', 'string', 'max:255'],
            'color_scheme' => ['string', 'max:255'],
            'date' => ['required'],
        ];
    }
}
