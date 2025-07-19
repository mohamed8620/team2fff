<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\API\RayController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DoctorController;

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/dashboard', [AuthController::class, 'dashboard']);

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'No user found with this email.'], 404);
    }

    $code = rand(100000, 999999);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        ['token' => bcrypt($code), 'created_at' => now()]
    );

    Mail::raw("Your password reset code is: $code", function ($message) use ($request) {
        $message->to($request->email)
            ->subject('Password Reset Code');
    });

    return response()->json(['message' => 'Reset code sent to your email.']);
});

Route::post('/verify-reset-code', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string'
    ]);

    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$record) {
        return response()->json(['message' => 'No reset request found.'], 404);
    }

    if (!Hash::check($request->code, $record->token)) {
        return response()->json(['message' => 'Invalid code.'], 400);
    }

    return response()->json(['message' => 'Code verified successfully.']);
});


Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string',
        'password' => 'required|confirmed|min:6',
    ]);

    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$record || !Hash::check($request->code, $record->token)) {
        return response()->json(['message' => 'Invalid reset attempt.'], 400);
    }

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    $user->update([
        'password' => Hash::make($request->password)
    ]);

    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    return response()->json(['message' => 'Password has been reset successfully.']);
});

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirectToProvider']);

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);

Route::middleware('auth:sanctum')->post('/rays', [RayController::class, 'store']);

Route::middleware('auth:sanctum')->get('/rays', [RayController::class, 'index']);

Route::middleware('auth:sanctum')->get('/rays/{id}', [RayController::class, 'show']);

Route::middleware('auth:sanctum')->delete('/rays/{id}', [RayController::class, 'destroy']);

Route::middleware('auth:sanctum')->post('/appointments', [AppointmentController::class, 'store']);

Route::get('/appointments/available', [AppointmentController::class, 'availableSlots']);

Route::middleware('auth:sanctum')->get('/appointments/my', [AppointmentController::class, 'myAppointment']);

Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'profile']);

Route::middleware('auth:sanctum')->put('/me', [AuthController::class, 'updateProfile']);

Route::middleware('auth:sanctum')->get('/doctors', [UserController::class, 'listDoctors']);

Route::middleware(['auth:sanctum'])->get('/doctor/patients', [DoctorController::class, 'listPatients']);

Route::middleware('auth:sanctum')->post('/doctor/notes', [DoctorController::class, 'addNote']);

Route::middleware('auth:sanctum')->put('/doctor/notes/{id}', [DoctorController::class, 'updateNote']);

Route::middleware('auth:sanctum')->delete('/doctor/notes/{id}', [DoctorController::class, 'deleteNote']);

Route::middleware('auth:sanctum')->get('/doctor/patients/{id}/notes', [DoctorController::class, 'getPatientNotes']);

Route::middleware('auth:sanctum')->get('/doctor/rays/{id}/ai', [DoctorController::class, 'showRayAI']);

Route::middleware('auth:sanctum')->post('/doctor/patients/status', [DoctorController::class, 'setPatientStatus']);
