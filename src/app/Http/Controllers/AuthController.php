<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Auth\Events\Registered;



class AuthController extends Controller
{
    ##会員登録処理
    public function register(RegisterRequest $request)
    {
        $form = $request->only(['name', 'email', 'password']);
        $form['password'] = Hash::make($form['password']);
        $user = User::create($form);
        event(new Registered($user));
        Auth::login($user);
        return redirect()->route('verification.notice');
    }

    ##ログイン認証
    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            session(['from_login' => true]);
            return redirect('/?tab=mylist');
        }
    }
}
