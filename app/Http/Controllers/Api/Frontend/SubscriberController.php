<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\PdfDownloadMail;
use App\Mail\SubscribeMail;
use App\Models\PdfRequest;
use App\Models\Subscriber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|unique:subscribers,email'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Invalid email address",
    //             'data' => $validator->errors(),
    //             'code' => 422
    //         ]);
    //     }

    //     try {
    //         $subscriber = Subscriber::firstOrCreate(['email' => $request->input('email')]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => "Subscriber created successfully",
    //             'data' => $subscriber,
    //             'code' => 200
    //         ]);

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //             'data' => [],
    //             'code' => 500
    //         ]);
    //     }
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:subscribers,email'],
        ]);

        try {
            $subscriber = Subscriber::create([
                'email' => $validated['email'],
            ]);
            Mail::to($subscriber->email)->send(new SubscribeMail($subscriber));

            return response()->json([
                'success' => true,
                'message' => 'Subscriber created successfully.',
                'data'    => $subscriber,
            ], 201);
        } catch (\Throwable $e) {

            Log::error('Subscriber creation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
    public function remove($token)
    {

        try {
            $subscriber = Subscriber::where('email', $token)->first();
            if (!$subscriber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscriber not found.',
                ], 404);
            }
            $subscriber->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subscriber removed successfully.',
                'data'    => $subscriber,
            ], 201);
        } catch (\Throwable $e) {

            Log::error('Subscriber removal failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function pdfGuide(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:pdf_requests,email'],
        ]);

        try {
            $subscriber = PdfRequest::create([
                'email' => $validated['email'],
            ]);
            // $filePath = public_path('uploads/pdf/download.pdf');
            // $filePath = "https://drive.google.com/file/d/1nz5mmFxLwzlK-6Z3eTwt17WtQj9qQN7-/view";

            $fileName = "Organ_Sciences_Customer_Handout";
            // $fileSize = filesize($filePath);
// 
            // function formatSize($bytes)
            // {
            //     if ($bytes >= 1048576) {
            //         return round($bytes / 1048576, 2) . ' MB';
            //     }
            //     return round($bytes / 1024, 2) . ' KB';
            // }

            // $fileSizeFormatted = formatSize(filesize($filePath));
            // $downloadLink = asset('uploads/pdf/download.pdf');
            $downloadLink = "https://drive.google.com/file/d/1nz5mmFxLwzlK-6Z3eTwt17WtQj9qQN7-/view";
            
            Mail::to($subscriber->email)->send(new PdfDownloadMail($subscriber, $downloadLink, $fileName, "225 KB"));


            return response()->json([
                'success' => true,
                'message' => 'PDF Link Send Successfully.',
                'data'    => $subscriber,
            ], 201);
        } catch (\Throwable $e) {

            Log::error('PDF Request creation failed', [

                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
