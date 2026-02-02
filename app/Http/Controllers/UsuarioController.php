<?php

namespace App\Http\Controllers;

use App\Mail\VerificacionEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['VerificarCredenciales','registrarUser','verificacionMail','recuperarContrasenia','codigo','cambio']]);
    }
    public function VerificarCredenciales(Request $datos)
    {
        try {
            $array = array('email'=>$datos->correo,'password'=> $datos->contrasenia);
            $credentials = $array;
            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['estatus' => 'Unauthorized'], 401);
            }

            if (!$datos->correo || !$datos->contrasenia)
                return ["estatus" => "error", "mensaje" => "Completa los campos"];

            $User = User::where('correo', $datos->correo)->first();

            if (!$User)
                return ["estatus" => "error", "mensaje" => "¡El correo no esta registrado!"];

            if ($User->status == 0)
                return ["estatus" => "error", "mensaje" => "¡La cuenta no ha sido verificada!"];

            if (!password_verify($datos->contrasenia, $User->contrasenia))
                return ["estatus" => "error", "mensaje" => "¡La contraseña que ingresaste es incorrecta!"];
            $User->tokenRecovery = $token;
            return $User;
        } catch (Exception $e) {
            $error = explode("in", $e);
            return (['estatus' => "Error", 'mensaje' => "Algo salio mal intenta de nuevo |" . $error[0]]);
        }
    }

    public function registrarUser(Request $datos)
    {
        $rules = [
            'nombre' => "required|min:3|max:32|alpha",
            'apellidoPaterno' => "required|min:3|max:32|alpha",
            'apellidoMaterno' => "required|min:3|max:32|alpha",
            'correo' => "required|email|min:8|max:64",
            'fechaNacimiento' => "required|date",
            'contrasenia' => "required|min:8|max:64",
            'contrasenia2' => "required|min:8|max:64"
        ];
        try {
            //Validacion
            $validator = Validator::make($datos->all(), $rules);
            if ($validator->fails()) {
                return $validator->errors();
            } else {
                //Se verifica si existe una cuenta ya
                $User = User::where('correo', $datos->correo)->first();
                if ($User)
                    return ["estatus" => "Error", "mensaje" => "El correo ya esta registrado"];
                //Se comparan las contrasenias
                if ($datos->contrasenia != $datos->contrasenia2)
                    return ["estatus" => "Error", "mensaje" => "Las contrasenias no son iguales"];

                $max_num = 6;
                $codigo = "";
                for ($x = 0; $x < $max_num; $x++) {
                    $num_aleatorio = rand(0, 9);
                    $codigo = $codigo . strval($num_aleatorio);
                }
                $datos["codigoConfirmacion"] = $codigo;
                //Se registran los datos
                $User = new User();
                $User->nombre = $datos->nombre;
                $User->apellidoPaterno = $datos->apellidoPaterno;
                $User->apellidoMaterno = $datos->apellidoMaterno;
                $User->correo = $datos->correo;
                $User->contrasenia = password_hash($datos->contrasenia, PASSWORD_DEFAULT, ['cost' => 5]);
                $User->fechaNacimiento = $datos->fechaNacimiento;
                $User->status = 0;
                $User->codigoConfirmacion = $datos->codigoConfirmacion;
                Mail::to($datos->correo)->send(new VerificacionEmail($User));
                $User->save();

                return ['estatus' => "sucess","mensaje" => "Cuenta creada"];
            }
        } catch (Exception $e) {
            $error = explode("in", $e);
            return (['estatus' => "Error", 'mensaje' => "Algo salio mal intenta de nuevo |" . $error[0]]);
        }
    }

    //Funcion para verificar email
    public function verificacionMail($codigo)
    {
        //Valida el codigo
        $User = User::where('codigoConfirmacion', $codigo)->first();
        if ($User) {
            $User->codigoConfirmacion = null;
            $User->status = 1;
            $User->save();
            return \response("Cuenta verificada, vuelve a la aplicacion", 201);
        } else {
            return \response("La cuenta ya ha sido verificada");
        }
    }

    public function recuperarContrasenia(Request $datos)
    {
        if (!$datos->correo)
            return ["estatus" => "error", "mensaje" => "¡Completa los campos!"];
        $User = User::where('correo', $datos->correo)->first();
        if (!$User)
            return ["estatus" => "error", "mensaje" => "¡El correo no esta registrado!"];
        $max_num = 6;
        $codigo = "";
        for ($x = 0; $x < $max_num; $x++) {
            $num_aleatorio = rand(0, 9);
            $codigo = $codigo . strval($num_aleatorio);
        }
        $User->tokenRecovery = $codigo;
        $User->save();
        Mail::to($User->correo)->send(new RecuperarMailable($User));
        return ["estatus" => "success", "mensaje" => "¡El correo se a enviado"];
    }

    public function codigo(Request $datos)
    {
        if (!$datos->codigo)
            return ["estatus" => "error", "mensaje" => "¡El ingresa el codigo!"];

        $User = User::where('token_recovery', $datos->codigo)->first();

        if (!$User)
            return ["estatus" => "error", "mensaje" => "¡Error en el codigo!"];

        return ["estatus" => "success", "codigo" => $datos->codigo];
    }

    public function cambio(Request $datos)
    {
        if (!$datos->contrasenia || !$datos->contrasenia2)
            return ["estatus" => "error", "mensaje" => "¡Completa los campos!"];

        if ($datos->contrasenia != $datos->contrasenia2)
            return ["estatus" => "error", "mensaje" => "¡Las contraseñas no son iguales!"];

        $User = User::where('token_recovery', $datos->codigo)->first();
        $User->contrasenia = password_hash($datos->contrasenia, PASSWORD_DEFAULT, ['cost' => 5]);
        $User->tokenRecovery = null;
        $User->save();

        return ["estatus" => "success", "mensaje" => "¡Contraseña cambiada!"];
    }
}
