<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /* =============================================================
     |  GET /api/questions?exam_id=…
     |  List all questions (and answers) for a given exam
     * ============================================================*/

         private function mapType(string $type): string
    {
        return match ($type) {
            'multiple_choice', 'single_choice' => 'mcq',
            default                             => $type,        // already 'mcq', 'true_false', etc.
        };
    }
    public function index(Request $request)
    {
        $examId = $request->query('exam_id');
        if (! $examId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameter: exam_id.',
            ], 422);
        }

        Log::info('Fetching questions for exam', ['exam_id' => $examId]);

        $rows = DB::table('exam_questions as q')
            ->leftJoin('exam_question_answers as a', 'q.id', '=', 'a.belongs_question_id')
            ->where('q.exam_id', $examId)
            ->orderBy('q.question_order')
            ->orderBy('a.answer_order')
            ->select([
                'q.id            as question_id',
                'q.exam_id',
                'q.question_title',
                'q.question_description',
                'q.answer_explanation',
                'q.question_type',
                'q.question_mark',
                'q.question_settings',
                'q.question_order',

                'a.id            as answer_id',
                'a.answer_title',
                'a.is_correct',
                'a.answer_order',
                'a.belongs_question_type',
                'a.image_id',
                'a.answer_two_gap_match',
                'a.answer_view_format',
                'a.answer_settings',
            ])
            ->get();

        $questions = [];
        foreach ($rows as $r) {
            $qid = $r->question_id;
            if (! isset($questions[$qid])) {
                $questions[$qid] = [
                    'question_id'          => $qid,
                    'exam_id'              => $r->exam_id,
                    'question_title'       => $r->question_title,
                    'question_description' => $r->question_description,
                    'answer_explanation'   => $r->answer_explanation,
                    'question_type'        => $r->question_type,
                    'question_mark'        => $r->question_mark,
                    'question_settings'    => $r->question_settings,
                    'question_order'       => $r->question_order,
                    'answers'              => [],
                ];
            }
            if ($r->answer_id !== null) {
                $questions[$qid]['answers'][] = [
                    'answer_id'             => $r->answer_id,
                    'answer_title'          => $r->answer_title,
                    'is_correct'            => (bool) $r->is_correct,
                    'answer_order'          => $r->answer_order,
                    'belongs_question_type' => $r->belongs_question_type,
                    'image_id'              => $r->image_id,
                    'answer_two_gap_match'  => $r->answer_two_gap_match,
                    'answer_view_format'    => $r->answer_view_format,
                    'answer_settings'       => $r->answer_settings,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => array_values($questions),
        ], 200);
    }

    /* =============================================================
     |  GET /api/questions/view/{id}
     |  Show one question + answers
     * ============================================================*/
    public function show($id)
    {
        $rows = DB::table('exam_questions as q')
            ->leftJoin('exam_question_answers as a', 'q.id', '=', 'a.belongs_question_id')
            ->where('q.id', $id)
            ->orderBy('a.answer_order')
            ->select([
                'q.id            as question_id',
                'q.exam_id',
                'q.question_title',
                'q.question_description',
                'q.answer_explanation',
                'q.question_type',
                'q.question_mark',
                'q.question_settings',
                'q.question_order',

                'a.id            as answer_id',
                'a.answer_title',
                'a.is_correct',
                'a.answer_order',
                'a.belongs_question_type',
                'a.image_id',
                'a.answer_two_gap_match',
                'a.answer_view_format',
                'a.answer_settings',
            ])
            ->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "No question found with ID {$id}.",
            ], 404);
        }

        $first = $rows->first();
        $question = [
            'question_id'          => $first->question_id,
            'exam_id'              => $first->exam_id,
            'question_title'       => $first->question_title,
            'question_description' => $first->question_description,
            'answer_explanation'   => $first->answer_explanation,
            'question_type'        => $first->question_type,
            'question_mark'        => $first->question_mark,
            'question_settings'    => $first->question_settings,
            'question_order'       => $first->question_order,
            'answers'              => [],
        ];

        foreach ($rows as $r) {
            if ($r->answer_id !== null) {
                $question['answers'][] = [
                    'answer_id'             => $r->answer_id,
                    'answer_title'          => $r->answer_title,
                    'is_correct'            => (bool) $r->is_correct,
                    'answer_order'          => $r->answer_order,
                    'belongs_question_type' => $r->belongs_question_type,
                    'image_id'              => $r->image_id,
                    'answer_two_gap_match'  => $r->answer_two_gap_match,
                    'answer_view_format'    => $r->answer_view_format,
                    'answer_settings'       => $r->answer_settings,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $question,
        ], 200);
    }

    /* =============================================================
     |  POST /api/questions
     |  Create question + answers
     * ============================================================*/
    public function store(Request $request)
    {
        $rules = [
            'exam_id'                => 'required|integer|exists:exams,id',
            'question_title'         => 'required|string',
            'question_description'   => 'nullable|string',
            'answer_explanation'     => 'nullable|string',
            'question_type'          => 'required|in:mcq,multiple_choice,single_choice,true_false,fill_in_the_blank',
            'question_mark'          => 'required|integer|min:1',
            'question_settings'      => 'nullable|array',
            'question_order'         => 'required|integer|min:1',
    
            'answers'                        => 'required|array|min:1',
            'answers.*.answer_title'         => 'nullable|string',
            'answers.*.is_correct'           => 'required|boolean',
            'answers.*.answer_order'         => 'nullable|integer',
            'answers.*.belongs_question_type'=> 'nullable|string',
            'answers.*.image_id'             => 'nullable|integer',
            'answers.*.answer_two_gap_match' => 'nullable|string',
            'answers.*.answer_view_format'   => 'nullable|string',
            'answers.*.answer_settings'      => 'nullable|array',
        ];
    
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            // 1. Create the question
            $questionId = DB::table('exam_questions')->insertGetId([
                'exam_id'              => $request->exam_id,
                'question_title'      => $request->question_title,
                'question_description' => $request->question_description,
                'answer_explanation'   => $request->answer_explanation,
                'question_type'       => $this->mapType($request->question_type),
                'question_mark'       => $request->question_mark,
                'question_settings'   => json_encode($request->question_settings ?? []),
                'question_order'      => $request->question_order,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
    
            // 2. Insert all answers
            foreach ($request->answers as $answer) {
                DB::table('exam_question_answers')->insert([
                    'belongs_question_id'    => $questionId,
                    'belongs_question_type'  => $answer['belongs_question_type'] ?? $this->mapType($request->question_type),
                    'answer_title'          => $answer['answer_title'] ?? null,
                    'is_correct'           => $answer['is_correct'],
                    'answer_order'          => $answer['answer_order'] ?? 0,
                    'image_id'             => $answer['image_id'] ?? null,
                    'answer_two_gap_match' => $answer['answer_two_gap_match'] ?? null,
                    'answer_view_format'   => $answer['answer_view_format'] ?? null,
                    'answer_settings'      => json_encode($answer['answer_settings'] ?? []),
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
    
            // 3. Update exam's total_questions count
            $currentCount = DB::table('exams')
                ->where('id', $request->exam_id)
                ->value('total_questions');
    
            $newCount = ($currentCount === null) ? 1 : $currentCount + 1;
    
            DB::table('exams')
                ->where('id', $request->exam_id)
                ->update(['total_questions' => $newCount]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Question created successfully.',
                'data'    => ['question_id' => $questionId]
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request'   => $request->all()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question. Please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /* =============================================================
     |  PUT/PATCH /api/questions/{id}
     |  Update question + answers
     * ============================================================*/
    public function update(Request $request, $id)
    {
        $rules = [
            'question_title'            => 'sometimes|required|string',
            'question_description'      => 'nullable|string',
            'answer_explanation'        => 'nullable|string',
            'question_type'             => 'sometimes|required|in:mcq,multiple_choice,single_choice,true_false,fill_in_the_blank',
            'question_mark'             => 'sometimes|required|integer|min:1',
            'question_settings'         => 'nullable|array',
            'question_order'            => 'sometimes|required|integer|min:1',

            'answers'                        => 'sometimes|array|min:1',
            'answers.*.answer_title'         => 'nullable|string',
            'answers.*.is_correct'           => 'required_with:answers|boolean',
            'answers.*.answer_order'         => 'nullable|integer',
            'answers.*.belongs_question_type'=> 'nullable|string',
            'answers.*.image_id'             => 'nullable|integer',
            'answers.*.answer_two_gap_match' => 'nullable|string',
            'answers.*.answer_view_format'   => 'nullable|string',
            'answers.*.answer_settings'      => 'nullable|array',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $updateData = $request->only([
                'question_title','question_description','answer_explanation',
                'question_mark','question_order'
            ]);

            if ($request->has('question_type')) {
                $updateData['question_type'] = $this->mapType($request->question_type);
            }
            if ($request->has('question_settings')) {
                $updateData['question_settings'] = json_encode($request->question_settings);
            }

            $updateData = array_filter($updateData, fn($v) => !is_null($v));
            if ($updateData) {
                $updateData['updated_at'] = now();
                DB::table('exam_questions')->where('id', $id)->update($updateData);
            }

            // Replace answers if provided
            if ($request->has('answers')) {
                DB::table('exam_question_answers')
                    ->where('belongs_question_id', $id)
                    ->delete();

                foreach ($request->answers as $ans) {
                    DB::table('exam_question_answers')->insert([
                        'belongs_question_id'    => $id,
                        'belongs_question_type'  => $ans['belongs_question_type'] ?? $this->mapType($request->question_type ?? $updateData['question_type'] ?? 'mcq'),
                        'answer_title'           => $ans['answer_title'],
                        'is_correct'             => $ans['is_correct'],
                        'image_id'               => $ans['image_id'] ?? null,
                        'answer_two_gap_match'   => $ans['answer_two_gap_match'] ?? null,
                        'answer_view_format'     => $ans['answer_view_format'] ?? null,
                        'answer_settings'        => json_encode($ans['answer_settings'] ?? null),
                        'answer_order'           => $ans['answer_order'] ?? 0,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully.',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error updating question', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question.',
            ], 500);
        }
    }

    /* =============================================================
     |  DELETE /api/questions/{id}
     |  Remove question + answers
     * ============================================================*/
    public function destroy($id)
{
    DB::beginTransaction();
    try {
        // 1. Get the question and verify existence
        $question = DB::table('exam_questions')
            ->where('id', $id)
            ->first();

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => "Question not found."
            ], 404);
        }

        $examId = $question->exam_id;

        // 2. Delete all associated answers
        DB::table('exam_question_answers')
            ->where('belongs_question_id', $id)
            ->delete();

        // 3. Delete the question
        $deleted = DB::table('exam_questions')
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            throw new \Exception("Question deletion failed");
        }

        // 4. Update exam's total_questions count
        $currentCount = DB::table('exams')
            ->where('id', $examId)
            ->value('total_questions');

        // Only decrement if current count exists and > 0
        if ($currentCount !== null && $currentCount > 0) {
            $newCount = $currentCount - 1;
            DB::table('exams')
                ->where('id', $examId)
                ->update(['total_questions' => $newCount]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully.'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Question deletion failed: ' . $e->getMessage(), [
            'question_id' => $id,
            'exception'   => $e
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete question. Please try again.',
            'error'   => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}
}
