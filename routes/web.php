<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect(route('login'));
});

Route::get('/dashboard', function () {
    return redirect(route('school.index'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/school/list', [SchoolController::class, 'list'])->name('school.list');
    Route::get('/school/{school_id}/add', [SchoolController::class, 'add'])->name('school.add');
    Route::post('/school/semester', [SchoolController::class, 'index'])->name('school.semester');
    Route::post('/school/year', [SchoolController::class, 'index'])->name('school.year');
    Route::get('/school/year', [SchoolController::class, 'index'])->name('school.default_year');
    Route::resource('/school', SchoolController::class);

    Route::get('/course/{school_id}/create', [CourseController::class, 'create'])->name('course.create');
    Route::post('/course/{school_id}', [CourseController::class, 'store'])->name('course.store');
    Route::get('/course/{course_id}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/course/{course_id}/edit', [CourseController::class, 'edit'])->name('course.edit');
    Route::put('/course/{course_id}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/course/{course_id}', [CourseController::class, 'destroy'])->name('course.destroy');

    Route::get('/group/', [GroupController::class, 'index'])->name('group.index');
    Route::get('/group/{course_id}/create', [GroupController::class, 'create'])->name('group.create');
    Route::post('/group/{course_id}', [GroupController::class, 'store'])->name('group.store');

    Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
    Route::get('/planning/{group_id}/create', [PlanningController::class, 'create'])->name('planning.create');
    Route::post('/planning/{group_id}', [PlanningController::class, 'store'])->name('planning.store');
    
    Route::resource('/program', ProgramController::class);
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
