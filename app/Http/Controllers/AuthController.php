<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Validator
};
use App\Models\User;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create', 'login']]);
    }

    public function create(Request $request)
    {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!$validator->fails()) {
            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');
    
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $token = auth()->attempt([
                'email' => $email,
                'password' => $password
            ]);


            if (!$token) {
                $array['error'] = 'Ocorreu um erro!';
                return $array;
            }

            $info = auth()->user();
            $info['avatar'] = url('media/avatars/'. $info['avatar']);
            $array['data'] = $info;
            $array['token'] = $token;
        } else {
            $array['error'] = 'Dados incorretos';
            return $array;
        }

        return $array;
    }

    public function login(Request $request)
    {
        $array = ['error' => ''];

        $email = $request->input('email');
        $password = $request->input('password');

        $token = auth()->attempt([
                        'email' => $email,
                        'password' => $password
                    ]);

        if (!$token) {
            $array['error'] = 'Usuário e/ou senha incorretas';
            return $array;
        }

        $info = auth()->user();
        $info['avatar'] = url('media/avatars/'. $info['avatar']);
        $array['data'] = $info;
        $array['token'] = $token;

        return $array;
    }

    public function logout()
    {
        auth()->logout();
        return ['success' => 'Deslogado com sucesso'];
    }

    public function refresh()
    {
        $array = ['error' => ''];

        $token = auth()->refresh();
        
        $info = auth()->user();
        $info['avatar'] = url('media/avatars/'. $info['avatar']);
        $array['data'] = $info;
        $array['token'] = $token;

        return $array;
    }
}
