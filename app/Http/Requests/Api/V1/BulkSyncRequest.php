<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        $userId = Auth::id();

        $created = $this->input('created', []);
        $updated = $this->input('updated', []);

        // Normalize created events
        $created = array_map(function ($item) use ($userId) {
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

            // Add user_id to each created item
            $item['user_id'] = $userId;

            return $item;
        }, $created);

        // Normalize updated events
        $updated = array_map(function ($item) use ($userId) {
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

            // Add user_id to each updated item
            $item['user_id'] = $userId;

            return $item;
        }, $updated);

        $this->merge([
            'user_id' => $userId,
            'created' => $created,
            'updated' => $updated,
        ]);
    }

    public function rules(): array
    {
        return [
            // Global user_id validation
            'user_id' => ['required', 'integer', 'exists:users,id'],

            // Created items validation
            'created' => ['array'],
            'created.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'created.*.name' => ['required', 'string', 'max:255'],
            'created.*.amount' => ['nullable', 'numeric'],
            'created.*.description' => ['nullable', 'string', 'max:255'],
            'created.*.color_scheme' => ['nullable', 'string', 'max:255'],
            'created.*.date' => ['required', 'date'],
            'created.*.is_done' => ['boolean'],
            'created.*.categories' => ['array'],

            // Updated items validation
            'updated' => ['array'],
            'updated.*.id' => ['required', 'integer'],
            'updated.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'updated.*.name' => ['required', 'string', 'max:255'],
            'updated.*.amount' => ['nullable', 'numeric'],
            'updated.*.description' => ['nullable', 'string', 'max:255'],
            'updated.*.color_scheme' => ['nullable', 'string', 'max:255'],
            'updated.*.is_done' => ['boolean'],
            'updated.*.date' => ['required', 'date'],
            'updated.*.categories' => ['array'],

            // Deleted items validation
            'deleted' => ['array'],
            'deleted.*' => ['integer'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $userId = Auth::id();

            $created = $this->input('created', []);
            foreach ($created as $index => $item) {
                if (isset($item['user_id']) && $item['user_id'] != $userId) {
                    $validator->errors()->add("created.{$index}.user_id", 'You can only create items for yourself.');
                }
            }

            $updated = $this->input('updated', []);
            foreach ($updated as $index => $item) {
                if (isset($item['user_id']) && $item['user_id'] != $userId) {
                    $validator->errors()->add("updated.{$index}.user_id", 'You can only update your own items.');
                }
            }
        });
    }
}
