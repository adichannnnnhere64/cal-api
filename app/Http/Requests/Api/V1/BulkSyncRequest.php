<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $created = $this->input('created', []);
        $updated = $this->input('updated', []);

        // Normalize created events
        $created = array_map(function ($item) {
            if (isset($item['eventname'])) {
                $item['name'] = $item['eventname'];
                unset($item['eventname']);
            }

            if (isset($item['startdate'])) {
                $item['date'] = $item['startdate'];
                unset($item['startdate']);
            }

            // Ensure categories is always an array
            $item['categories'] = $item['categories'] ?? [];

            return $item;
        }, $created);

        // Normalize updated events
        $updated = array_map(function ($item) {
            if (isset($item['eventname'])) {
                $item['name'] = $item['eventname'];
                unset($item['eventname']);
            }

            if (isset($item['startdate'])) {
                $item['date'] = $item['startdate'];
                unset($item['startdate']);
            }

            // Ensure categories is always an array
            $item['categories'] = $item['categories'] ?? [];

            return $item;
        }, $updated);

        $this->merge([
            'created' => $created,
            'updated' => $updated,
        ]);
    }

    public function rules(): array
    {
        return [
            'created.*.name' => ['required', 'string', 'max:255'],
            'created.*.amount' => ['nullable', 'numeric'],
            'created.*.description' => ['required', 'string', 'max:255'],
            'created.*.color_scheme' => ['string', 'max:255'],
            'created.*.date' => ['required'],
            'created.*.categories' => ['array'],

            'updated.*.id' => ['required', 'numeric'],
            'updated.*.name' => ['required', 'string', 'max:255'],
            'updated.*.amount' => ['nullable', 'numeric'],
            'updated.*.description' => ['required', 'string', 'max:255'],
            'updated.*.color_scheme' => ['string', 'max:255'],
            'updated.*.date' => ['required'],
            'updated.*.categories' => ['array'],

            'deleted' => ['array'],
            'deleted.*' => ['integer'],
        ];
    }
}
