<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Data\DataController;
use App\Http\Controllers\Exam\ExamController;
use App\Http\Controllers\Subject\SubjectController;
use App\Http\Controllers\Grade\GradeController;
use App\Http\Controllers\Exam\StudentResultController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Settings\SchoolCalendarController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Exam\StudentPerformanceController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); 


Route::post('/login', [UserController::class, 'authenticate']);

Route::get('/open', [DataController::class, 'open']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('/closed', [DataController::class,'closed']);
});

//Route::get('/permission', [UserController::class, 'userPermission'])->middleware('jwt.verify:CREATE_USERS');

Route::middleware(['jwt.verify:REGISTER_STUDENT_SUBJECT'])->group(function () {
    Route::get('/registered_student_subject/{class_id}/{subject_id}', [StudentController::class, 'registerStudentSubject']);
    Route::post('/update_registered_student', [StudentController::class, 'updateStudentSubject']);
});

Route::middleware(['jwt.verify:UPLOAD_STUDENT_RESULTS'])->group(function () {
    Route::get('/current_exam/{id}', [ExamController::class, 'currentExam']);
    Route::get('/teacher_subject', [SubjectController::class, 'teacherSubject']);
    Route::get('/teacher_grade/{subject_id}', [GradeController::class, 'teacherGrade']);
    Route::post('/student_result', [StudentResultController::class, 'studentResults']);
    Route::post('/update_student_result', [StudentResultController::class, 'updateStudentResults']);
});

Route::middleware(['jwt.verify:ALL_USERS'])->group(function () {
    Route::get('/user', [UserController::class,'getAuthenticatedUser']);
    Route::get('/subject', [SubjectController::class, 'index']);
    Route::get('/grade/{institution_id}', [GradeController::class, 'show']);
    Route::get('/school_calendar/{institution_id}', [SchoolCalendarController::class, 'getSchoolCalendar']);
    Route::post('/change_password', [UserController::class, 'changePassword']);
});    

Route::middleware(['jwt.verify:REGISTER_USER'])->group(function () {
    Route::post('/register', [UserController::class, 'register']);
}); 

Route::middleware(['jwt.verify:REGISTER_TEACHER'])->group(function () {
    Route::get('/teacher/{institution_id}', [TeacherController::class, 'index']);
    Route::post('/register_teacher', [TeacherController::class, 'store']);
    Route::post('/update_teacher', [TeacherController::class, 'update']);
    Route::get('/teacher_subject/{teacher_id}',[TeacherController::class, 'getTeacherSubject'] );
    Route::post('/assign_teacher_subject', [TeacherController::class,'assignTeacherSubject']);
}); 

Route::middleware(['jwt.verify:REGISTER_STUDENT'])->group(function () {
    Route::get('/students/{institution_id}/{class_id}/{school_calendar_id}', [StudentController::class, 'getStudentClass']);
    Route::post('/import_students', [StudentController::class, 'importStudents']);
});

Route::middleware(['jwt.verify:PUBLISH_RESULT'])->group(function () {
    Route::get('/class_teacher_grade', [GradeController::class, 'getClassTeacherClass']);
    Route::get('/class_result/{school_calendar_id}/{exam_id}/{class_id}', [StudentResultController::class, 'classResults']);
    Route::post('/publish_results', [StudentResultController::class, 'publishResult']);
    Route::post('/publish_message', [NotificationController::class, 'publishMessage']);
    Route::post('/publish_message_final', [NotificationController::class, 'sendFinalResult']);
    Route::post('/publish_results_final', [StudentResultController::class, 'publishFinalResult']);
});

Route::middleware(['jwt.verify:STUDENT_RESULT'])->group(function () {
    Route::get('/student_result/{user_id}', [StudentResultController::class, 'getPublishedResult']);
    Route::get('/student_final_result/{user_id}', [StudentResultController::class, 'getFinalResult']);
    Route::post('/parent_feedback', [StudentResultController::class, 'parentFeedback']);
});

Route::post('/school_calendar', [SchoolCalendarController::class, 'addSchoolCalendar']);
Route::get('/school_term/{school_calendar_id}', [SchoolCalendarController::class, 'getSchoolTerm']);

//this route will be deleted after upgrade
Route::get('/update_grade_point', [StudentResultController::class, 'updateGradePoints']);
Route::get('/clean_data', [StudentResultController::class, 'updateStudent']);
Route::post('/delivery_report', [NotificationController::class, 'deliveryReport']);


Route::get('/class_result/{school_calendar_id}/{class_id}', [StudentResultController::class, 'finalResults']);
Route::get('/student/{class_id}/{school_calendar_id}', [StudentController::class, 'getStudents']);

Route::get('/performance/{class_id}/{student_id}/{term_id}', [StudentPerformanceController::class, 'getPerformance']);
Route::post('/performance', [StudentPerformanceController::class, 'postStudentPerformance']);
Route::get('/performance_comment/{class_id}/{student_id}/{term_id}', [StudentPerformanceController::class, 'getComments']);
Route::post('/performance_comment', [StudentPerformanceController::class, 'postPerformanceComment']);


Route::get('/student_final_report/{class_id}/{term_id}', [StudentResultController::class, 'getFinalReport']);


Route::get('/pdf/final_report/{term_id}/{class_id}/{language}', [StudentResultController::class, 'getPdf']);

Route::get('/pdf/print_report/{term_id}/{class_id}', [StudentResultController::class, 'printAllReport']);