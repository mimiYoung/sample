<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function create()
    {
        return view('sessions.create');
    }

    /**
     * 用户登录
     */
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' =>  'required|email|max:255',
            'password'  =>  'required'
        ]);

        if(Auth::attempt($credentials, $request->has('remember'))) {
            if(Auth::user()->activated) {
                session()->flash('success', '欢迎回来！');
                return redirect()->intended(route('users.show', [Auth::user()]));
            } else {
                Auth::logout();
                session()->flash('warning', '您的账号未激活，请先在邮箱中激活账号。');
                return redirect('/');
            }
        } else {
            session()->flash('danger', '邮箱或密码有误，请重新输入！');
            return redirect()->back();
        }

        return;
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出!');
        return redirect('login');
    }
}
