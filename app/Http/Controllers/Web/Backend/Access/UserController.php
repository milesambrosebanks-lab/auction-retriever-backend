<?php

namespace App\Http\Controllers\Web\Backend\Access;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{

    public function __construct()
    {
        View::share('crud', 'user');
    }

    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();

         $user = User::where('id', '!=', $user->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'customer');
            })->with('roles');

        if ($request->ajax()) {
            return DataTables::of($user)
                ->addIndexColumn()
                ->addColumn('last_activity', fn($user) => $user->last_activity_at ? $user->last_activity_at : null)
                ->addColumn('created', fn($user) => $user->created_at)
                ->addColumn('action', function ($user) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.users.edit', $user->id) . '" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>';
                    $btn .= '<a href="' . route('admin.users.show', $user->id) . '" class="btn btn-info"><i class="fa-solid fa-eye"></i></a>';

                    $btn .= '<form action="' . route('admin.users.destroy', $user->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure?\')">';
                    $btn .= csrf_field() . method_field('DELETE');
                    $btn .= '<button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i></button>';
                    $btn .= '</form>';

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('backend.layouts.access.users.index', compact('user'));
    }

    public function index2(Request $request)
    {
        $user = Auth::guard('web')->user();
        $users = User::where('id', '!=', $user->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'customer');
            })
            ->with('roles')
            ->orderBy('id', 'desc')
            ->paginate(25);

        return view('backend.layouts.access.users.public', compact('users'));
    }

    public function create()
    {
        return view('backend.layouts.access.users.create', ['roles' => Role::all()]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        foreach ($request->roles as $role) {
            DB::table('model_has_roles')->insert([
                'role_id' => $role,
                'model_type' => 'App\Models\User',
                'model_id' => $user->id
            ]);
        }

        return redirect()->route('admin.users.index')->with('t-success', 'User created t-successfully');
    }

    public function show($id)
    {
        $user = User::with(['profile'])->find($id);
        return view('backend.layouts.access.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::all();
        return view('backend.layouts.access.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $id,
            // 'roles' => 'required|array',
            // 'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $user = User::find($id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // DB::table('model_has_roles')->where('model_id', $id)->delete();

            // foreach ($request->roles as $role) {
            //     DB::table('model_has_roles')->insert([
            //         'role_id' => $role,
            //         'model_type' => 'App\Models\User',
            //         'model_id' => $user->id
            //     ]);
            // }

            return redirect()->back()->with('t-success', 'User updated t-successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);
        DB::table('model_has_roles')->where('model_id', $id)->delete();
        $user->delete();
        return redirect()->back()->with('t-success', 'User deleted t-successfully');
    }

    public function status(int $id)
    {
        $user = User::findOrFail($id);
        if (!$user) {
            redirect()->back()->with('t-error', 'User not found');
        }
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();
        session()->put('t-success', 'Status updated successfully');
        return view('backend.layouts.access.users.show', compact('user'));
    }

    public function card($slug)
    {

        $user = User::where('slug', $slug)->first();
        $logoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));
        $whitelogoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));
        $backLogoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));

        $avatarPath = public_path(
            $user->avatar && file_exists(public_path($user->avatar)) ? $user->avatar : 'default/profile.jpg'
        );

        $avatarBase64 = base64_encode(file_get_contents($avatarPath));

        //for pdf
        /* $qrCode = base64_encode(QrCode::size(90)->generate(route('admin.users.card', $user->slug)));
        $pdf = Pdf::loadView('card.pdf', compact('user', 'logoBase64', 'whitelogoBase64', 'avatarBase64', 'qrCode', 'backLogoBase64'))->setPaper('a4', 'portrait');
        return $pdf->stream();  */

        //for web
        $qrCode = QrCode::size(90)->generate(route('admin.users.card', $user->slug));
        return view('card.web', compact('user', 'logoBase64', 'whitelogoBase64', 'avatarBase64', 'qrCode', 'backLogoBase64'));
    }
}
