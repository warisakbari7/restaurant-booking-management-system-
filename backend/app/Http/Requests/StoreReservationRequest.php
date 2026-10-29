<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxGuestCount = config('restaurant.rules.max_guests');
        return [
            'reservation_date' => ['required', 'date_format:Y-m-d'],
            'reservation_time' => ['required','date_format:H:i:s'],
            'guest_count' => ['required', 'integer', 'min:1', "max:$maxGuestCount"],
            'special_requests' => ['string', 'nullable', 'max:1000']
        ];
    }
}
