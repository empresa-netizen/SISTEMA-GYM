<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentPhotoRequest extends FormRequest
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
            'photo' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp'],
            'type' => ['nullable', 'string', 'in:front,back,side,progress'],
            'caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.required' => 'Selecione uma foto de evolucao.',
            'photo.mimes' => 'Envie uma foto nos formatos jpg, png, gif ou webp.',
            'photo.max' => 'A foto deve ter no maximo 10MB.',
            'type.in' => 'Tipo de foto invalido.',
            'caption.max' => 'A legenda deve ter no maximo 255 caracteres.',
        ];
    }
}
