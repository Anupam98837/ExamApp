<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;      // ← add this
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics (all-in-one function)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDashboard(Request $request)
{
    Log::info('Admin dashboard access attempt started');

    try {
        // ————————————— Token verification —————————————
        $header = $request->header('Authorization', '');
        if (!preg_match('/Bearer\s+(.+)$/i', trim($header), $m)) {
            Log::warning('Missing or malformed authorization token');
            return response()->json([
                'success' => false,
                'message' => 'Authorization token required',
            ], 401);
        }
        $plain = $m[1];
        $tokenRecord = DB::table('personal_access_tokens')
            ->where('token', hash('sha256', $plain))
            ->where('tokenable_type', 'admin')
            ->first();
        if (!$tokenRecord) {
            Log::warning('Invalid token attempt');
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
            ], 401);
        }
        $adminId = $tokenRecord->tokenable_id;
        Log::info('Admin authenticated', ['admin_id' => $adminId]);

        // ————————————— Load admin profile —————————————
        $admin = DB::table('admin')
            ->select('*')
            ->where('id', $adminId)
            ->first();

        // ————————————— Exams data —————————————
        $examsTotal   = DB::table('exams')->count();
        $examsPublic  = DB::table('exams')->where('is_public','yes')->count();
        $examsPrivate = DB::table('exams')->where('is_public','no')->count();
        $examTypes    = DB::table('exams')
                             ->select('pricing_model', DB::raw('count(*) as cnt'))
                             ->groupBy('pricing_model')
                             ->pluck('cnt','pricing_model');
        $recentExams  = DB::table('exams')
                             ->orderBy('created_at','desc')
                             ->limit(5)
                             ->get(['id','examName','created_at']);

        // ————————————— Students data —————————————
        $studentsTotal         = DB::table('students')->count();
        $studentsRegistered    = DB::table('students')
                                    ->where('status','active')
                                    ->where('is_approved',1)
                                    ->count();
        $studentsNotRegistered = $studentsTotal - $studentsRegistered;
        $studentsSubscribed    = DB::table('students')
                                    ->where('is_subscribed',1)
                                    ->count();
        $recentStudents        = DB::table('students')
                                    ->orderBy('created_at','desc')
                                    ->limit(5)
                                    ->get(['id','name','email','created_at']);

        // ————————————— Compile response —————————————
        $dashboardData = [
            'admin'    => $admin,
            'exams'    => [
                'total'    => $examsTotal,
                'public'   => $examsPublic,
                'private'  => $examsPrivate,
                'by_type'  => $examTypes,
                'recent'   => $recentExams,
            ],
            'students' => [
                'total'          => $studentsTotal,
                'registered'     => $studentsRegistered,
                'not_registered' => $studentsNotRegistered,
                'subscribed'     => $studentsSubscribed,
                'recent'         => $recentStudents,
            ],
            // … you can append mentors, quizzes, etc. here …
        ];

        return response()->json([
            'success' => true,
            'data'    => $dashboardData,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Dashboard error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to load dashboard data',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



    /**
 * Get student dashboard statistics
 *
 * @param Request $request
 * @return JsonResponse
 */
public function studentDashboard(Request $request): JsonResponse
{
    try {
        Log::info('Student dashboard access attempt started');

        // 1. Token verification
        $header = $request->header('Authorization', '');
        if (!preg_match('/Bearer\s+(.+)$/i', trim($header), $matches)) {
            Log::warning('Missing or malformed authorization token');
            return response()->json([
                'success' => false,
                'message' => 'Authorization token required'
            ], 401);
        }

        $plainToken = $matches[1];
        Log::debug('Token extracted', ['token' => substr($plainToken, 0, 10).'...']);

        $tokenRecord = DB::table('personal_access_tokens')
            ->where('token', hash('sha256', $plainToken))
            ->where('tokenable_type', 'student')
            ->first();

        if (!$tokenRecord) {
            Log::warning('Invalid token attempt');
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);
        }

        $studentId = $tokenRecord->tokenable_id;
        Log::info('Student authenticated', ['student_id' => $studentId]);

        // 2. Fetch student basic info
        $student = DB::table('students')
            ->where('id', $studentId)
            ->first(['id', 'name', 'email', 'phone', 'status', 'is_approved']);

        if (!$student) {
            Log::warning('Student record not found', ['student_id' => $studentId]);
            return response()->json([
                'success' => false,
                'message' => 'Student record not found'
            ], 404);
        }

        // 3. Build base response
        $dashboardData = [
            'student' => [
                'id'          => $student->id,
                'name'        => $student->name,
                'email'       => $student->email,
                'phone'       => $student->phone,
                'status'      => $student->status,
                'is_approved' => $student->is_approved,
            ],
            'stats' => [
                'total_exams_taken' => 0,
                'average_score'     => 0,
                'highest_score'     => 0,
                'total_attempts'    => 0,
                'recent_results'    => [],
            ],
        ];

        // 4. Gather exam results
        $results = DB::table('exam_results')
            ->where('student_id', $studentId)
            ->where('publish_to_student', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($results->isNotEmpty()) {
            $totalExams   = $results->count();
            $totalMarks   = $results->sum('marks_obtained');
            $highestScore = $results->max('marks_obtained');
            $totalAtt     = $results->sum('total_attempts');

            // Fetch total marks per exam
            $examIds    = $results->pluck('exam_id')->unique();
            $examTotals = DB::table('exam_questions')
                ->whereIn('exam_id', $examIds)
                ->select('exam_id', DB::raw('SUM(question_mark) as total_marks'))
                ->groupBy('exam_id')
                ->pluck('total_marks', 'exam_id');

            // Compute average percentage
            $sumPercent  = 0;
            $counted     = 0;
            foreach ($results as $r) {
                if (isset($examTotals[$r->exam_id]) && $examTotals[$r->exam_id] > 0) {
                    $sumPercent += ($r->marks_obtained / $examTotals[$r->exam_id]) * 100;
                    $counted++;
                }
            }

            $recentResults = $results->take(5)->map(function ($r) use ($examTotals) {
                $total   = $examTotals[$r->exam_id] ?? 0;
                $percent = $total > 0
                    ? round(($r->marks_obtained / $total) * 100, 2)
                    : 0;

                return [
                    'exam_id'        => $r->exam_id,
                    'marks_obtained' => $r->marks_obtained,
                    'total_marks'    => $total,
                    'percentage'     => $percent,
                    'attempt_number' => $r->total_attempts,
                    'date'           => $r->created_at,
                ];
            });

            $dashboardData['stats'] = [
                'total_exams_taken' => $totalExams,
                'average_score'     => $counted > 0 ? round($sumPercent / $counted, 2) : 0,
                'highest_score'     => $highestScore,
                'total_attempts'    => $totalAtt,
                'recent_results'    => $recentResults->toArray(),
            ];
        }

        Log::info('Student dashboard data compiled successfully', [
            'student_id' => $studentId,
            'exams_taken'=> $dashboardData['stats']['total_exams_taken']
        ]);

        return response()->json([
            'success' => true,
            'data'    => $dashboardData,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Student dashboard error: '.$e->getMessage(), [
            'exception' => $e,
            'trace'     => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to load student dashboard',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}