<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }

    /**
     * 获取用户头像
     *
     * @param string $size
     * @return void
     */
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
        // return "http://www.gravatar.com/avatar/".$hash."?s=".$size;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * 模型关联：用户——微博
     *
     * @return void
     */
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    /**
     * 用户微博时间倒序
     * $user->followings == $user->followings()->get()
     * @return void
     */
    public function feed()
    {
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);

        return Status::whereIn('user_id', $user_ids)->with('user')->orderBy('created_at', 'desc');
    }

    /**
     * 获取用户粉丝id
     *
     * @return void
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    /**
     * 获取用户关注的id
     *
     * @return void
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * 关注用户
     *
     * @param array $user_ids
     * @return void
     */
    public function follow($user_ids)
    {
        if(!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }

        $this->followings()->sync($user_ids, false);
    }

    /**
     * 取消关注用户
     *
     * @param array $user_ids
     * @return void
     */
    public function unfollow($user_ids)
    {
        if(!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }

        $this->followings()->detach($user_ids, false);
    }

    /**
     * 当前用户是否关注该用户
     *
     * @param int $user_id
     * @return boolean
     */
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
