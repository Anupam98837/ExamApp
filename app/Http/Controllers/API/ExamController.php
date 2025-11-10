<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Keep this one
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Mail\ExamResultMail;
use Illuminate\Support\Facades\Mail;

class ExamController extends Controller
{
    /* ===============================================================
     |  POST  /api/exams   – Create a new exam
     * ===============================================================*/
    public function store(Request $request)
    {
        Log::info('Exam creation started', ['data' => $request->all()]);

        $rules = [
            'admin_id'            => 'required|integer|exists:admin,id',

            'examName'            => 'required|string|max:255',
            'examDescription'     => 'required|string',
            'examImg'             => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Instructions'        => 'nullable|string',

            'is_public'           => 'required|in:yes,no',

            'pricing_model'       => 'nullable|in:free,paid',
            'regular_price'       => 'required_if:pricing_model,paid|nullable|numeric|min:0',
            'sale_price'          => 'nullable|numeric|min:0',

            'result_set_up_type'  => 'nullable|in:Immediately,Now,Schedule',
            'result_release_date' => 'nullable|date|required_if:result_set_up_type,Schedule',

            /* quiz-level meta now living on the exam itself */
            'totalTime'           => 'nullable|integer|min:1',
            'total_attempts'      => 'nullable|integer|min:1',
            'total_questions'     => 'nullable|integer|min:1',

            'associated_course'   => 'nullable|string|max:255',
            'associated_department'=> 'nullable|string|max:255',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            Log::warning('Exam creation validation failed', ['errors' => $v->errors()]);
            return response()->json(['success'=>false,'errors'=>$v->errors()], 422);
        }

        /* ---------- optional image ---------- */
        $imgPath = null;
        if ($request->hasFile('examImg')) {
            $file     = $request->file('examImg');
            $filename = 'exam_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/exam'), $filename);
            $imgPath  = "assets/images/exam/{$filename}";
        } else {
            // Default exam image
            $imgPath = "assets/images/exam/exam.jpg";
        }

        /* ---------- insert ---------- */
        $id = DB::table('exams')->insertGetId([
            'admin_id'            => $request->admin_id,

            'examName'            => $request->examName,
            'examDescription'     => $request->examDescription,
            'examImg'             => $imgPath,
            'Instructions'        => $request->Instructions,

            'is_public'           => $request->is_public,

            'pricing_model'       => $request->pricing_model ?? 'free',
            'regular_price'       => $request->pricing_model === 'paid' ? $request->regular_price : null,
            'sale_price'          => $request->pricing_model === 'paid' ? $request->sale_price   : null,

            'result_set_up_type'  => $request->result_set_up_type ?? 'Immediately',
            'result_release_date' => $request->result_release_date,

            'totalTime'           => $request->totalTime,
            'total_attempts'      => $request->total_attempts ?? 1,
            'total_questions'     => $request->total_questions,

            'associated_course'   => $request->associated_course,
            'associated_department'=> $request->associated_department,

            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        Log::info('Exam created', ['exam_id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Exam created successfully.',
            'examId'  => $id,
        ], 201);
    }

    /* ===============================================================
     |  GET  /api/exams         – list
     |  GET  /api/exams/{id}    – show single
     * ===============================================================*/
   /**
 * GET  /api/exams – list (enriched exactly like firstExamsByCourse)
 */
/**
 * GET /api/exams
 *
 * - Guests (no Bearer token):     1 exam per course
 * - Students (valid token):       based on free_exam_attempts:
 *      • 1  → 1 per course
 *      • 4  → 3 per course
 *      • ≥5 → all exams
 * - Admins (X-User-Role=admin):   all exams
 */
public function index(Request $request): JsonResponse
{
    Log::info('index: started');

    // 1) Determine role & student (if any)
    $userRole  = $request->header('X-User-Role', 'guest');
    $authHeader = $request->header('Authorization', '');
    $student   = null;

    if (preg_match('/Bearer\s+(\S+)/', $authHeader, $m)) {
        $plain  = $m[1];
        $hashed = hash('sha256', $plain);
        $student = DB::table('personal_access_tokens')
                     ->where('tokenable_type', 'student')
                     ->where('token', $hashed)
                     ->join('students', 'personal_access_tokens.tokenable_id', '=', 'students.id')
                     ->select('students.*')
                     ->first();
        if ($student) {
            Log::info('index: student identified', ['student_id' => $student->id]);
        } else {
            Log::warning('index: invalid student token', ['token_hash' => $hashed]);
        }
    }

    // 2) Fetch raw exams ordered by course & creation
    $allExams = DB::table('exams')
                  ->orderBy('associated_course')
                  ->orderBy('created_at')
                  ->get();

    // 3) Determine per-course limit
    if ($userRole === 'admin') {
        $perCourse = PHP_INT_MAX;   // admin sees all
    } elseif ($student) {
        $free = (int)$student->free_exam_attempts;
        if ($free === 1) {
            $perCourse = 1;
        } elseif ($free > 3 && $free < 4) {
            $perCourse = 3;
        } else {
            $perCourse = PHP_INT_MAX; // free ≥5
        }
    } else {
        // guest
        $perCourse = 1;
    }

    // 4) Group & slice the exams
    $selected = collect();
    $allExams
        ->groupBy('associated_course')
        ->each(function($group) use ($perCourse, $selected) {
            // take up to $perCourse from each course
            $selected->push(
                $group->slice(0, $perCourse)
            );
        });
    // flatten back into a single collection
    $rawExams = $selected->flatten(1);

    Log::info('index: selected exams after per-course limit', [
        'per_course' => $perCourse,
        'count'      => $rawExams->count(),
    ]);

    // 5) Enrich each exam (pricing, counts, discount, user_attempt_count)
    $results = $rawExams->map(function($exam) use ($student) {
        $examArr = (array) $exam;

        // pricing normalization
        $regular  = (float) ($exam->regular_price ?? 0);
        $sale     = (float) ($exam->sale_price   ?? $regular);
        $discount = $regular > 0
            ? (int) round((($regular - $sale) / $regular) * 100)
            : 0;

        // counts
        $questionCount = DB::table('exam_questions')
            ->where('exam_id', $exam->id)
            ->count();
        $studentCount = DB::table('exam_results')
            ->where('exam_id', $exam->id)
            ->distinct('student_id')
            ->count('student_id');

        $examArr['regular_price']    = $regular;
        $examArr['sale_price']       = $sale;
        $examArr['discount_percent'] = $discount;
        $examArr['question_count']   = $questionCount;
        $examArr['student_count']    = $studentCount;

        // user-specific attempt count
        if ($student) {
            $attempts = DB::table('exam_results')
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->count();
            $examArr['user_attempt_count'] = $attempts;
        }

        return $examArr;
    });

    // 6) Return
    Log::info('index: returning response', ['total' => $results->count()]);
    return response()->json([
        'success' => true,
        'exams'   => $results,
    ], 200);
}

/**
 * GET  /api/exams/{examId}/has-attempts
 *
 * Check whether the authenticated student still has attempts left on a given exam.
 */
public function hasAttempts(Request $request, int $examId): JsonResponse
{
    // 1) Authenticate student via Bearer token
    if (! preg_match('/Bearer\s+(\S+)/', $request->header('Authorization', ''), $m)) {
        return response()->json([
            'success' => false,
            'message' => 'Token not provided',
        ], 401);
    }
    $plain  = $m[1];
    $hashed = hash('sha256', $plain);

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
    $studentId = $token->tokenable_id;

    // 2) Fetch the exam to get its per-exam limit
    $exam = DB::table('exams')
        ->select('total_attempts')
        ->where('id', $examId)
        ->first();
    if (! $exam) {
        return response()->json([
            'success' => false,
            'message' => 'Exam not found',
        ], 404);
    }

    // 3) Count how many times the student has already submitted
    $used = DB::table('exam_results')
        ->where('exam_id', $examId)
        ->where('student_id', $studentId)
        ->count();

    // 4) Compare and respond
    $hasAttempts = $used < ($exam->total_attempts ?? 1);

    return response()->json([
        'success'      => true,
        'has_attempts' => $hasAttempts,          // true  → “yes”
        'used'         => $used,
        'allowed'      => $exam->total_attempts, // for clarity
    ], 200);
}




    /* alias so `/show/{id}` still works if you keep that route */
    public function show(int $id)
    {
        Log::info('Fetching single exam', ['id' => $id]);

        $exam = DB::table('exams')->find($id);

        return $exam
            ? response()->json(['success' => true, 'exam' => $exam], 200)
            : response()->json(['success' => false, 'message' => 'Exam not found'], 404);
    }

    /**
 * GET /api/exams/first-by-course
 *
 * Return the first (earliest created) exam for each associated course,
 * enriched with pricing, counts, and (if a valid student token is provided)
 * the number of times that student has attempted each exam.
 */
public function firstExamsByCourse(Request $request): JsonResponse
{
    Log::info('firstExamsByCourse: started');

    // 1) Extract student ID from Bearer token (if present)
    $studentId   = null;
    $authHeader  = $request->header('Authorization', '');
    Log::info('firstExamsByCourse: Authorization header', ['header' => $authHeader]);
    if (preg_match('/Bearer\s+(\S+)/', $authHeader, $m)) {
        $plain  = $m[1];
        $hashed = hash('sha256', $plain);
        Log::info('firstExamsByCourse: token hashed', ['hash' => $hashed]);
        $token  = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'student')
            ->where('token', $hashed)
            ->first();
        if ($token) {
            $studentId = $token->tokenable_id;
            Log::info('firstExamsByCourse: student identified', ['student_id' => $studentId]);
        } else {
            Log::warning('firstExamsByCourse: invalid student token', ['token_hash' => $hashed]);
        }
    } else {
        Log::info('firstExamsByCourse: no Bearer token provided');
    }

    // 2) Subquery: earliest created_at per course
    Log::info('firstExamsByCourse: building subquery for earliest exam by course');
    $sub = DB::table('exams')
        ->selectRaw('associated_course, MIN(created_at) AS first_date')
        ->whereNotNull('associated_course')
        ->groupBy('associated_course');
    Log::info('firstExamsByCourse: subquery built');

    // 3) Join to get full exam rows
    Log::info('firstExamsByCourse: executing main join query');
    $firstExams = DB::table('exams as e')
        ->joinSub($sub, 'firsts', function ($join) {
            $join->on('e.associated_course', '=', 'firsts.associated_course')
                 ->on('e.created_at', '=', 'firsts.first_date');
        })
        ->select('e.*')
        ->orderBy('e.associated_course')
        ->get();
    Log::info('firstExamsByCourse: fetched first exams', ['count' => $firstExams->count()]);

    // 4) Enrich each exam record
    Log::info('firstExamsByCourse: enriching exam records');
    $results = $firstExams->map(function($exam) use ($studentId) {
        Log::info('firstExamsByCourse: processing exam', [
            'exam_id'            => $exam->id,
            'associated_course'  => $exam->associated_course
        ]);

        $examArr = (array) $exam;

        // pricing normalization
        $regular  = (float) ($exam->regular_price ?? 0);
        $sale     = (float) ($exam->sale_price   ?? $regular);
        $discount = $regular > 0
            ? (int) round((($regular - $sale) / $regular) * 100)
            : 0;

        // counts
        $questionCount = DB::table('exam_questions')
            ->where('exam_id', $exam->id)
            ->count();
        $studentCount = DB::table('exam_results')
            ->where('exam_id', $exam->id)
            ->distinct('student_id')
            ->count('student_id');

        $examArr['regular_price']    = $regular;
        $examArr['sale_price']       = $sale;
        $examArr['discount_percent'] = $discount;
        $examArr['question_count']   = $questionCount;
        $examArr['student_count']    = $studentCount;

        // user-specific attempt count
        if ($studentId) {
            $attempts = DB::table('exam_results')
                ->where('exam_id', $exam->id)
                ->where('student_id', $studentId)
                ->count();
            $examArr['user_attempt_count'] = $attempts;
            Log::info('firstExamsByCourse: user attempt count added', [
                'exam_id'           => $exam->id,
                'student_id'        => $studentId,
                'user_attempt_count'=> $attempts
            ]);
        }

        return $examArr;
    });
    Log::info('firstExamsByCourse: enrichment complete');

    Log::info('firstExamsByCourse: returning response', ['total' => $results->count()]);
    return response()->json([
        'success' => true,
        'exams'   => $results,
    ], 200);
}



    // add this at the bottom of the class:

    /**
     * GET /api/exams/suggested
     * 
     * Suggest exams based on the student's best past performance.
     */
    public function suggested(Request $request): JsonResponse
    {
        // 1) extract Bearer token
        if (! preg_match('/Bearer\s(\S+)/', $request->header('Authorization', ''), $m)) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided'
            ], 401);
        }
        $plain = $m[1];
        $hashed = hash('sha256', $plain);

        // 2) find the student token record
        $token = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'student')
            ->where('token', $hashed)
            ->first();
        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }
        $studentId = $token->tokenable_id;

        // 3) find their top past result
        $best = DB::table('exam_results')
            ->where('student_id', $studentId)
            ->orderBy('marks_obtained', 'desc')
            ->first();
        if (! $best) {
            return response()->json([
                'success' => true,
                'message' => 'No past exam results found, so no suggestions available',
                'exams'   => []
            ], 200);
        }

        // 4) lookup that exam's associated_course
        $assocCourse = DB::table('exams')
            ->where('id', $best->exam_id)
            ->value('associated_course');
        if (! $assocCourse) {
            return response()->json([
                'success' => true,
                'message' => 'No course metadata on your best exam, so no suggestions available',
                'exams'   => []
            ], 200);
        }

        // 5) fetch public exams in the same course
        $suggested = DB::table('exams')
            ->where('associated_course', $assocCourse)
            ->where('is_public', 'yes')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Suggested exams for course “{$assocCourse}”",
            'exams'   => $suggested
        ], 200);
    }

    


    /* ===============================================================
     |  PATCH /api/exams/{id}   – update
     * ===============================================================*/
    public function update(Request $request, $id)
{
    Log::info('Exam update: start', ['exam_id' => $id, 'payload' => $request->all()]);

    // Check existence
    $exam = DB::table('exams')->where('id', $id)->first();
    if (! $exam) {
        Log::warning('Exam update: not found', ['exam_id' => $id]);
        return response()->json(['success' => false, 'message' => 'Exam not found'], 404);
    }

    // Validation rules
    $rules = [
        'admin_id'             => 'sometimes|integer|exists:admin,id',
        'examName'             => 'sometimes|required|string|max:255',
        'examDescription'      => 'sometimes|required|string',
        'examImg'              => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        'Instructions'         => 'sometimes|nullable|string',
        'is_public'            => 'sometimes|required|in:yes,no',
        'pricing_model'        => 'sometimes|in:free,paid',
        'regular_price'        => 'required_if:pricing_model,paid|nullable|numeric|min:0',
        'sale_price'           => 'nullable|numeric|min:0',
        'result_set_up_type'   => 'sometimes|in:Immediately,Now,Schedule',
        'result_release_date'  => 'sometimes|date|required_if:result_set_up_type,Schedule',
        'totalTime'            => 'sometimes|integer|min:1',
        'total_attempts'       => 'sometimes|integer|min:1',
        'total_questions'      => 'sometimes|integer|min:1',
        'associated_course'    => 'sometimes|nullable|string|max:255',
        'associated_department'=> 'sometimes|nullable|string|max:255',
    ];

    Log::info('Exam update: validating', ['exam_id' => $id, 'rules' => $rules]);
    $v = Validator::make($request->all(), $rules);
    if ($v->fails()) {
        Log::warning('Exam update: validation failed', [
            'exam_id' => $id,
            'errors'  => $v->errors()->toArray(),
        ]);
        return response()->json(['success' => false, 'errors' => $v->errors()], 422);
    }

    $data = $v->validated();
    Log::info('Exam update: validation passed', ['exam_id' => $id, 'validated' => $data]);

    // Handle new image
    if ($request->hasFile('examImg')) {
        Log::info('Exam update: handling new image', ['exam_id' => $id]);
        $file     = $request->file('examImg');
        $filename = 'exam_' . time() . '.' . $file->getClientOriginalExtension();
        $destination = public_path('assets/images/exam');
        $file->move($destination, $filename);
        $data['examImg'] = "assets/images/exam/{$filename}";
        Log::info('Exam update: image saved', ['exam_id' => $id, 'path' => $data['examImg']]);
    }

    $data['updated_at'] = now();
    Log::info('Exam update: updating database', [
        'exam_id' => $id,
        'fields'  => array_keys($data),
    ]);

    DB::table('exams')->where('id', $id)->update($data);

    Log::info('Exam update: completed successfully', ['exam_id' => $id]);

    return response()->json([
        'success' => true,
        'message' => 'Exam updated successfully.',
    ], 200);
}


    /* ===============================================================
     |  DELETE /api/exams/{id}  – delete
     * ===============================================================*/
    public function destroy(Request $request, $id)
    {
        $deleted = DB::table('exams')->where('id',$id)->delete();

        return $deleted
            ? response()->json(['success'=>true,'message'=>'Exam deleted'], 200)
            : response()->json(['success'=>false,'message'=>'Exam not found'], 404);
    }

    /* ===============================================================
     |  PATCH /api/exams/{id}/result-setup  – change release rules
     * ===============================================================*/
    public function updateResultSetUpType(Request $request, $id)
    {
        $exam = DB::table('exams')->where('id',$id)->first();
        if (! $exam) {
            return response()->json(['success'=>false,'message'=>'Exam not found'], 404);
        }

        $rules = [
            'admin_id'            => 'required|integer|exists:admin,id',
            'result_set_up_type'  => 'required|in:Immediately,Now,Schedule',
            'result_release_date' => 'nullable|date|required_if:result_set_up_type,Schedule',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['success'=>false,'errors'=>$v->errors()], 422);
        }

        DB::table('exams')->where('id',$id)->update([
            'result_set_up_type'  => $request->result_set_up_type,
            'result_release_date' => $request->result_set_up_type === 'Schedule'
                                    ? $request->result_release_date
                                    : null,
            'updated_at'          => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Result set-up type updated.',
        ], 200);
    }
    public function questionsWithAnswers(int $examId): JsonResponse
{
    $rows = DB::table('exam_questions as q')
        ->join('exam_question_answers as a', 'a.belongs_question_id', '=', 'q.id')
        ->where('q.exam_id', $examId)
        ->orderBy('q.question_order')
        ->orderBy('a.answer_order')
        ->select([
            'q.id                as question_id',
            'q.question_title',
            'q.question_description',
            'q.answer_explanation',
            'q.question_type',
            'q.question_mark',
            'q.question_settings',
            'q.question_order',

            'a.id                as answer_id',
            'a.belongs_question_id',
            'a.belongs_question_type',
            'a.answer_title',
            'a.is_correct',
            'a.image_id',
            'a.answer_two_gap_match',
            'a.answer_view_format',
            'a.answer_settings',
            'a.answer_order',
        ])
        ->get();

    $questions = [];
    foreach ($rows as $r) {
        $qid = $r->question_id;
        if (!isset($questions[$qid])) {
            $questions[$qid] = [
                'question_id'                 => $qid,
                'question_title'              => $r->question_title,
                'question_description'        => $r->question_description,
                'answer_explanation'          => $r->answer_explanation,
                'question_type'               => $r->question_type,
                'question_mark'               => $r->question_mark,
                'question_settings'           => $r->question_settings,
                'question_order'              => $r->question_order,
                'answers'                     => [],
                'has_multiple_correct_answer' => 0,
            ];
        }
        $questions[$qid]['answers'][] = [
            'answer_id'            => $r->answer_id,
            'belongs_question_id'  => $r->belongs_question_id,
            'belongs_question_type'=> $r->belongs_question_type,
            'answer_title'         => $r->answer_title,
            'is_correct'           => (bool) $r->is_correct,
            'image_id'             => $r->image_id,
            'answer_two_gap_match' => $r->answer_two_gap_match,
            'answer_view_format'   => $r->answer_view_format,
            'answer_settings'      => $r->answer_settings,
            'answer_order'         => $r->answer_order,
        ];
        if ($r->is_correct) {
            $questions[$qid]['has_multiple_correct_answer']++;
        }
    }

    foreach ($questions as &$q) {
        $q['has_multiple_correct_answer'] = $q['has_multiple_correct_answer'] > 1;
    }

    return response()->json([
        'success'   => true,
        'questions' => array_values($questions),
    ], 200);
}


/* ===============================================================
 |  POST /api/exam/{examId}/submit
 |  Save attempt, score it, handle result-release rules
 * ===============================================================*/
public function submit(Request $request, int $examId): JsonResponse
{
    /* 1) Validate input */
    $v = Validator::make($request->all(), [
        'student_id'            => 'required|integer|exists:students,id',
        'answers'               => 'required|array|min:1',
        'answers.*.question_id' => 'required|integer|exists:exam_questions,id',
        'answers.*.selected'    => 'nullable',
    ]);
    if ($v->fails()) {
        return response()->json(['success' => false, 'errors' => $v->errors()], 422);
    }
    $answers = $v->validated()['answers'];

    /* 2) Ensure at least one answer */
    $hasAnswer = collect($answers)->contains(fn($a) =>
        is_array($a['selected']) ? count($a['selected']) > 0 : !is_null($a['selected'])
    );
    if (!$hasAnswer) {
        return response()->json(['success' => false, 'message' => 'At least one answer is required'], 422);
    }

    /* 3) Score the submission (kept for persistence) */
    $totalMarks = 0;
    foreach ($answers as $ans) {
        $qid = $ans['question_id'];
        $sel = $ans['selected'];

        $correctIds = DB::table('exam_question_answers')
            ->where('belongs_question_id', $qid)
            ->where('is_correct', 1)
            ->pluck('id')
            ->sort()->values()->all();

        $isCorrect = is_array($sel)
            ? (sort($sel) | true) && ($sel === $correctIds)
            : ((int)$sel === ($correctIds[0] ?? null));

        if ($isCorrect) {
            $totalMarks += DB::table('exam_questions')->where('id', $qid)->value('question_mark') ?? 1;
        }
    }

    /* 4) Determine publish rules */
    $exam = DB::table('exams')
        ->where('id', $examId)
        ->first(['result_set_up_type', 'result_release_date','pricing_model', ]);
    $publish = $exam->result_set_up_type === 'Immediately'
             || ($exam->result_set_up_type === 'Schedule'
                 && Carbon::now()->gte(Carbon::parse($exam->result_release_date)))
             ? 1 : 0;

    /* 5) Count previous attempts (for recording) */
    $prev = DB::table('exam_results')
        ->where('student_id', $request->student_id)
        ->where('exam_id', $examId)
        ->count();

    /* 6) Persist this exam result */
    DB::table('exam_results')->insert([
        'student_id'         => $request->student_id,
        'exam_id'            => $examId,
        'marks_obtained'     => $totalMarks,
        'total_attempts'     => $prev + 1,
        'students_answer'    => json_encode($answers),
        'publish_to_student' => $publish,
        'created_at'         => now(),
        'updated_at'         => now(),
    ]);

    /* 7) Increment student’s own attempt counter */
    if ($exam->pricing_model !== 'free') {
        DB::table('students')
            ->where('id', $request->student_id)
            ->increment('current_attempt_count', 1);
    }

    /* 8) Return only success message */
    return response()->json([
        'success' => true,
        'message' => 'Exam submitted successfully.'
    ], 201);
}

/**
 * GET /api/exams/{id}/payment-info
 * 
 * Return full exam details plus pricing info (regular_price, sale_price, discount_percent)
 */
public function paymentInfo(int $id): JsonResponse
{
    Log::info('paymentInfo: fetching exam', ['exam_id' => $id]);

    // 1) Fetch the exam
    $exam = DB::table('exams')->where('id', $id)->first();
    if (! $exam) {
        Log::warning('paymentInfo: exam not found', ['exam_id' => $id]);
        return response()->json([
            'success' => false,
            'message' => 'Exam not found'
        ], 404);
    }

    // 2) Normalize prices
    $regular = (float) ($exam->regular_price ?? 0);
    $sale    = (float) ($exam->sale_price   ?? $regular);
    $discount = $regular > 0
        ? (int) round((($regular - $sale) / $regular) * 100)
        : 0;

    // 3) Compute question & student counts
    $questionCount = DB::table('exam_questions')
        ->where('exam_id', $id)
        ->count();
    $studentCount = DB::table('exam_results')
        ->where('exam_id', $id)
        ->distinct('student_id')
        ->count('student_id');

    // 4) Merge into payload
    $data = (array) $exam;
    $data['regular_price']    = $regular;
    $data['sale_price']       = $sale;
    $data['discount_percent'] = $discount;
    $data['question_count']   = $questionCount;
    $data['student_count']    = $studentCount;

    Log::info('paymentInfo: returning response', ['exam' => $data]);

    return response()->json([
        'success' => true,
        'exam'    => $data,
    ], 200);
}






public function getStudentsResults(Request $request)
{
    Log::info('getAllStudentsResults: fetch initiated', ['request' => $request->all()]);

    // Check if student_id parameter is provided
    $studentId = $request->query('student_id');

    // 1) Build base query
    $query = DB::table('exam_results as er')
        ->join('students as s', 's.id', '=', 'er.student_id')
        ->join('exams as e', 'e.id', '=', 'er.exam_id')
        ->select([
            'er.id',
            'er.student_id',
            's.name',
            's.email',
            's.phone',
            'er.exam_id',
            'e.examName',
            'er.publish_to_student',
            'e.total_questions',
            'er.marks_obtained',
            'er.total_attempts',
            'er.students_answer',
            'er.created_at',
            'er.updated_at',
        ])
        ->orderBy('er.created_at', 'desc');

    // Filter by student_id if provided
    if ($studentId) {
        Log::info('Filtering results by student_id', ['student_id' => $studentId]);
        $query->where('er.student_id', $studentId);
    }

    // Get results
    $results = $query->get();

    // 2) Enhance each row with attempted/not attempted counts
    $enhanced = $results->map(function($row) {
        $answers = json_decode($row->students_answer, true) ?: [];

        // count how many were actually answered
        $attempted = collect($answers)
            ->filter(fn($ans) => isset($ans['selected']) && $ans['selected'] !== null)
            ->count();

        // use exam's total_questions (fallback to 0)
        $totalQuestions = $row->total_questions ?? 0;

        // never go below zero
        $notAttempted = max(0, $totalQuestions - $attempted);

        return [
            'id' => $row->id,
            'student_id' => $row->student_id,
            'name' => $row->name,
            'email' => $row->email,
            'phone' => $row->phone,
            'exam_id' => $row->exam_id,
            'examName' => $row->examName,
            'publish_to_student' => $row->publish_to_student,
            'total_questions' => $totalQuestions,
            'marks_obtained' => $row->marks_obtained,
            'total_attempts' => $row->total_attempts,
            'attempted_questions' => $attempted,
            'not_attempted_questions' => $notAttempted,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    });

    Log::info('Fetched exam_results records', [
        'count' => $enhanced->count(),
        'filtered_by_student' => $studentId ? true : false
    ]);

    return response()->json([
        'success' => true,
        'count' => $enhanced->count(),
        'filtered_by_student' => $studentId ? $studentId : false,
        'results' => $enhanced,
    ], 200);
}


/* ===============================================================
 |  GET /api/exam-results/{id}/answer-sheet (HTML stream)
 |  Re-uses same HTML template; now pulls from exam_* tables
 * ===============================================================*/
public function answerSheetDownload(int $id)
{
    /* ---------- 0) Locate submission ---------- */
    $submission = DB::table('exam_results')
        ->where('id', $id)
        ->first() ?? abort(404, 'Submission not found');

    /* helper: first existing column in table */
    $col = static function (string $table, array $names) {
        foreach ($names as $n) {
            if (Schema::hasColumn($table, $n)) return $n;
        }
        return null;
    };

    /* ---------- 1) Student meta ---------- */
    $student = DB::table('students')->where('id', $submission->student_id)->first();

    $studentName = $student?->{$col('students', ['name','full_name','student_name'])} ?? '-';
    $department  = $student?->{$col('students', ['department','dept','branch'])}     ?? '-';
    $year        = $student?->{$col('students', ['year','current_year'])}           ?? '-';
    $semester    = $student?->{$col('students', ['semester','sem'])}                ?? '-';

    /* ---------- 2) Exam title ---------- */
    $examName = DB::table('exams')
        ->where('id', $submission->exam_id)
        ->value($col('exams',['examName','title','name'])) ?? '-';

    /* ---------- 3) Questions ---------- */
    $questions = DB::table('exam_questions')
        ->select(
            'id as question_id','question_order','question_title',
            'question_mark','question_type',
            DB::raw('(
                SELECT COUNT(*) FROM exam_question_answers
                WHERE belongs_question_id = exam_questions.id
                  AND is_correct = 1
            ) > 1 AS has_multiple_correct_answer')
        )
        ->where('exam_id', $submission->exam_id)
        ->orderBy('question_order')
        ->get();

    /* ---------- 4) Answers per question ---------- */
    $answerRows = DB::table('exam_question_answers')
        ->whereIn('belongs_question_id', $questions->pluck('question_id'))
        ->orderBy('belongs_question_id')
        ->orderBy('answer_order')
        ->get()
        ->groupBy('belongs_question_id');

    /* ---------- 5) Student answers JSON ---------- */
    $jsonCol = $col('exam_results', ['students_answer','answers','student_answers'])
        ?? abort(500, 'Answers column missing in exam_results');
    $studentAnswers = json_decode($submission->{$jsonCol} ?? '[]', true);

    // mini helper: fetch answer object for a question
    $getAnswer = static function(array $answers, $qid) {
        foreach ($answers as $row) {
            if (($row['question_id'] ?? null) == $qid) return $row['selected'] ?? null;
        }
        return null;
    };

    /* ---------- 6) Score meta ---------- */
    $totalMarks = $questions->sum(fn($q) => $q->question_mark ?? 1);
    $percentage = $totalMarks ? round(($submission->marks_obtained ?? 0) / $totalMarks * 100) : 0;
    $passFail   = $percentage >= 60 ? 'PASS' : 'FAIL';

    /* ---------- 7) Cleaner ---------- */
    $clean = static fn($txt) => trim(preg_replace(
        '/\s+/', ' ',
        strip_tags(html_entity_decode($txt ?? '', ENT_QUOTES, 'UTF-8'))
    ));

    /* ---------- 8) Stream HTML ---------- */
    return Response::streamDownload(
        function () use (
            $studentName,$department,$year,$semester,
            $examName,$questions,$answerRows,$studentAnswers,$getAnswer,
            $submission,$totalMarks,$percentage,$passFail,$clean
        ) {

            $currentDate      = now()->format('F j, Y');
            $currentTimestamp = now()->format('F j, Y, g:i a');
            $cleanText        = $clean;

            echo <<<HTML
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Answer Sheet - {$cleanText($studentName)}</title>
<style>
@page { size:A4; margin:0 }
body{font-family:"Helvetica Neue",Arial,sans-serif;margin:0;padding:0;color:#333;line-height:1.6}
#downloadPdfBtn{position:fixed;top:10px;right:10px;background:#2c3e50;color:#fff;border:none;
  padding:8px 12px;border-radius:4px;cursor:pointer;z-index:1000;box-shadow:0 2px 6px rgba(0,0,0,.3)}
@media print{#downloadPdfBtn{display:none}.container{max-width:none}}
.container{max-width:800px;margin:0 auto;padding:20px}
.header{text-align:center;padding:20px;border-bottom:2px solid #2c3e50;margin-bottom:25px;background:#f9f9f9}
.school-name{font-size:24px;font-weight:bold;color:#2c3e50;margin-bottom:10px}
.student-info{display:flex;justify-content:space-between;flex-wrap:wrap;margin-bottom:20px}
.student-info-box{flex:1;min-width:250px;padding:15px;border:1px solid #ddd;border-radius:5px;
  background:#f5f5f5;margin-right:15px;margin-bottom:10px}
.student-info-box:last-child{margin-right:0}
.student-info-label{font-weight:bold;color:#555}
.score-summary{display:flex;justify-content:space-around;flex-wrap:wrap;background:#f1f8ff;
  border:1px solid #c0d6e4;border-radius:5px;padding:15px;margin-bottom:25px}
.score-item{text-align:center;padding:10px}.score-value{font-size:24px;font-weight:bold;margin-bottom:5px}
.questions-section{margin-top:30px}.section-title{font-size:18px;font-weight:bold;padding-bottom:10px;
  border-bottom:1px solid #eee;margin-bottom:20px;color:#2c3e50}
.question{margin-bottom:25px;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fff;
  box-shadow:0 1px 3px rgba(0,0,0,.05);page-break-inside:avoid}
.question-header{display:flex;justify-content:space-between;margin-bottom:10px}
.q-number{font-weight:bold;color:#2c3e50}.q-title{flex:1;font-weight:bold}.q-points{color:#666;font-style:italic}
.answer{margin-top:10px;padding:10px;border-radius:3px}
.correct{background:#e8f5e9;border-left:4px solid #4caf50}
.wrong  {background:#ffebee;border-left:4px solid #f44336}
.skipped{background:#fff8e1;border-left:4px solid #ffc107}
.answer-label{font-weight:bold;margin-right:5px}
.correct-text{color:#2e7d32;font-weight:600}
.wrong-text  {color:#c62828;font-weight:600}
.skipped-text{color:#ef6c00;font-weight:600}
.footer{text-align:center;margin-top:40px;padding-top:20px;border-top:1px solid #eee;color:#777;font-size:12px}
</style>
</head><body>
<button id="downloadPdfBtn">Download PDF</button>
<div class="container">
  <div class="header">
    <div class="school-name">Academic Answer Sheet</div>
    <h2>{$cleanText($examName)}</h2>
  </div>

  <div class="student-info">
    <div class="student-info-box">
      <div><span class="student-info-label">Student Name:</span> {$cleanText($studentName)}</div>
      <div><span class="student-info-label">Department:</span> {$cleanText($department)}</div>
      <div><span class="student-info-label">Year:</span> {$cleanText($year)}</div>
      <div><span class="student-info-label">Semester:</span> {$cleanText($semester)}</div>
    </div>
    <div class="student-info-box">
      <div><span class="student-info-label">Exam ID:</span> #{$submission->exam_id}</div>
      <div><span class="student-info-label">Submission ID:</span> #{$submission->id}</div>
      <div><span class="student-info-label">Date:</span> {$currentDate}</div>
    </div>
  </div>

  <div class="score-summary">
    <div class="score-item"><div class="score-value">{$submission->marks_obtained}/{$totalMarks}</div><div>Score</div></div>
    <div class="score-item"><div class="score-value">{$percentage}%</div><div>Percentage</div></div>
    <div class="score-item"><div class="score-value">{$passFail}</div><div>Result</div></div>
  </div>

  <div class="questions-section">
    <div class="section-title">Questions and Answers</div>
HTML;

            /* ---------- QUESTION LOOP ---------- */
            foreach ($questions as $q) {
                $qTitle = nl2br(e($cleanText($q->question_title)));
                $qMark  = $q->question_mark ?? 1;
                $stuRaw = $getAnswer($studentAnswers, $q->question_id);

                /* correct answer (first one or joined list) */
                $correct = '';
                foreach ($answerRows[$q->question_id] ?? [] as $ans) {
                    if ($ans->is_correct) {
                        $correct = $q->question_type === 'fill_in_the_blank' && $ans->answer_two_gap_match
                                   ? $ans->answer_two_gap_match
                                   : $ans->answer_title;
                        break;
                    }
                }
                $correct = $cleanText($correct);

                /* evaluate */
                $isSkipped  = empty($stuRaw);
                $isCorrect  = false;
                $stuDisplay = '';

                if (!$isSkipped) {
                    if ($q->question_type === 'fill_in_the_blank') {
                        $stuDisplay = $cleanText($stuRaw);
                        $isCorrect  = strcasecmp($stuDisplay, $correct) === 0;

                    } elseif ($q->question_type === 'true_false') {
                        foreach ($answerRows[$q->question_id] ?? [] as $ans) {
                            if ($ans->id == $stuRaw) {
                                $stuDisplay = $cleanText($ans->answer_title);
                                break;
                            }
                        }
                        $isCorrect = strcasecmp($stuDisplay, $correct) === 0;

                    } else { // MCQ
                        $multi = $q->has_multiple_correct_answer;
                        $correctIds = array_column(
                            array_filter($answerRows[$q->question_id]->toArray(),
                                fn($a)=>$a->is_correct),
                            'id'
                        );

                        if ($multi) {
                            $stuArr = (array)$stuRaw;
                            sort($stuArr); sort($correctIds);
                            $isCorrect = $stuArr === $correctIds;

                            $labels = [];
                            foreach ($answerRows[$q->question_id] as $ans) {
                                if (in_array($ans->id, $stuArr)) {
                                    $labels[] = $cleanText($ans->answer_title);
                                }
                            }
                            $stuDisplay = implode(', ', $labels);
                        } else {
                            $isCorrect = ($stuRaw == ($correctIds[0] ?? null));
                            foreach ($answerRows[$q->question_id] as $ans) {
                                if ($ans->id == $stuRaw) {
                                    $stuDisplay = $cleanText($ans->answer_title);
                                    break;
                                }
                            }
                        }
                    }
                }

                $statusClass = $isSkipped ? 'skipped' : ($isCorrect ? 'correct' : 'wrong');
                $statusText  = $isSkipped ? 'Question Skipped'
                           : ($isCorrect ? 'Correct Answer!' : 'Incorrect Answer');
                $suffix      = $qMark > 1 ? 's' : '';

                echo <<<HTML
    <div class="question">
      <div class="question-header">
        <div class="q-number">Q{$q->question_order}.</div>
        <div class="q-title">{$qTitle}</div>
        <div class="q-points">{$qMark} point{$suffix}</div>
      </div>
      <div class="answer {$statusClass}">
        <div class="{$statusClass}-text">{$statusText}</div>
HTML;
                if (!$isSkipped && !$isCorrect) {
                    echo '<div><span class="answer-label">Your Answer:</span> '
                       . nl2br(e($stuDisplay)) . '</div>';
                }
                if ($isSkipped || !$isCorrect) {
                    echo '<div><span class="answer-label">Correct Answer:</span> '
                       . nl2br(e($correct)) . '</div>';
                }
                echo "</div></div>";
            }

            echo <<<HTML
  </div> <!-- /questions-section -->

  <div class="footer">
    <p>This is an automatically generated answer sheet. Please contact your instructor if you have questions.</p>
    <p>Generated on {$currentTimestamp}</p>
  </div>
</div>

<script>
document.getElementById('downloadPdfBtn')
        .addEventListener('click', () => window.print());
</script>
</body></html>
HTML;
        },
        "answer_sheet_{$id}.html",
        ['Content-Type' => 'text/html']
    );
}


/**
 * Send student exam result to email
 * 
 * POST /api/exam/send-result-to-email
 * 
 * @param Request $request
 * @return JsonResponse
 */
/**
 * Send student exam result to email
 * 
 * POST /api/exam/send-result-to-email
 * 
 * @param Request $request
 * @return JsonResponse
 */
/**
 * Generates answer sheet HTML content
 */
private function generateAnswerSheetHtml(int $resultId): string 
{
    /* ---------- 1) Get submission data ---------- */
    $submission = DB::table('exam_results')
        ->where('id', $resultId)
        ->first();
    
    if (!$submission) {
        throw new \Exception("Exam result not found");
    }

    /* ---------- 2) Get student data ---------- */
    $student = DB::table('students')
        ->where('id', $submission->student_id)
        ->first();

    /* ---------- 3) Get exam data ---------- */
    $exam = DB::table('exams')
        ->where('id', $submission->exam_id)
        ->first();

    /* ---------- 4) Get questions and answers ---------- */
    $questions = DB::table('exam_questions')
        ->where('exam_id', $submission->exam_id)
        ->orderBy('question_order')
        ->get();

    $studentAnswers = json_decode($submission->students_answer ?? '[]', true);

    /* ---------- 5) Generate HTML ---------- */
    ob_start();
    include(resource_path('views/emails/answer_sheet_template.php'));
    return ob_get_clean();
}
/**
 * Send student exam result to email
 * 
 * POST /api/exam/send-result-to-email
 * 
 * @param Request $request
 * @return JsonResponse
 */
/**
 * Send student exam result to email
 * 
 * POST /api/exam/send-result-to-email
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function sendResultToEmail(Request $request): JsonResponse
{
    Log::info('sendResultToEmail API called.', ['payload' => $request->all()]);

    // Validate input
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|integer|exists:students,id',
        'email'      => 'required|email',
        'exam_id'    => 'required|integer|exists:exams,id',
        'result_id'  => 'nullable|integer|exists:exam_results,id'
    ]);

    if ($validator->fails()) {
        Log::warning('Validation failed in sendResultToEmail.', ['errors' => $validator->errors()]);
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    try {
        $studentId = $request->student_id;
        $email     = $request->email;
        $examId    = $request->exam_id;
        $resultId  = $request->result_id;

        Log::info("Fetching exam result", compact('studentId','examId','resultId'));

        // Get the most recent result if no specific result_id provided
        $resultQuery = DB::table('exam_results')
            ->where('exam_id', $examId)
            ->where('student_id', $studentId);

        if ($resultId) {
            $resultQuery->where('id', $resultId);
        }

        $result = $resultQuery->orderBy('created_at', 'desc')->first();
        if (!$result) {
            Log::warning("No exam result found");
            return response()->json([
                'success' => false,
                'message' => 'Exam result not found for this student'
            ], 404);
        }

        // Verify the result is published to student
        if (!$result->publish_to_student) {
            Log::warning("Result not published to student", ['result_id' => $result->id]);
            return response()->json([
                'success' => false,
                'message' => 'This result has not been released yet'
            ], 403);
        }

        // Get student and exam details
        $student = DB::table('students')->where('id', $studentId)->first();
        if (!$student) {
            Log::warning("Student not found", ['student_id' => $studentId]);
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        $exam = DB::table('exams')->where('id', $examId)->first();
        if (!$exam) {
            Log::warning("Exam not found", ['exam_id' => $examId]);
            return response()->json([
                'success' => false,
                'message' => 'Exam not found'
            ], 404);
        }

        // Update student email if different from request and not already taken
        if ($student->email !== $email) {
            $conflict = DB::table('students')
                ->where('email', $email)
                ->where('id', '<>', $studentId)
                ->exists();

            if ($conflict) {
                Log::warning("Email already taken by another student", [
                    'new_email'   => $email,
                    'student_id'  => $studentId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Email already taken'
                ], 409);
            }

            DB::table('students')
                ->where('id', $studentId)
                ->update([
                    'email'      => $email,
                    'updated_at' => now()
                ]);

            Log::info("Updated student email", [
                'old_email'   => $student->email,
                'new_email'   => $email,
                'student_id'  => $studentId
            ]);
        }

        // Get questions and answers
        $questions = DB::table('exam_questions')
            ->where('exam_id', $examId)
            ->orderBy('question_order')
            ->get();

        $answerRows = DB::table('exam_question_answers')
            ->whereIn('belongs_question_id', $questions->pluck('id'))
            ->orderBy('belongs_question_id')
            ->orderBy('answer_order')
            ->get()
            ->groupBy('belongs_question_id');

        $studentAnswers = json_decode($result->students_answer ?? '[]', true);

        // Calculate total marks and percentage
        $totalMarks = $questions->sum('question_mark');
        $percentage = $totalMarks > 0 
            ? round(($result->marks_obtained / $totalMarks) * 100)
            : 0;
        $passFail = $percentage >= 60 ? 'PASS' : 'FAIL';

        // Generate answer sheet HTML
        $htmlContent = $this->generateAnswerSheetContent($result->id, [
            'questions'      => $questions,
            'answerRows'     => $answerRows,
            'studentAnswers' => $studentAnswers
        ]);

        // Prepare email data
        $mailData = [
            'studentName'    => $student->name ?? 'Student',
            'examName'       => $exam->examName,
            'marksObtained'  => $result->marks_obtained,
            'totalMarks'     => $totalMarks,
            'percentage'     => $percentage,
            'passFail'       => $passFail,
            'answerSheet'    => $htmlContent,
            'attemptNumber'  => $result->total_attempts,
            'submissionDate' => \Carbon\Carbon::parse($result->created_at)->format('F j, Y \a\t g:i a'),
            'exam'           => $exam,
            'student'        => $student,
            'submission'     => $result,
            'questions'      => $questions,
            'answerRows'     => $answerRows,
            'studentAnswers' => $studentAnswers
        ];

        Log::info("Sending exam result email", [
            'to'   => $email,
            'exam' => $exam->examName
        ]);
        Log::debug("Prepared mail data before sending:", $mailData);

        Mail::to($email)->send(new ExamResultMail($mailData));

        return response()->json([
            'success' => true,
            'message' => 'Exam result has been sent to your email'
        ], 200);

    } catch (\Exception $e) {
        Log::error('Failed to send exam result email', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to send exam result',
            'error'   => $e->getMessage()
        ], 500);
    }
}


/**
 * Generate answer sheet HTML content
 */
private function generateAnswerSheetContent(int $resultId, array $data = []): string
{
    $submission = DB::table('exam_results')
        ->where('id', $resultId)
        ->first();

    if (!$submission) {
        throw new \Exception("Exam result not found");
    }

    $student = DB::table('students')
        ->where('id', $submission->student_id)
        ->first();

    $exam = DB::table('exams')
        ->where('id', $submission->exam_id)
        ->first();

    // Use passed data or fetch fresh
    $questions = $data['questions'] ?? DB::table('exam_questions')
        ->where('exam_id', $submission->exam_id)
        ->orderBy('question_order')
        ->get();

    $answerRows = $data['answerRows'] ?? DB::table('exam_question_answers')
        ->whereIn('belongs_question_id', $questions->pluck('id'))
        ->orderBy('belongs_question_id')
        ->orderBy('answer_order')
        ->get()
        ->groupBy('belongs_question_id');

    $studentAnswers = $data['studentAnswers'] ?? json_decode($submission->students_answer ?? '[]', true);

    // Render the view
    return view('emails.exam_result', [
        'submission' => $submission,
        'student' => $student,
        'exam' => $exam,
        'questions' => $questions,
        'answerRows' => $answerRows,
        'studentAnswers' => $studentAnswers,
        'currentDate' => Carbon::now()->format('F j, Y'),
        'currentTimestamp' => Carbon::now()->format('F j, Y, g:i a'),
        'totalMarks' => $questions->sum('question_mark')
    ])->render();
}

}
