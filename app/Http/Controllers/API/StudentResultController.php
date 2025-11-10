<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentResultController extends Controller
{
    /* -----------------------------------------------------------
     | GET /api/exam-results  (?exam_id=)
     * ----------------------------------------------------------*/
    public function index(Request $request): JsonResponse
    {
        $q = DB::table('exam_results');

        if ($request->filled('exam_id')) {
            $q->where('exam_id', $request->exam_id);
        }

        return response()->json($q->get(), 200);
    }

    /* -----------------------------------------------------------
     | PATCH /api/exam-results/{id}/publish-toggle
     * ----------------------------------------------------------*/
    public function togglePublish(int $id): JsonResponse
    {
        $row = DB::table('exam_results')->where('id', $id)->first();
        if (! $row) {
            return response()->json([
                'success' => false,
                'message' => 'exam_results record not found',
            ], 404);
        }

        DB::table('exam_results')
            ->where('id', $id)
            ->update([
                'publish_to_student' => DB::raw('1 - publish_to_student'),
                'updated_at'        => now(),
            ]);

        $newStatus = DB::table('exam_results')
            ->where('id', $id)
            ->value('publish_to_student');

        return response()->json([
            'success'            => true,
            'publish_to_student' => (int) $newStatus,
            'id'                 => $id,
        ], 200);
    }

    /* -----------------------------------------------------------
     | GET /api/exam-results/student/{student_id}  (?exam_id=)
     * ----------------------------------------------------------*/
    public function showForStudent(Request $request, int $student_id): JsonResponse
    {
        $base = DB::table('exam_results as er')
            ->join('exams   as e', 'e.id', '=', 'er.exam_id')
            ->join('students as s', 's.id', '=', 'er.student_id')
            ->where('er.student_id', $student_id)
            ->where('er.publish_to_student', 1)
            ->select([
                'er.id',
                'er.student_id',
                's.name',
                'er.exam_id',
                'e.examName',
                'er.publish_to_student',
                'er.marks_obtained',
                'er.total_attempts',
                'er.students_answer',
                'er.created_at',
                'er.updated_at',
            ]);

        if ($request->filled('exam_id')) {
            $base->where('er.exam_id', $request->exam_id);
        }

        $results = $base->orderBy('er.created_at', 'desc')->get();

        if ($results->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'No results found',
            ], 200);
        }

        /* ----- Question counts per exam ----- */
        $examIds = $results->pluck('exam_id')->unique()->all();
        $questionCounts = DB::table('exam_questions')
            ->whereIn('exam_id', $examIds)
            ->select('exam_id', DB::raw('count(*) as total'))
            ->groupBy('exam_id')
            ->pluck('total', 'exam_id')
            ->toArray();

        /* ----- Enhance payload ----- */
        $enhanced = $results->map(function ($row) use ($questionCounts) {
            $answers = json_decode($row->students_answer, true) ?: [];

            $attempted = collect($answers)
                ->filter(fn($ans) =>
                    is_array($ans['selected'])
                        ? count($ans['selected']) > 0
                        : !is_null($ans['selected'])
                )->count();

            $totalQuestions = $questionCounts[$row->exam_id] ?? 0;
            $notAttempted   = max(0, $totalQuestions - $attempted);

            return [
                'id'                     => $row->id,
                'student_id'             => $row->student_id,
                'name'                   => $row->name,
                'exam_id'                => $row->exam_id,
                'examName'               => $row->examName,
                'publish_to_student'     => $row->publish_to_student,
                'marks_obtained'         => $row->marks_obtained,
                'total_attempts'         => $row->total_attempts,
                'attempted_questions'    => $attempted,
                'not_attempted_questions'=> $notAttempted,
                'created_at'             => $row->created_at,
                'updated_at'             => $row->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $enhanced,
        ], 200);
    }
}
