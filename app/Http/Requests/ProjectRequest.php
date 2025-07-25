<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:konstruksi,maintenance,other',
            'status' => 'required|in:draft,planning,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'planned_service_value' => 'nullable|numeric|min:0',
            'planned_material_value' => 'nullable|numeric|min:0',
            'planned_total_value' => 'nullable|numeric|min:0',
            'final_service_value' => 'nullable|numeric|min:0',
            'final_material_value' => 'nullable|numeric|min:0',
            'final_total_value' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default values for numeric fields if they are empty
        $this->merge([
            'planned_service_value' => $this->planned_service_value ?: 0,
            'planned_material_value' => $this->planned_material_value ?: 0,
            'planned_total_value' => $this->planned_total_value ?: 0,
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama proyek harus diisi.',
            'type.required' => 'Tipe proyek harus dipilih.',
            'status.required' => 'Status proyek harus dipilih.',
            'priority.required' => 'Prioritas proyek harus dipilih.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ];
    }
}
