<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
      'password' => ['required', 'confirmed', Password::defaults()],
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
      'password.letters' => 'La contraseña debe incluir al menos una letra.',
      'password.numbers' => 'La contraseña debe incluir al menos un número.',
      'password.uncompromised' => 'Esta contraseña apareció en una filtración de datos; elige otra.',
      'password.confirmed' => 'La confirmación de contraseña no coincide.',
    ];
  }
}
