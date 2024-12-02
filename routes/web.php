<?php

use App\Filament\Resources\UploadRevisionResource\Pages\UploadRevision;
use App\Http\Controllers\ReviewController;
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

Route::get('/review/reject/{review}', [ReviewController::class, 'rejectReview'])->name('review.reject');
Route::get('/', function () {
    return redirect('/admin/login');
});
// Route::get('/admin/upload-revision', UploadRevision::class)
//     ->middleware(['auth', 'role:author'])
//     ->name('author.upload-revision');