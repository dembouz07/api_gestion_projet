<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SprintRequest extends FormRequest
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
            'number' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after:start_date',
            'objective' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->start_date && $this->deadline) {
                $start = Carbon::parse($this->start_date);
                $end = Carbon::parse($this->deadline);
                $days = $start->diffInDays($end);

                if ($days < 14) {
                    $validator->errors()->add('deadline', 'La durée du sprint doit être d\'au moins 2 semaines.');
                }

                if ($days > 28) {
                    $validator->errors()->add('deadline', 'La durée du sprint ne peut pas dépasser 4 semaines.');
                }
            }
        });
    }
}
