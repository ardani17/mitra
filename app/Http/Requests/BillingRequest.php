<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillingRequest extends FormRequest
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
            'project_id' => 'required|exists:projects,id',
            'billing_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'sp_number' => 'nullable|string|max:255',
            'tax_invoice_number' => 'nullable|string|max:255',
            'nilai_jasa' => 'required|numeric|min:0',
            'nilai_material' => 'required|numeric|min:0',
            'ppn_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000'
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
            'project_id' => 'proyek',
            'billing_date' => 'tanggal penagihan',
            'invoice_number' => 'nomor invoice',
            'sp_number' => 'nomor SP',
            'tax_invoice_number' => 'nomor faktur pajak',
            'nilai_jasa' => 'nilai jasa',
            'nilai_material' => 'nilai material',
            'ppn_rate' => 'rate PPN',
            'description' => 'deskripsi'
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
            'project_id.required' => 'Proyek harus dipilih.',
            'project_id.exists' => 'Proyek yang dipilih tidak valid.',
            'billing_date.required' => 'Tanggal penagihan harus diisi.',
            'billing_date.date' => 'Tanggal penagihan harus berupa tanggal yang valid.',
            'nilai_jasa.required' => 'Nilai jasa harus diisi.',
            'nilai_jasa.numeric' => 'Nilai jasa harus berupa angka.',
            'nilai_jasa.min' => 'Nilai jasa tidak boleh kurang dari 0.',
            'nilai_material.required' => 'Nilai material harus diisi.',
            'nilai_material.numeric' => 'Nilai material harus berupa angka.',
            'nilai_material.min' => 'Nilai material tidak boleh kurang dari 0.',
            'ppn_rate.required' => 'Rate PPN harus diisi.',
            'ppn_rate.numeric' => 'Rate PPN harus berupa angka.',
            'ppn_rate.min' => 'Rate PPN tidak boleh kurang dari 0.',
            'ppn_rate.max' => 'Rate PPN tidak boleh lebih dari 100.',
        ];
    }
}
