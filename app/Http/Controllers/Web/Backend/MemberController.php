<?php
namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Imports\MembersImport;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class MemberController extends Controller
{

    public function index(Request $request, $type = 'member')
    {

        if ($request->ajax()) {
            $user = auth('web')->user();

            // Base query with join to profiles for ordering/searching
            $data = User::leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->select(
                    'users.id',
                    'users.id_number',
                    'users.email',
                    'users.slug',
                    'users.created_at',
                    'profiles.registered_by',
                    'profiles.dob',
                    'profiles.country',
                    'profiles.county',
                    'profiles.first_name',
                    'profiles.country_of_residence',
                    'profiles.last_name',
                    'profiles.phone',
                    'profiles.city',
                    'profiles.gender',
                    'profiles.district',
                    'profiles.address',
                    'profiles.state',
                    'profiles.postal_code',
                    'profiles.type',
                    'profiles.is_card_printed'
                )
                ->where('users.id', '!=', $user->id);

            // Type filter
            if ($type) {
                $data->where('profiles.type', $type);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('id_number', fn($data) => $data->id_number - 1)
                ->addColumn('registered_by', fn($data) => $data->registered_by)
                ->addColumn('first_name', fn($data) => $data->first_name ?? '')
                ->addColumn('last_name', fn($data) => $data->last_name ?? '')
                ->addColumn('dob', fn($data) => $data->dob ?? '')
                ->addColumn('email', fn($data) => $data->email ?? '')
                ->addColumn('phone', fn($data) => $data->phone ?? '')
                ->addColumn('city', fn($data) => $data->city ?? '')
                ->addColumn('gender', fn($data) => $data->gender ?? '')
                ->addColumn('date_of_issue', fn($data) => Carbon::parse($data->created_at)->format('d/m/Y'))
                ->addColumn('checkbox', fn($data) => '<input type="checkbox" class="user-checkbox" value="' . $data->id_number . '">')
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                    <a href="#" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white" title="Edit"><i class="fe fe-edit"></i></a>
                    <a href="#" onclick="goToShow(' . $data->id . ')" class="btn btn-info fs-14 text-white" title="Show"><i class="fe fe-info"></i></a>
                    <a href="#" tar  onclick="goToPrint(\'' . $data->slug . '\')" class="btn btn-warning fs-14 text-white" title="Print"><i class="fe fe-printer"></i></a>
                    <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white" title="Delete"><i class="fe fe-trash"></i></a>
                </div>';
                })
                ->filter(function ($query) use ($request) {

                    // Global search
                    if ($search = $request->get('search')['value']) {
                        $query->where(function ($q) use ($search) {
                            $q->where('users.email', 'like', "%{$search}%")
                                ->orWhere('profiles.first_name', 'like', "%{$search}%")
                                ->orWhere('profiles.registered_by', 'like', "%{$search}%")
                                ->orWhere('profiles.first_name', 'like', "%{$search}%")
                                ->orWhere('profiles.last_name', 'like', "%{$search}%")
                                ->orWhere('profiles.dob', 'like', "%{$search}%")
                                ->orWhere('profiles.phone', 'like', "%{$search}%")
                                ->orWhere('profiles.city', 'like', "%{$search}%");
                        });
                    }

                    // Live filter for id_number
                    if ($idNumber = $request->get('id_number')) {
                        $query->where('users.id_number', 'like', "%{$idNumber}%");
                    }

                    // Live filter for registered_by
                    if ($registered_by = $request->get('registered_by')) {
                        $query->where('profiles.registered_by', 'like', "%{$registered_by}%");
                    }

                    // Live filter for first_name
                    if ($firstName = $request->get('first_name')) {
                        $query->where('profiles.first_name', 'like', "%{$firstName}%");
                    }

                    // Live filter for last_name
                    if ($lastName = $request->get('last_name')) {
                        $query->where('profiles.last_name', 'like', "%{$lastName}%");
                    }

                    if ($dob = $request->get('dob')) {
                        $query->where('profiles.dob', 'like', "%{$dob}%");
                    }

                    // Live filter for gender
                    if ($gender = $request->get('gender')) {
                        $query->where('profiles.gender', 'like', "%{$gender}%");
                    }

                    // Live filter for email
                    if ($email = $request->get('email')) {
                        $query->where('users.email', 'like', "%{$email}%");
                    }

                    // Live filter for phone
                    if ($phone = $request->get('phone')) {
                        $query->where('profiles.phone', 'like', "%{$phone}%");
                    }

                    // Live filter for city
                    if ($country = $request->get('country')) {
                        $query->where('profiles.country', 'like', "%{$country}%");
                    }

                    if ($county = $request->get('county')) {
                        $query->where('profiles.county', 'like', "%{$county}%");
                    }

                    if ($district = $request->get('district')) {
                        $query->where('profiles.district', 'like', "%{$district}%");
                    }

                    if ($address = $request->get('address')) {
                        $query->where('profiles.address', 'like', "%{$address}%");
                    }

                    if ($state = $request->get('state')) {
                        $query->where('profiles.state', 'like', "%{$state}%");
                    }

                    if ($zip = $request->get('zip')) {
                        $query->where('profiles.postal_code', 'like', "%{$zip}%");
                    }
                    // Live filter for date_of_issue from

                    // Column-specific date range filter
                    if ($from = $request->get('date_of_issue_from')) {
                        $fromDate = Carbon::createFromFormat('d/m/Y', $from)->startOfDay();
                        $query->where('users.created_at', '>=', $fromDate);
                    }

                    if ($to = $request->get('date_of_issue_to')) {
                        $toDate = Carbon::createFromFormat('d/m/Y', $to)->endOfDay();
                        $query->where('users.created_at', '<=', $toDate);
                    }

                    // Live filter for print status
                    if (($printStatus = $request->get('print_status')) !== null && $printStatus !== '') {
                        $query->where('profiles.is_card_printed', $printStatus);
                    }

                })
            // Enable ordering for all columns
                ->orderColumn('id_number', fn($query, $order) => $query->orderBy('users.id_number', $order))
                ->orderColumn('first_name', fn($query, $order) => $query->orderBy('profiles.first_name', $order))
                ->orderColumn('registered_by', fn($query, $order) => $query->orderBy('profiles.registered_by', $order))
                ->orderColumn('last_name', fn($query, $order) => $query->orderBy('profiles.last_name', $order))
                ->orderColumn('country', fn($query, $order) => $query->orderBy('profiles.country', $order))
                ->orderColumn('county', fn($query, $order) => $query->orderBy('profiles.county', $order))
                ->orderColumn('address', fn($query, $order) => $query->orderBy('profiles.address', $order))
                ->orderColumn('district', fn($query, $order) => $query->orderBy('profiles.district', $order))
                ->orderColumn('gender', fn($query, $order) => $query->orderBy('profiles.gender', $order))
                ->orderColumn('email', fn($query, $order) => $query->orderBy('users.email', $order))
                ->addColumn('is_card_printed', function ($data) {
                    if ($data->is_card_printed) {
                        return '<span class="badge bg-success">Printed</span>
                        <button onclick="updatePrintStatus(' . $data->id . ', 0)"
                        class="btn btn-sm btn-warning ms-2">Mark as Not Printed</button>';
                    } else {
                        return '<span class="badge bg-warning">Not Printed</span>
                        <button onclick="updatePrintStatus(' . $data->id . ', 1)"
                        class="btn btn-sm btn-success ms-2">Mark as Printed</button>';
                    }
                })

                ->orderColumn('date_of_issue', fn($query, $order) => $query->orderBy('users.created_at', 'asc'))
                ->rawColumns(['checkbox', 'is_card_printed', 'id_number', 'action'])
                ->make();
        }

        return view('backend.layouts.member.index', compact('type'));
    }

    public function create()
    {
        return view('backend.layouts.member.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'avatar'               => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'registered_by'        => 'nullable|string',
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'nullable|string|max:100',
            'dob'                  => 'required',
            'gender'               => 'required|in:male,female,other',
            'phone'                => 'required|numeric|unique:profiles',
            'email'                => 'nullable|string|email|max:150|unique:users',
            'country_of_residence' => 'required|string',
            'county'               => 'nullable|string',
            'district'             => 'nullable|string',
            'country'              => 'nullable|string',
            'address'              => 'nullable|string',
            'postal_code'          => 'nullable|string',
            'state'                => 'nullable|string',

        ]);

        try {
            $password = Str::random(8);

            if ($request->hasFile('avatar')) {
                $avatar = Helper::fileUpload(
                    $request->file('avatar'),
                    'members',
                    time() . '_' . getFileName($request->file('avatar'))
                );
            }

            $maxIdNumber = User::max('id_number') ?? 0;

            //  Auto-generate email if missing
            $email = $request->input('email');
            if (empty($email)) {
                $first  = strtolower(preg_replace('/\s+/', '', $request->input('first_name')));
                $last   = strtolower(preg_replace('/\s+/', '', $request->input('last_name')));
                $random = rand(10, 999); // 2–3 digits
                $email  = $first . '.' . $last . $random . '@gmail.com';
            }

            $user = User::create([
                'name'              => $request->input('first_name') . ' ' . $request->input('last_name'),
                'slug'              => 'cmc_' . (User::max('id') + 1),
                'email'             => $email,
                'password'          => Hash::make($password),
                'email_verified_at' => Carbon::now(),
                'avatar'            => $avatar ?? null,
                'id_number'         => $maxIdNumber + 1,
            ]);

            $user->profile()->create([
                'first_name'           => $request->input('first_name'),
                'last_name'            => $request->input('last_name'),
                'phone'                => $request->input('phone'),
                'gender'               => $request->input('gender'),
                'dob'                  => $request->input('dob'),
                'country_of_residence' => $request->input('country_of_residence') === 'Other' ? $request->input('country_of_residence') : $request->input('country_of_residence'),
                'country'              => $request->input('country_of_residence') === 'Other' ? $request->input('country') : $request->input('country_of_residence'),
                'county'               => $request->input('country_of_residence') === 'Liberia' ? $request->input('county') : null,
                'district'             => $request->input('country_of_residence') === 'Liberia' ? $request->input('district') : null,
                'city'                 => $request->input('city'),
                'address'              => $request->input('address'),
                'state'                => $request->input('state'),
                'postal_code'          => $request->input('postal_code'),
                'message'              => $request->input('message') ?? '',
                'content'              => $request->input('content') ?? '',
                'type'                 => $request->input('type') ?? 'member',
            ]);

            $user->assignRole('member');

            return redirect()->route('admin.member.index', ['type' => $user->profile->type])
                ->with('t-success', 'Member created successfully!');

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $member = User::with('profile')->findOrFail($id);
        return view('backend.layouts.member.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::with('profile')->findOrFail($id);
        return view('backend.layouts.member.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::with('profile')->findOrFail($id);
        $request->validate([
            'registered_by' => 'nullable|string',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'nullable|string|max:100',
            'dob'           => 'required',
            'gender'        => 'required|in:male,female,other',
            'phone'         => "required|numeric|unique:profiles,phone,{$user->profile->id}",
            'email' => "required|string|email|max:150|unique:users,email,{$user->id}",
            'country_of_residence' => 'required|string',
            'county'               => 'nullable|string',
            'district'             => 'nullable|string',
            'country'              => 'nullable|string',
            'address'              => 'nullable|string',
            'postal_code'          => 'nullable|string',
            'state'                => 'nullable|string',
            'avatar'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        try {
            // Update avatar if new file uploaded
            if ($request->hasFile('avatar')) {
                $avatar = Helper::fileUpload(
                    $request->file('avatar'),
                    'members',
                    time() . '_' . getFileName($request->file('avatar'))
                );
                $user->avatar = $avatar;
            }

            // Update basic user info
            $user->name  = $request->input('first_name') . ' ' . $request->input('last_name');
            $user->email = $request->input('email');
            $user->save();

            $profile = $user->profile;

            // Update always-relevant fields
            $profile->registered_by = $request->input('registered_by');
            $profile->first_name    = $request->input('first_name');
            $profile->last_name     = $request->input('last_name');
            $profile->phone         = $request->input('phone');
            $profile->gender        = $request->input('gender');
            $profile->dob           = $request->input('dob');
            $profile->type          = $request->input('type') ?? $profile->type;
            $profile->postal_code   = $request->input('postal_code');
            $profile->message       = $request->input('message') ?? '';
            $profile->content       = $request->input('content') ?? '';

            // Country of residence logic
            if ($request->input('country_of_residence') === 'Liberia') {
                // Liberia selected → clear Other fields
                $profile->country     = null;
                $profile->address     = null;
                $profile->state       = null;
                $profile->postal_code = null;

                // Set Liberia fields
                $profile->county   = $request->input('county');
                $profile->district = $request->input('district');
            } elseif ($request->input('country_of_residence') === 'Other') {
                // Other selected → clear Liberia fields
                $profile->county   = null;
                $profile->district = null;

                // Set Other fields
                $profile->country = $request->input('country');
                $profile->address = $request->input('address');
                $profile->state   = $request->input('state');
            }

            $profile->country_of_residence = $request->input('country_of_residence');
            $profile->city                 = $request->input('city');

            $profile->save();

            return redirect()->route('admin.member.index', ['type' => $profile->type])
                ->with('t-success', 'Member updated successfully!');

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {

            // user data
            $data = User::findOrFail($id);

            if ($data->avatar && file_exists(public_path($data->avatar))) {
                Helper::fileDelete(public_path($data->avatar));
            }

            $data->delete();
            return response()->json([
                'status'  => 't-success',
                'message' => 'Your action was successful!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 't-error',
                'message' => 'Your action was successful!',
            ]);
        }
    }

    public function updatePrintStatus(Request $request, $id)
    {

        $member                           = User::with('profile')->findOrFail($id);
        $member->profile->is_card_printed = $request->print_status;
        $member->profile->save();

        return response()->json(['message' => 'Print status updated successfully.' . $request->print_status]);
    }

    public function createImportCsv()
    {

        return view('backend.layouts.member.import');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx|max:2048',
        ]);

        try {
            Excel::import(new MembersImport, $request->file('file'));

            return redirect()->route('admin.member.index')
                ->with('t-success', 'Members Imported Successfully');
        } catch (\Exception $e) {
            return back()->with('t-error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar.*' => 'required|image',
            'avatar'   => 'required|array|min:1',
        ]);

        try {
            if ($request->hasFile('avatar')) {
                $avatars = [];
                foreach ($request->file('avatar') as $file) {
                    // Use original file name
                    $originalName = $file->getClientOriginalName();

                    // Move the file to public/user folder
                    $path = $file->move(public_path('uploads/user'), $originalName);

                    // Save the path relative to public folder
                    $avatars[] = 'user/' . $originalName;
                }

                return redirect()->back()
                    ->with('t-success', 'Avatars uploaded successfully!')
                    ->with('avatar_paths', $avatars);

            } else {
                return response()->json(['error' => 'No files uploaded'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
