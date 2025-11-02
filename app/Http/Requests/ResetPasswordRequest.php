<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
      'token' => 'required|string',
      'email' => 'required|email', // Removido 'exists:users,email' por seguridad
      'password' => 'required|string|min:8|confirmed',
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
      'token.required' => 'El token es obligatorio.',
      'email.required' => 'El campo email es obligatorio.',
      'email.email' => 'El email debe tener un formato válido.',
      'password.required' => 'La contraseña es obligatoria.',
      'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
      'password.confirmed' => 'La confirmación de contraseña no coincide.',
    ];
  }
}
