<?php
namespace App\Http\Controllers\Api\Frontend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Leader;
use App\Models\User;

class LeaderController extends Controller
{
    public function index()
    {
        $leaders = Leader::where('status', 'active')->orderBy('order_id', 'asc')->paginate();
        $data    = [
            'leaders' => $leaders,
        ];
        return Helper::jsonResponse(true, 'get all leaders', 200, $data);
    }

    public function show($id)
    {
        $leader = Leader::where('id', $id)->first();
        $data   = [
            'leader' => $leader,
        ];
        return Helper::jsonResponse(true, 'get single leader', 200, $data);
    }

    public function mainLeader()
    {
        $leader = Leader::where('is_leader', 1)->first();
        $data   = [
            'leader' => $leader,
        ];
        return Helper::jsonResponse(true, 'get single leader', 200, $data);
    }

    public function executiveLeader($item = 12)
    {
        $leaders = Leader::where('is_leader', '!=', '1')->orderBy('order_id', 'asc')->paginate($item);
        $data    = [
            'leaders' => $leaders,
        ];
        return Helper::jsonResponse(true, 'get single leader', 200, $data);
    }

    public function members($type = 'member', $item = null)
    {
        // dd($type, $item);
        $usersQuery = User::with('profile:id,user_id,type,content')
            ->where('id', '!=', 1)
            ->whereHas('profile', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->orderBy('id', 'asc');

        if ($item) {
            $users = $usersQuery->find($item);
            // descritpiton return need
            if (!$users) {
                return Helper::jsonResponse(false, 'User not found', 404);
            }
            $users->description = optional($users->profile)->content;

        } else {
            $users = $usersQuery->get();
        }

        $users->each(function ($user) {
            $user->description = optional($user->profile)->content;
        });

        return Helper::jsonResponse(true, 'User list retrieved successfully', 200, ['users' => $users]);
    }

}
