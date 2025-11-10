<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing & Public Pages
|--------------------------------------------------------------------------
*/
Route::view('/', 'landingPage.pages.home')->name('home');
Route::view('/selectRole', 'landingPage.pages.selectRole')->name('select.role');
Route::view('/exams', 'landingPage.pages.exams')->name('public.exams');
Route::view('/buy-exam', 'landingPage.pages.buyExam')->name('public.buyExam');

/*
|--------------------------------------------------------------------------
| Authentication Pages
|--------------------------------------------------------------------------
*/
// Admin
Route::view('/admin/login', 'users.admin.pages.common.login')->name('admin.login');
// Mentor
Route::view('/mentor/login', 'users.mentor.pages.common.login')->name('mentor.login');
// Student
Route::view('/student/login', 'users.student.pages.common.login')->name('student.login');
Route::view('/student/register', 'modules.student.registerStudent')->name('student.register');

/*
|--------------------------------------------------------------------------
| Admin Dashboard & Management
|--------------------------------------------------------------------------
*/
Route::view('/admin/dashboard', 'users.admin.pages.common.dashboard')->name('admin.dashboard');
Route::view('/admin/mentor/manage', 'users.admin.pages.mentor.manageMentor')->name('admin.mentor.manage');
Route::view('/admin/student/manage', 'users.admin.pages.student.manageStudent')->name('admin.student.manage');
Route::view('/course/manage', 'users.admin.pages.course.manageCourse')->name('admin.student.manage');
Route::view('/exam/manage', 'users.admin.pages.exam.manageExam')->name('exam.manage');
Route::view('/exam/add',    'users.admin.pages.exam.addExam')->name('exam.add');
Route::view('/exam/result', 'users.admin.pages.result.viewResult')->name('admin.exam.result');

/*
|--------------------------------------------------------------------------
| Mentor Dashboard & Management
|--------------------------------------------------------------------------
*/
Route::view('/mentor/dashboard',         'users.mentor.pages.common.dashboard')->name('mentor.dashboard');
Route::view('/mentor/student/manage',    'users.mentor.pages.student.manageStudent')->name('mentor.student.manage');
Route::view('/quiz/manage',              'users.mentor.pages.quiz.manageQuiz')->name('mentor.quiz.manage');

/*
|--------------------------------------------------------------------------
| Student Dashboard & Activities
|--------------------------------------------------------------------------
*/
Route::view('/student/dashboard',        'users.student.pages.common.dashboard')->name('student.dashboard');
Route::view('/student/profile',          'users.student.pages.common.profile')->name('student.profile');
Route::view('/student/exams',            'users.student.pages.exam.viewExam')->name('student.exams');
Route::view('/student/exams/suggested',  'users.student.pages.exam.suggestedExam')->name('student.exams.suggested');
Route::view('/student/my-exam',          'users.student.pages.exam.myExam')->name('student.myExam');
Route::view('/student/quiz',             'users.student.pages.quiz.myQuiz')->name('student.myQuiz');
Route::view('/student/startquiz',        'modules.quiz.startQuiz')->name('student.startQuiz');
Route::view('/student/result',           'users.student.pages.result.viewResult')->name('student.result');
Route::view('/result/by-email',          'modules.result.viewResultByEmail')->name('result.byEmail');

/*
|--------------------------------------------------------------------------
| Exam & Question Modules
|--------------------------------------------------------------------------
*/
Route::view('/exam/details',             'modules.exam.examDetails')->name('exam.details');
Route::view('/exam/start',               'modules.exam.startExam')->name('exam.start');
Route::view('/exam/catalogue',           'modules.exam.viewExam')->name('exam.catalogue');
Route::view('/question/add',             'modules.question.manageQuestion')->name('question.add');

/*
|--------------------------------------------------------------------------
| Fallback
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return redirect()->route('home');
});
