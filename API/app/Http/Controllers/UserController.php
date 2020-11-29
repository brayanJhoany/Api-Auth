<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Validator;
use Illuminate\Http\Request;

use \Firebase\JWT\JWT;

class UserController extends Controller
{
    // url de mock API donde tenemos registrado la tabla user.
    private $baseUrl='https://5fbbd1f2c09c200016d412a5.mockapi.io/';
    private $key='ajVa7hqVnpkzKmwlV78BiWTqrt0YWigYROmyI2jMbOXRrG80AvkhacNstN4x6Zke';


    /**
     * Permite el inicio de secion de un usuario, recibe como parametros
     * el Email y la password
     *
     * @return JsonResponse
     */
    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        //validamos los campos de email y password
        $validator =Validator::make($credentials,[
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        //si los credenciales son invalidos, retornamos un error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try {
            $users =  $response = Http::get($this->baseUrl.'user');
            $users = $users->json();
            foreach($users as $user){
                if($credentials['email']==$user['email']){
                    if($credentials['password']==$user['password']){
                        $payload = array(
                            "id" => $user['id'],
                            "name" => $user['name'],
                            "email" => $user['email'],
                            "password" => $user['password'],
                        );
                        $token = JWT::encode($payload, $this->key);
                        $response = Http::put($this->baseUrl.'user/'.$user['id'], [
                            'token' => $token,
                        ]);
                        return response()->json([
                            'success' => true,
                            'message' => 'Operacion realizada con exito',
                            'data' => ['token'=>$token],
                        ], 200);
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Clave incorrecta',
                            'data' => [],
                        ], 422);
                    }
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'El email no existe',
                'data' => []
            ], 422);
        }catch (\Illuminate\Database\QueryException $ex){
            return response()->json([
                'success' => false,
                'message' => 'Problema en la base de datos',
                'data' => ['error'=>$ex]
            ], 409);
        }
    }

    /**
     * Registra un nuevo usuario, recibe como parametro
     * el name, email y password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $response = Http::post($this->baseUrl.'user/', [
            "name" => $request['name'],
            "email" => $request['email'],
            'password' => $request['password'],
            'token' => null,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Se a registrado con exito',
            'data' => [],
        ], 200);
    }

    /**
     * Muestra la información correspondiente a un usuario registrado.
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request){
        try{
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, $this->key, array('HS256'));
            $response = Http::get($this->baseUrl.'user/'.$decoded->id);
            $response = $response->json();
            unset($response['password']);
            unset($response['token']);
            return response()->json([
                'success' => true,
                'message' => "Informaciòn del usuario",
                'data' => $response
            ], 200);
        }catch(\Illuminate\Database\QueryException $ex){
            return response()->json([
                'success' => false,
                'message' => "Error en establecer comunicaciòn con la base de datos",
                'data' => ['error'=>$ex]
            ], 409);
        }
    }

    /**
     * Actualiza la información de un usuario registrado.
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request){
        $credentials = $request->only('name', 'email','password');
        $validator =Validator::make($credentials,[
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error en los parametros ingresados',
                'data' => ['error'=>$validator->errors()],
            ], 422);
        }
        $token = $request->bearerToken();
        $decoded = JWT::decode($token, $this->key, array('HS256'));
        if($credentials['name']=='' || $credentials['name']==null){
            $credentials['name']=$decoded->name;
        }
        if($credentials['email']=='' || $credentials['email']==null){
            $credentials['email']=$decoded->email;
        }
        if($credentials['password']=='' || $credentials['password']==null){
            $credentials['password']=$decoded->password;
        }
        try{
            $response = Http::put($this->baseUrl.'user/'.$decoded->id, [
                'name' => $credentials['name'],
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);
            return response()->json([
                'success' => true,
                'message' => "Se modifico la informacion del usuario",
                'data' => null
            ], 200);
        }catch(\Illuminate\Database\QueryException $ex){
            return response()->json([
                'success' => false,
                'message' => "Problema en la base de datos",
                'data' => ['error'=>$ex]
            ], 409);
        }
    }

    /**
     * Permite eliminar la informacion registrada de un usuario.
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request){
        try{
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, $this->key, array('HS256'));
            $response = Http::delete($this->baseUrl.'user/'.$decoded->id);
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron los registros del usuario con exito.",
                'data' => $response->json()
            ], 200);
        }catch(\Illuminate\Database\QueryException $ex){
            return response()->json([
                'success' => false,
                'message' => "Error en establecer comunicación con la base de datos.",
                'data' => ['error'=>$ex]
            ], 409);
        }
    }

}
