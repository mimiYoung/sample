<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $user = $users->first();
        $user_id = $user->id;

        //获取除 id 为 1 以外的用户 id 组
        $followers = $users->slice(1);
        $follower_ids = $followers->pluck('id')->toArray();

        //除了 id 为 1 的自己，其他都关注
        $user->follow($follower_ids);

        //除 id = 1 以外的所有 id 来关注 1 号
        foreach ($followers as $follower) {
            $follower->follow($user_id);
        }
    }
}
