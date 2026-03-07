<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Mail\Message;
use App\Models\CMS;
use App\Models\EmailLog;
use App\Models\Subscriber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class EmailLogController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'to_email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);
            $validatedData['from_email'] = config('mail.from.address'); // Set the sender email from config
            $validatedData['status'] = 'sent'; // You can set this based on the actual email sending result
            $validatedData['others'] = json_encode([
                'subscriber_id' => $request->input('subscriber_id'),
                // Add any other relevant information here
            ]);

            // Create a new email log entry
            $emailLog = EmailLog::create($validatedData);
            // send message to email
            Mail::to($emailLog->to_email)->send(new Message($emailLog->subject, $emailLog->message));

            return redirect()->back()->with('success', 'Email sent successfully.');
        } catch (Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send email. Please try again later.');
            // Optionally, you can return a JSON response for AJAX
            // return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $emailLog = EmailLog::findOrFail($id);
            $emailLog->delete();
            return new JsonResponse(['success' => true, 'message' => 'Email log deleted successfully.']);
        } catch (Exception $e) {
            Log::error('Failed to delete email log: ' . $e->getMessage());
            return new JsonResponse(['success' => false, 'message' => 'Failed to delete email log. Please try again later.'], 500);
        }
    }
}
