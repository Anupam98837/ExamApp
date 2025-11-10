<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /* ===============================================================
     |  POST /api/student/register
     * ===============================================================*/
    public function register(Request $request): JsonResponse
{
    // 1) Validate inputs
    $v = Validator::make($request->all(), [
        'name'               => 'required|string|max:255',
        'phone'              => 'required|string|max:20',
        'email'              => 'required|email',
        'password'           => 'required|string|min:8',
        'alternative_phone'  => 'nullable|string|max:20',
        'alternative_email'  => 'nullable|email',
        'whatsapp_no'        => 'nullable|string|max:20',
        'date_of_birth'      => 'nullable|date',
        'place_of_birth'     => 'nullable|string|max:255',
        'religion'           => 'nullable|string|max:100',
        'caste'              => 'nullable|string|max:100',
        'blood_group'        => 'nullable|string|max:10',
        'identity_type'      => 'nullable|in:Aadhar,Voter ID,PAN',
        'identity_details'   => 'nullable|string|max:20',
        'city'               => 'nullable|string|max:100',
        'po'                 => 'nullable|string|max:100',
        'ps'                 => 'nullable|string|max:100',
        'state'              => 'nullable|string|max:100',
        'country'            => 'nullable|string|max:100',
        'pin'                => 'nullable|string|max:6',
        'department'         => 'nullable|string|max:255',
        'course'             => 'nullable|string|max:255',
    ]);

    if ($v->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $v->errors()
        ], 422);
    }

    // 2) Collect sanitized data
    $data = $request->only([
        'name','phone','email',
        'alternative_phone','alternative_email',
        'whatsapp_no','date_of_birth','place_of_birth',
        'religion','caste','blood_group',
        'identity_type','identity_details',
        'city','po','ps','state','country','pin',
        'department','course'
    ]);
    $data['password']       = bcrypt($request->password);
    $data['plain_password'] = $request->password;

    // 3) Look for existing student
    $existing = DB::table('students')
        ->where('email', $data['email'])
        ->orWhere('phone', $data['phone'])
        ->first();

    if ($existing) {
        // 4a) Update existing record and add 3 more free attempts
        $update = $data;

        // Ensure status is active
        if ($existing->status !== 'active') {
            $update['status'] = 'active';
        }

        // Increment free_exam_attempts by 3
        $previous = is_numeric($existing->free_exam_attempts)
                  ? (int)$existing->free_exam_attempts
                  : 0;
        $update['free_exam_attempts'] = $previous + 3;

        // Back-fill created_at if missing
        if (empty($existing->created_at)) {
            $update['created_at'] = now();
        }
        // Always update updated_at
        $update['updated_at'] = now();

        DB::table('students')
            ->where('id', $existing->id)
            ->update($update);

        Log::info("Student #{$existing->id} profile updated – +3 free attempts", ['id' => $existing->id]);

        return response()->json([
            'success'    => true,
            'message'    => 'Profile updated. You have been granted 3 additional free exam attempts.',
            'student_id' => $existing->id,
            'free_exam_attempts' => $update['free_exam_attempts'],
        ], 200);
    } else {
        // 4b) Insert new with 3 free attempts
        $data['status']             = 'active';
        $data['free_exam_attempts'] = 3;
        $data['created_at']         = now();
        $data['updated_at']         = now();

        $id = DB::table('students')->insertGetId($data);

        Log::info("Student #{$id} registered – pending admin approval", ['id' => $id]);

        return response()->json([
            'success'    => true,
            'message'    => 'Registration successful. You have 3 free exam attempts.',
            'student_id' => $id,
            'free_exam_attempts' => 3,
        ], 201);
    }
}




    /* ===============================================================
     |  POST /api/student/login
     * ===============================================================*/
    public function login(Request $request): JsonResponse
{
    $request->validate([
        'login' => 'required|string', // Can be email or phone
    ]);

    // Determine if login is email or phone
    $isEmail = filter_var($request->login, FILTER_VALIDATE_EMAIL);
    $field   = $isEmail ? 'email' : 'phone';
    $name    = $isEmail ? 'Email' : 'Phone number';

    // Find student by email or phone
    $student = DB::table('students')
        ->where($field, $request->login)
        ->first();

    // If student exists
    if ($student) {
        $token = $this->generateToken($student->id);

        // Re-fetch up-to-date counts
        $fresh = DB::table('students')
            ->select('free_exam_attempts', 'current_attempt_count', 'is_subscribed')
            ->where('id', $student->id)
            ->first();

        $free     = (int) $fresh->free_exam_attempts;
        $used     = (int) $fresh->current_attempt_count;
        $infinite = ! empty($fresh->is_subscribed);
        $remaining = $infinite ? 'infinite' : max(0, $free - $used);

        return response()->json([
            'success'            => true,
            'message'            => 'Login successful',
            'student_token'      => $token,
            'student_id'         => $student->id,
            'email'              => $student->email,
            'phone'              => $student->phone,
            'name'               => $student->name,
            'free_exam_attempts' => $free,
            'used_attempts'      => $used,
            'remaining_attempts' => $remaining,
        ], 200);
    }

    // If student doesn't exist - create minimal record
    $id = DB::table('students')->insertGetId([
        $field                 => $request->login,
        'free_exam_attempts'   => 1,
        'current_attempt_count'=> 0,
        'created_at'           => now(),
        'updated_at'           => now(),
    ]);
    $student = DB::table('students')->find($id);
    $token   = $this->generateToken($student->id);

    return response()->json([
        'success'            => true,
        'message'            => "$name added. Proceed to exam",
        'student_token'      => $token,
        'tokenable_type'     => 'student',
        'student_id'         => $student->id,
        $field               => $student->{$field},
        'is_new'             => true,
        'free_exam_attempts' => 1,
        'used_attempts'      => 0,
        'remaining_attempts' => 1,
    ], 200);
}


    /* ===============================================================
     |  GET /api/student/me
     * ===============================================================*/
    /**
 * ===============================================================
 *  GET /api/student/me or GET /api/student/details?student_id={id}
 * ===============================================================
 */
public function getStudentDetails(Request $request): JsonResponse
{
    try {
        // 1) If a student_id is provided, fetch based on that:
        if ($request->filled('student_id')) {
            $v = Validator::make($request->all(), [
                'student_id' => 'required|integer|exists:students,id',
            ]);
            if ($v->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $v->errors(),
                ], 422);
            }

            $student = DB::table('students')->find($request->student_id);

            return response()->json([
                'success' => true,
                'message' => 'Student details fetched successfully by ID',
                'student' => $student,
            ], 200);
        }

        // 2) Otherwise, extract and validate the bearer token:
        if (! preg_match('/Bearer\s(\S+)/', $request->header('Authorization', ''), $m)) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided',
            ], 401);
        }
        $plainToken = $m[1];
        $hashed     = hash('sha256', $plainToken);

        $token = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'student')
            ->where('token', $hashed)
            ->first();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
            ], 401);
        }

        $student = DB::table('students')->find($token->tokenable_id);
        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student details fetched successfully by token',
            'student' => $student,
        ], 200);

    } catch (\Exception $e) {
        Log::error('getStudentDetails error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    /* ===============================================================
     |  POST /api/student/logout
     * ===============================================================*/
    public function logout(Request $request): JsonResponse
    {
        if (!preg_match('/Bearer\s(\S+)/', $request->header('Authorization', ''), $m)) {
            return response()->json(['success' => false, 'message' => 'Token not provided'], 401);
        }
        $deleted = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'student')
            ->where('token', hash('sha256', $m[1]))
            ->delete();

        return response()->json([
            'success' => (bool)$deleted,
            'message' => $deleted ? 'Logged out successfully' : 'Invalid token',
        ], $deleted ? 200 : 401);
    }

    /* ===============================================================
     |  Helpers
     * ===============================================================*/
    private function generateToken(int $studentId): string
    {
        $plain = bin2hex(random_bytes(40));
        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'student',
            'tokenable_id'   => $studentId,
            'name'           => 'auth_token',
            'token'          => hash('sha256', $plain),
            'abilities'      => json_encode(['*']),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        return $plain;
    }

    /**
 * Check if a student is eligible to take an exam
 *
 * POST /api/student/check-attempt
 *
 * @param  Request      $request
 * @return JsonResponse
 */
public function checkAttempt(Request $request): JsonResponse
{
    // 1) Validate input
    $v = Validator::make($request->all(), [
        'student_id' => 'required|integer|exists:students,id',
    ]);
    if ($v->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $v->errors(),
        ], 422);
    }

    // 2) Fetch student record
    $student = DB::table('students')
        ->select('free_exam_attempts', 'current_attempt_count', 'is_subscribed')
        ->where('id', $request->student_id)
        ->first();

    // Pull into variables
    $freeExamAttempts      = (int) $student->free_exam_attempts;
    $currentAttemptCount   = (int) $student->current_attempt_count;
    $isSubscribed          = !empty($student->is_subscribed);

    // 3) If subscribed → infinite
    if ($isSubscribed) {
        return response()->json([
            'success'               => true,
            'free_exam_attempts'    => $freeExamAttempts,
            'current_attempt_count' => $currentAttemptCount,
            'remaining_attempts'    => 'infinite',
            'message'               => 'Student is subscribed and has unlimited exam attempts.',
        ], 200);
    }

    // 4) Compute remaining
    $remaining = max(0, $freeExamAttempts - $currentAttemptCount);

    if ($remaining > 0) {
        return response()->json([
            'success'               => true,
            'free_exam_attempts'    => $freeExamAttempts,
            'current_attempt_count' => $currentAttemptCount,
            'remaining_attempts'    => $remaining,
            'message'               => "You have {$remaining} free attempt(s) remaining.",
        ], 200);
    }

    // 5) No attempts left
    return response()->json([
        'success'               => false,
        'free_exam_attempts'    => $freeExamAttempts,
        'current_attempt_count' => $currentAttemptCount,
        'remaining_attempts'    => 0,
        'message'               => 'No free exam attempts remaining.',
    ], 200);
}


/**
 * ===============================================================
 *  GET /api/students
 *  — Return every student record in the database
 * ===============================================================
 */
public function allStudents(Request $request): JsonResponse
{
    // If you need to restrict this to admins you could check a token here.
    // For now, we’ll just fetch everyone in reverse chronological order:

    $students = DB::table('students')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'success'  => true,
        'students' => $students,
    ], 200);
}



}
