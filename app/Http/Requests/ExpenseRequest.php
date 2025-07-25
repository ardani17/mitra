<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('amount')) {
            // Hapus titik pemisah ribuan dari amount
            $amount = str_replace('.', '', $this->amount);
            // Hapus koma jika ada (untuk desimal)
            $amount = str_replace(',', '.', $amount);
            
            $this->merge([
                'amount' => $amount
            ]);
        }
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
            'description' => 'required|string|max:500',
            'category' => 'required|in:material,labor,equipment,transportation,other',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'receipt_number' => 'nullable|string|max:100',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Proyek harus dipilih.',
            'project_id.exists' => 'Proyek yang dipilih tidak valid.',
            'description.required' => 'Deskripsi pengeluaran harus diisi.',
            'category.required' => 'Kategori pengeluaran harus dipilih.',
            'amount.required' => 'Jumlah pengeluaran harus diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'amount.min' => 'Jumlah tidak boleh negatif.',
        ];
    }
}
