<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\ExamController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\StudentResultController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\MailController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/me', [AuthController::class, 'getUserByToken'])->middleware(['CheckAuth:admin,student']);
Route::post('/send-mail', [MailController::class, 'sendMail']);


Route::prefix('admin')->group(function () {
    // Public
    Route::post('login', [AdminController::class, 'login']);

    // Protected
    Route::middleware('CheckAuth:admin')->group(function () {
        Route::post('logout', [AdminController::class, 'logout']);
        Route::get('dashboard', [DashboardController::class, 'adminDashboard']);
    });
});


Route::prefix('student')->group(function () {

    Route::post('/register', [StudentController::class, 'register']);
    Route::post('/login', [StudentController::class, 'login']);
    Route::post('/email-upload', [StudentController::class, 'uploadEmail']);
    // Protected routes with student middleware
    Route::middleware('CheckAuth:student')->group(function () {
        Route::post('/logout', [StudentController::class, 'logout']);
        Route::post('/check-attempt', [StudentController::class, 'checkAttempt']);
        Route::get('/dashboard', [DashboardController::class, 'studentDashboard']);
    });
    // Route accessible by both admin and student
    Route::middleware(['CheckAuth:admin,student'])->group(function () {
        Route::get('/all', [StudentController::class, 'allStudents']);
        Route::get('/details', [StudentController::class, 'getStudentDetails']);
    });

});

//course

Route::prefix('courses')->group(function () {
    // ▶️ Public
    Route::get('/',       [CourseController::class, 'index'])->name('courses.index');
    Route::get('{id}',    [CourseController::class, 'show'])
         ->whereNumber('id')
         ->name('courses.show');

    // ▶️ Admin only (protect with your admin middleware)
    Route::middleware('CheckAuth:admin')->group(function () {
        Route::post('/',       [CourseController::class, 'store'])->name('courses.store');
        Route::put('{id}',     [CourseController::class, 'update'])
             ->whereNumber('id')
             ->name('courses.update');
        Route::delete('{id}',  [CourseController::class, 'destroy'])
             ->whereNumber('id')
             ->name('courses.destroy');
    });
});



Route::prefix('exam')->group(function () {
    // ▶️ Public routes accessible to both admin & student
    Route::get('first-by-course', [ExamController::class, 'firstExamsByCourse'])
         ->name('exams.first-by-course');

    Route::get('suggested', [ExamController::class, 'suggested'])
         ->name('exams.suggested');

    Route::get('{examId}/questions-with-answers', [ExamController::class,'questionsWithAnswers'])
         ->whereNumber('examId')
         ->name('exams.questions-with-answers');

    Route::post('{examId}/submit', [ExamController::class,'submit'])
         ->whereNumber('examId')
         ->name('exams.submit');

    Route::get('/', [ExamController::class, 'index'])
         ->name('exams.index');

    Route::get('{id}', [ExamController::class, 'show'])
         ->whereNumber('id')
         ->name('exams.show');

    // ▶️ Protected routes for admin & student
    Route::middleware(['CheckAuth:admin,student'])->group(function () {

     Route::get('{examId}/has-attempts', [ExamController::class, 'hasAttempts'])
     ->whereNumber('examId')
             ->name('exams.has-attempts');

        Route::get('students/quizzes/results', [ExamController::class, 'getStudentsResults'])
             ->name('exams.students-results');

        Route::get('results/{id}/answer-sheet', [ExamController::class,'answerSheetDownload'])
             ->whereNumber('id')
             ->name('exams.answer-sheet');

        Route::post('result-to-email', [ExamController::class, 'sendResultToEmail'])
             ->name('exams.send-result-email');

        Route::get('{id}/payment-info', [ExamController::class, 'paymentInfo'])
             ->whereNumber('id')
             ->name('exams.payment-info');
             
    });

    // ▶️ Admin-only routes
    Route::middleware('CheckAuth:admin')->group(function () {
        Route::post('/', [ExamController::class, 'store'])
             ->name('exams.store');

        Route::put('{id}', [ExamController::class, 'update'])
             ->whereNumber('id')
             ->name('exams.update');

        Route::delete('{id}', [ExamController::class, 'destroy'])
             ->whereNumber('id')
             ->name('exams.destroy');

        Route::patch('{id}/result-setup', [ExamController::class, 'updateResultSetUpType'])
             ->whereNumber('id')
             ->name('exams.update-result-setup');

        Route::post('{id}/questions', [ExamController::class, 'addQuestion'])
             ->whereNumber('id')
             ->name('exams.questions.store');

        Route::patch('{examId}/questions/{questionId}', [ExamController::class, 'updateQuestion'])
             ->whereNumber('examId')->whereNumber('questionId')
             ->name('exams.questions.update');

        Route::put('{examId}/questions/{questionId}', [ExamController::class, 'updateQuestion'])
             ->whereNumber('examId')->whereNumber('questionId')
             ->name('exams.questions.update-alias');

        Route::delete('{examId}/questions/{questionId}', [ExamController::class, 'deleteQuestion'])
             ->whereNumber('examId')->whereNumber('questionId')
             ->name('exams.questions.destroy');
    });
});




//Question
Route::prefix('questions')->group(function () {
    // Shared access: admin & student
    Route::middleware(['CheckAuth:admin,student'])->group(function () {
        Route::get('view',        [QuestionController::class, 'index']); // List all (with ?exam_id)
        Route::get('view/{id}',   [QuestionController::class, 'show'])->whereNumber('id'); // View single
    });
    // Admin-only routes
    Route::middleware('CheckAuth:admin')->group(function () {
        Route::post('add',        [QuestionController::class, 'store']);
        Route::put('edit/{id}',   [QuestionController::class, 'update'])->whereNumber('id');
        Route::delete('delete/{id}', [QuestionController::class, 'destroy'])->whereNumber('id');
    });

});


//Student Result
Route::prefix('student-results')->group(function () {
    Route::get('/', [StudentResultController::class, 'index']);                    // admin list
    Route::patch('{id}/publish-toggle', [StudentResultController::class, 'togglePublish']);
    Route::get('student/{student_id}', [StudentResultController::class, 'showForStudent']);
});