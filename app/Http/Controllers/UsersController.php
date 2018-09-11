<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Rquests;
use App\Models\User;
use Mail, Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['create','store','show','confirmEmail']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    /**
     * 用户列表(页面)
     *
     * @return void
     */
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * 用户注册(页面)
     *
     * @return void
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * 用户个人(页面)
     *
     * @param User $user
     * @return void
     */
    public function show(User $user)
    {
        $statuses = $user->statuses()
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return view('users.show',compact('user', 'statuses'));
    }

    /**
     * 用户注册
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'  =>  'required|min:3|max:50',
            'email' =>  'required|email|unique:users|max:255',
            'password'  =>  'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name'  =>  $request->name,
            'email' =>  $request->email,
            'password'  =>  bcrypt($request->password)
        ]);

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送，请查收。');
        return redirect('/');
    }

    /**
     * 用户资料修改(页面)
     *
     * @param User $user
     * @return void
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    /**
     * 用户资料修改
     *
     * @param User $user
     * @param Request $request
     * @return void
     */
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name'  =>  'required|max:50',
            'passwrod'  =>  'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);
        
        $data = [];
        $data['name'] = $request->name;

        if($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);
        
        session()->flash('success', '个人资料修改成功！');
        
        return redirect()->route('users.show', $user->id);
    }

    /**
     * 删除用户
     *
     * @param User $user
     * @return void
     */
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户!');
        return back();
    }

    /**
     * 发送用户激活邮件
     *
     * @param object $user
     * @return void
     */
    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'young@sample.com';
        $name = 'Young';
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to);
            $message->subject($subject);
            // $message->from($from, $name);
            // $message->sender('john@johndoe.com', 'John Doe');
            // $message->cc('john@johndoe.com', 'John Doe');
            // $message->bcc('john@johndoe.com', 'John Doe');
            // $message->replyTo('john@johndoe.com', 'John Doe');
            
            // $message->priority(3);
            // $message->attach('pathToFile');
        });
    }

    /**
     * 用户邮箱验证后激活
     *
     * @param string $token
     * @return void
     */
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，注册成功！');
        return redirect()->route('users.show', [$user]);
    }

    /**
     * 我关注的人
     *
     * @param User $user
     * @return void
     */
    public function followings(User $user)
    {
        $users = $user->followings()->paginate(10);
        $title = '我关注的人';

        return view('users.show_follow', compact('users', 'title'));
    }

    /**
     * 粉丝
     *
     * @param User $user
     * @return void
     */
    public function followers(User $user)
    {
        $users = $user->followers()->paginate(10);
        $title = '粉丝';

        return view('users.show_follow', compact('users', 'title'));
    }
}
