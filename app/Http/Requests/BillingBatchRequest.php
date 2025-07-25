<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillingBatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'billing_date' => 'required|date',
            'pph_rate' => 'required|numeric|min:0|max:100',
            'ppn_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'projects' => 'required|array|min:1',
            'projects.*' => 'required|exists:projects,id',
            'client_type' => 'required|in:wapu,non_wapu',
            'sp_number' => 'required|string|max:100|unique:billing_batches,sp_number,' . ($this->route('billing_batch') ? $this->route('billing_batch')->id : 'NULL'),
            'invoice_number' => 'required|string|max:100|unique:billing_batches,invoice_number,' . ($this->route('billing_batch') ? $this->route('billing_batch')->id : 'NULL'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'billing_date.required' => 'Tanggal penagihan wajib diisi.',
            'billing_date.date' => 'Format tanggal penagihan tidak valid.',
            'pph_rate.required' => 'Rate PPh wajib diisi.',
            'pph_rate.numeric' => 'Rate PPh harus berupa angka.',
            'pph_rate.min' => 'Rate PPh minimal 0%.',
            'pph_rate.max' => 'Rate PPh maksimal 100%.',
            'ppn_rate.required' => 'Rate PPN wajib diisi.',
            'ppn_rate.numeric' => 'Rate PPN harus berupa angka.',
            'ppn_rate.min' => 'Rate PPN minimal 0%.',
            'ppn_rate.max' => 'Rate PPN maksimal 100%.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
            'projects.required' => 'Minimal harus memilih satu proyek.',
            'projects.array' => 'Format proyek tidak valid.',
            'projects.min' => 'Minimal harus memilih satu proyek.',
            'projects.*.required' => 'ID proyek wajib diisi.',
            'projects.*.exists' => 'Proyek yang dipilih tidak valid.',
            'client_type.required' => 'Tipe klien wajib dipilih.',
            'client_type.in' => 'Tipe klien harus WAPU atau Non-WAPU.',
            'sp_number.required' => 'Nomor SP wajib diisi.',
            'sp_number.string' => 'Nomor SP harus berupa teks.',
            'sp_number.max' => 'Nomor SP maksimal 100 karakter.',
            'sp_number.unique' => 'Nomor SP sudah digunakan.',
            'invoice_number.required' => 'Nomor invoice wajib diisi.',
            'invoice_number.string' => 'Nomor invoice harus berupa teks.',
            'invoice_number.max' => 'Nomor invoice maksimal 100 karakter.',
            'invoice_number.unique' => 'Nomor invoice sudah digunakan.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'billing_date' => 'tanggal penagihan',
            'pph_rate' => 'rate PPh',
            'ppn_rate' => 'rate PPN',
            'notes' => 'catatan',
            'projects' => 'proyek',
            'sp_number' => 'nomor SP',
            'invoice_number' => 'nomor invoice',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure projects is always an array
        if ($this->has('projects') && !is_array($this->projects)) {
            $this->merge([
                'projects' => [$this->projects]
            ]);
        }

        // Convert rates to float
        if ($this->has('pph_rate')) {
            $this->merge([
                'pph_rate' => (float) $this->pph_rate
            ]);
        }

        if ($this->has('ppn_rate')) {
            $this->merge([
                'ppn_rate' => (float) $this->ppn_rate
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if selected projects have final values and are available for billing
            if ($this->has('projects')) {
                $projects = \App\Models\Project::whereIn('id', $this->projects)
                    ->where(function($query) {
                        // At least one of service or material value must be > 0
                        $query->where('final_service_value', '>', 0)
                              ->orWhere('final_material_value', '>', 0);
                    })
                    ->whereRaw('COALESCE(final_service_value, 0) + COALESCE(final_material_value, 0) > 0')
                    ->get();

                if ($projects->count() !== count($this->projects)) {
                    $validator->errors()->add('projects', 'Beberapa proyek tidak memiliki nilai final atau tidak tersedia untuk penagihan.');
                }

                // Check if projects already have billing in batch
                foreach ($projects as $project) {
                    $existingBilling = $project->billings()
                        ->whereNotNull('billing_batch_id')
                        ->first();
                    
                    if ($existingBilling) {
                        // If updating, allow if billing is in current batch
                        if (!$this->route('billing_batch') || 
                            $existingBilling->billing_batch_id !== $this->route('billing_batch')->id) {
                            $validator->errors()->add('projects', "Proyek {$project->code} sudah memiliki penagihan dalam batch lain.");
                        }
                    }
                }
            }

            // Validate billing date is not in the future
            if ($this->has('billing_date')) {
                $billingDate = \Carbon\Carbon::parse($this->billing_date);
                if ($billingDate->isFuture()) {
                    $validator->errors()->add('billing_date', 'Tanggal penagihan tidak boleh di masa depan.');
                }
            }
        });
    }
}
