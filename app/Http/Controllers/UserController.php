<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $logged;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->logged = auth()->user();
    }

    public function read()
    {
        $array = ['error' => ''];

        $info = $this->logged;
        $info['avatar'] = url('media/avatars/'.$info['avatar']);

        $array['data'] = $info;

        return $array;

    }
}
