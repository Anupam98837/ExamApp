<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * GET /api/courses
     */
    public function index(): JsonResponse
    {
        Log::info('Course index: fetching all courses');
        $courses = DB::table('course')
                     ->orderBy('created_at', 'desc')
                     ->get();

        return response()->json([
            'success' => true,
            'courses' => $courses,
        ], 200);
    }

    /**
     * POST /api/courses
     */
    public function store(Request $request): JsonResponse
{
    Log::info('Course store: creating new course', ['payload' => $request->all()]);

    $rules = [
        'title'         => 'required|string|max:255',
        'description'   => 'required|string',
        'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'regular_price' => 'nullable|numeric|min:0',
        'sale_price'    => 'nullable|numeric|min:0',
    ];

    $v = Validator::make($request->all(), $rules);
    if ($v->fails()) {
        Log::warning('Course store: validation failed', ['errors' => $v->errors()]);
        return response()->json(['success' => false, 'errors' => $v->errors()], 422);
    }

    // Check for existing title
    $exists = DB::table('course')->where('title', $request->title)->exists();
    if ($exists) {
        Log::warning('Course store: title already exists', ['title' => $request->title]);
        return response()->json([
            'success' => false,
            'message' => 'Course already exists.'
        ], 409);
    }

    // Handle optional image upload
    if ($request->hasFile('image')) {
        $file     = $request->file('image');
        $filename = 'course_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('assets/images/course'), $filename);
        $imgPath  = "assets/images/course/{$filename}";
    } else {
        // Default course image
        $imgPath = "assets/images/course/course.png";
    }

    // Insert new course
    $id = DB::table('course')->insertGetId([
        'title'         => $request->title,
        'description'   => $request->description,
        'image'         => $imgPath,
        'regular_price' => $request->regular_price,
        'sale_price'    => $request->sale_price,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    Log::info('Course store: created', ['course_id' => $id]);
    return response()->json([
        'success'   => true,
        'message'   => 'Course created successfully.',
        'course_id' => $id,
    ], 201);
}


    /**
     * GET /api/courses/{id}
     */
    public function show(int $id): JsonResponse
    {
        Log::info('Course show: fetching', ['course_id' => $id]);
        $course = DB::table('course')->find($id);

        if (! $course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'course'  => $course,
        ], 200);
    }

    /**
     * PUT/PATCH /api/courses/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        Log::info('Course update: validating', ['course_id' => $id, 'payload' => $request->all()]);

        $rules = [
            'title'         => "sometimes|required|string|max:255|unique:course,title,{$id}",
            'description'   => 'sometimes|required|string',
            'image'         => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'regular_price' => 'sometimes|nullable|numeric|min:0',
            'sale_price'    => 'sometimes|nullable|numeric|min:0',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            Log::warning('Course update: validation failed', ['errors' => $v->errors()]);
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        // handle new image if uploaded
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = 'course_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/course'), $filename);
            $data['image'] = "assets/images/course/{$filename}";
        }

        $data['updated_at'] = now();

        DB::table('course')->where('id', $id)->update($data);
        Log::info('Course update: applied', ['course_id' => $id, 'fields' => array_keys($data)]);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully.',
        ], 200);
    }

    /**
     * DELETE /api/courses/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        Log::info('Course destroy: deleting', ['course_id' => $id]);
        $deleted = DB::table('course')->where('id', $id)->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully.',
        ], 200);
    }
}
