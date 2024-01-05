<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect('/');
        } else {
            return view('login');
        }
    }
    public function actionlogin(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                return redirect('/aktivitas');
            } else {
                session()->flash('status', 'Username / Password salah');
                return redirect('/login');
            }
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    public function actionLogout()
    {
        Auth::logout();
        return redirect('/login');
    }
}

