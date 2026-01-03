<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DateSelectionController;
use App\Http\Controllers\CalendarFileController;
use Illuminate\Support\Facades\DB;

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
    try {
        DB::connection()->getPDO();
        return redirect(route('login'));
    } catch (\Exception $e) {
        return view('maintenance');
    }
});

Route::get('/dashboard', function () {
    return redirect(route('school.dashboard'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
//    Route::get('/calendar/import/{calendar_id}', [CalendarController::class, 'readICSFile'])->name('ics.read');
    Route::prefix('admin/calendars')->middleware(['auth'])->group(function () {
    Route::get('/', [CalendarFileController::class, 'index'])->name('calendar.index');
    Route::post('/upload', [CalendarFileController::class, 'upload'])->name('calendar.upload');
    Route::get('/upload', function () {
    return redirect()->route('calendar.index');});
    Route::post('/import', [CalendarFileController::class, 'import'])->name('calendar.import');
    Route::delete('/delete/{source}', [CalendarFileController::class, 'destroy'])->name('calendar.destroy');
});
    Route::post('/select', [DateSelectionController::class, 'index'])->name('date.select');
    Route::get('/school/list', [SchoolController::class, 'list'])->name('school.list');
    Route::get('/school/{school_id}/add', [SchoolController::class, 'add'])->name('school.add');
    Route::post('/school/{school_id}/document', [DocumentController::class, 'store'])->name('document.store');
    Route::post('/school/semester', [SchoolController::class, 'index'])->name('school.semester');
    Route::post('/school/year', [SchoolController::class, 'dashboard'])->name('school.year');
    Route::get('/school/year', [SchoolController::class, 'dashboard'])->name('school.default_year');
    Route::get('/school/dashboard', [SchoolController::class, 'dashboard'])->name('school.dashboard');
    Route::resource('/school', SchoolController::class);

    Route::get('documents/delete/{document}', [DocumentController::class, 'delete'])->name('documents.delete');

    Route::get('/course/{school_id}/create', [CourseController::class, 'create'])->name('course.create');
    Route::post('/course/{school_id}', [CourseController::class, 'store'])->name('course.store');
    Route::get('/course/{course_id}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/course/{course_id}/edit', [CourseController::class, 'edit'])->name('course.edit');
    Route::put('/course/{course_id}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/course/{course_id}', [CourseController::class, 'destroy'])->name('course.destroy');

    Route::get('/group', [GroupController::class, 'index'])->name('group.index');
    Route::get('/group/{course_id}/create', [GroupController::class, 'create'])->name('group.new');
    Route::get('/group/link/{group_id}', [GroupController::class, 'link'])->name('group.link');
    Route::get('/group/switch/{group_id}', [GroupController::class, 'switch'])->name('group.switch');
    Route::delete('/group/unlink/{group_id}', [GroupController::class, 'unlink'])->name('group.unlink');
    Route::post('/group/{course_id}', [GroupController::class, 'store'])->name('group.save');
    Route::resource('/group', GroupController::class);

    Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
    Route::get('/planning/previous', [PlanningController::class, 'previous'])->name('planning.previous');
    Route::get('/planning/next', [PlanningController::class, 'next'])->name('planning.next');
    Route::post('/planning/period', [PlanningController::class, 'index'])->name('planning.period');
    Route::get('/planning/{id}', [PlanningController::class, 'edit'])->name('planning.edit');
    Route::put('/planning/{id}', [PlanningController::class, 'update'])->name('planning.update');
    Route::delete('/planning/{id}', [PlanningController::class, 'destroy'])->name('planning.delete');
    Route::post('/planning/create', [PlanningController::class, 'create'])->name('planning.create');
    Route::post('/planning/store', [PlanningController::class, 'store'])->name('planning.store');

    Route::get('/billing', [BillingController::class, 'billing'])->name('billing.index');
    Route::get('/billing/previous', [BillingController::class, 'previous'])->name('billing.previous');
    Route::get('/billing/next', [BillingController::class, 'next'])->name('billing.next');
    Route::get('/billing/byDate', [BillingController::class, 'byDate'])->name('billing.byDate');
    Route::post('/billing/set_bill', [BillingController::class, 'setBill'])->name('billing.setBill');

    Route::resource('/program', ProgramController::class);

    Route::get('/invoice/payed/{invoice_id}', [InvoiceController::class, 'payed'])->name('invoice.payed');
    Route::resource('/invoice', InvoiceController::class);
    Route::resource('/documents', DocumentController::class);
    
    Route::get('/company/', [CompanyController::class, 'show'])->name('company.show');
    Route::get('/company/{company_id}', [CompanyController::class, 'show'])->name('company.show_any');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/switch', [ProfileController::class, 'switch'])->name('profile.switch');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
