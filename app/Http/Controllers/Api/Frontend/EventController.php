<?php

namespace App\Http\Controllers\Api\Frontend;

use Carbon\Carbon;
use App\Models\Event;
use App\Helpers\Helper;
use App\Helpers\Notify;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\EventRegister;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Event::query()->where('status', 'active');

        if ($request->filter === 'this_week') {
            $query->whereBetween('start_time', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        }

        if ($request->filter === 'this_month') {
            $query->whereMonth('start_time', Carbon::now()->month)
                ->whereYear('start_time', Carbon::now()->year);
        }

        $events = $query->orderBy('created_at', 'desc')->paginate(6);

        $events->getCollection()->transform(function ($item) {
            $item->description_for_flutter = strip_tags($item->description);
            return $item;
        });

        return $this->success($events, 'Events retrieved successfully', 200);
    }

    public function fourItem()
    {
        $events = Event::where('status', 'active')->orderBy('id', 'desc')->limit(4)->get();
        $data = [
            'events' => $events
        ];
        return Helper::jsonResponse(true, 'Events retrived successfully', 200, $data);
    }

    public function show($id)
    {
        $event = Event::where('id', $id)->first();

        $event->plain_content = strip_tags($event->description);

        return Helper::jsonResponse(true, 'Event Details retrived successfully', 200, $event);
    }

    public function register(Request $request, $eventId)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'email'      => 'nullable|email',
            'phone'      => 'required|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'message'    => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $user = auth('api')->user();
        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $event = Event::where('id', $eventId)->where('registration_enabled', 'yes')->first();

        if (!$event) {
            return $this->error([], 'Event not found or Registration is disabled for this event', 422);
        }

        $data = EventRegister::create([
            'user_id'    => $user->id,
            'event_id'   => $eventId,
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'occupation' => $request->occupation,
            'message'    => $request->message,
        ]);

        Notify::Firebase('Event', 'Event Register successfully', $user->id);
        Notify::inApp('Event', 'Event Register successfully', $user->id);

        return $this->success($data, 'Event Register successfully', 201);
    }
}
