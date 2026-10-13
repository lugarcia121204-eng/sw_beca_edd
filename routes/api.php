<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CurpController;

// API para buscar CURP (usado por Register.vue)
Route::post('/buscar-curp', [CurpController::class , 'search']);

// Ruta de prueba: obtener el código de verificación por email (solo local/testing)
Route::post('/testing/verification-code', function (Request $request) {
	if (!App::environment(['local', 'testing'])) {
		return response()->json(['message' => 'Not allowed'], 403);
	}

	$email = $request->input('email');
	$code = DB::table('users')->where('email', $email)->value('email_verification_code');

	return response()->json(['code' => $code]);
});
