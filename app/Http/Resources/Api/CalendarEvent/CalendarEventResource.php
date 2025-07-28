<?php

namespace App\Http\Resources\Api\CalendarEvent;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class CalendarEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'eventname' => $this->name,
            'name' => $this->name,
            'colorScheme' => $this->color_scheme,
            'description' => $this->description,
            'amount' => $this->amount,
            'startdate' => $this->date,
            'starttime' => '00:00',

            'enddate' => $this->date,
            'endtime' => '00:00',
            'categories' => $this->categories()->pluck('id'),
            'categories_object' => $this->categories()->get()
        ];
    }
}

