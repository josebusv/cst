<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    /**
     * Send a reset link to the given user.
     * Siempre retorna el mismo mensaje por seguridad, sin revelar si el email existe.
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        try {
            // Verificar si el usuario existe antes de intentar enviar
            $user = User::where('email', $request->email)->first();
            
            if ($user) {
                // Solo enviar si el usuario existe
                Password::sendResetLink($request->only('email'));
            }
            
            // Siempre retornar el mismo mensaje, independientemente de si el email existe
            return response()->json([
                'message' => 'Si el email está registrado, recibirás un enlace de recuperación.',
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            // También retornar mensaje genérico en caso de error
            return response()->json([
                'message' => 'Si el email está registrado, recibirás un enlace de recuperación.',
                'status' => 'success'
            ], 200);
        }
    }

    /**
     * Reset the given user's password.
     */
    public function reset(ResetPasswordRequest $request)
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Contraseña restablecida exitosamente.',
                    'status' => 'success'
                ], 200);
            }

            return response()->json([
                'message' => $this->getResetErrorMessage($status),
                'status' => 'error'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the reset error message.
     */
    private function getResetErrorMessage($status)
    {
        switch ($status) {
            case Password::INVALID_TOKEN:
                return 'Token de recuperación inválido o expirado.';
            case Password::INVALID_USER:
                return 'Token de recuperación inválido o expirado.'; // No revelar que el usuario no existe
            case Password::RESET_THROTTLED:
                return 'Demasiados intentos. Intenta de nuevo en unos minutos.';
            default:
                return 'No se pudo restablecer la contraseña.';
        }
    }

    /**
     * Validate a password reset token without resetting the password.
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Token no válido o expirado.',
                'status' => 'error'
            ], 400);
        }

        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenData) {
            return response()->json([
                'message' => 'Token no válido o expirado.',
                'status' => 'error'
            ], 400);
        }

        // Verificar si el token ha expirado (60 minutos)
        $tokenAge = now()->diffInMinutes($tokenData->created_at);
        if ($tokenAge > 60) {
            return response()->json([
                'message' => 'Token no válido o expirado.',
                'status' => 'error'
            ], 400);
        }

        // Verificar el token
        if (!Hash::check($request->token, $tokenData->token)) {
            return response()->json([
                'message' => 'Token no válido o expirado.',
                'status' => 'error'
            ], 400);
        }

        return response()->json([
            'message' => 'Token válido.',
            'status' => 'success'
        ], 200);
    }
}