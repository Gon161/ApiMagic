<?php

use App\Http\Controllers\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('/registroForm', [UsuarioController::class, 'registrarUsuario'])->name('registro.form');
    Route::post('/verificarCredenciales', [UsuarioController::class, 'VerificarCredenciales'])->name('login.form');
    Route::get('/verificacionMail/{codigo}', [UsuarioController::class, 'verificacionMail'])->name('verificacion.mail');
    Route::post('/recuperarContrasenia', [UsuarioController::class, 'recuperarContrasenia'])->name('recuperar.contrasenia');
    Route::post('/codigo', [UsuarioController::class, 'codigo'])->name('contrasenia');
    Route::post('/cambio/codigo', [UsuarioController::class, 'cambio'])->name('cambio');
    /*
    Route::post('register', [AuthController::class,'register']);
    Route::post('login', [AuthController::class,'login']);
    Route::post('logout', [AuthController::class,'logout']);
    */

});
