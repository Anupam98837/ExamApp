<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Mail\WelcomeMail;

class MailController extends Controller
{
    /**
     * Send a welcome email (or arbitrary message) via JSON API.
     */
    public function sendMail(Request $request)
    {
        Log::info('API sendMail invoked', ['payload' => $request->all()]);

        // 1) Validate incoming fields
        $validator = Validator::make($request->all(), [
            'to'      => 'required|email',
            'message' => 'required|string',
            'subject' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('sendMail validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        Log::info('Validation passed', ['validated' => $data]);

        // 2) Prepare
        $to      = $data['to'];
        $msg     = $data['message'];
        $subject = $data['subject'] ?? 'New Mail';
        Log::info('Prepared email parameters', compact('to', 'subject'));

        // 3) Send
        try {
            Mail::to($to)->send(new WelcomeMail($msg, $subject));
            Log::info('Mail::send succeeded', ['to' => $to]);
        } catch (\Exception $e) {
            Log::error('Mail::send failed', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to send email.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        // 4) Return JSON success
        return response()->json([
            'status'  => 'success',
            'message' => "Email sent successfully to {$to}",
        ], 200);
    }
}

