<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Librarian\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Librarian\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Librarian\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Librarian\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Librarian\Auth\NewPasswordController;
use App\Http\Controllers\Librarian\Auth\PasswordController;
use App\Http\Controllers\Librarian\Auth\PasswordResetLinkController;
use App\Http\Controllers\Librarian\Auth\RegisteredUserController;
use App\Http\Controllers\Librarian\Auth\VerifyEmailController;
use App\Http\Controllers\Librarian\BooksController;
use App\Http\Controllers\Librarian\BorrowsController;
use App\Http\Controllers\Librarian\GenresController;
use App\Http\Controllers\Librarian\RengesController;
use App\Http\Controllers\Librarian\LibrarianController;
use App\Models\Librarian;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
});

Route::middleware('auth:librarian')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});


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
Route::get('/home', [LibrarianController::class, 'home'])
->middleware(['auth:librarian', 'verified'])->name('home');


Route::resource('books', BooksController::class)
->middleware('auth:librarian')->except('index');

Route::prefix('books')
->middleware('auth:librarian')
->group(function(){
    Route::get('/filteredByGenre/{genre}', [BooksController::class, 'filteredByGenreIndex'])->name('books.filteredByGenreIndex');
    Route::get('/filteredByTitle/{title}', [BooksController::class, 'filteredByTitleIndex'])->name('books.filteredByTitleIndex');
    Route::get('/filteredByAuthors/{authors}', [BooksController::class, 'filteredByAuthorsIndex'])->name('books.filteredByAuthorsIndex');
});

Route::prefix('expired-books')
->middleware('auth:librarian')
->group(function(){
    Route::get('/index', [BooksController::class, 'expiredBooksIndex'])->name('expired-books.index');
    Route::post('/destroy/{book}', [BooksController::class, 'expiredBooksDestroy'])->name('expired-books.destroy');
    Route::post('/restore/{book}', [BooksController::class, 'expiredBooksRestore'])->name('expired-books.restore');
});


Route::resource('genres', GenresController::class)
->middleware('auth:librarian');

Route::prefix('expired-genres')
->middleware('auth:librarian')
->group(function(){
    Route::get('/index', [GenresController::class, 'expiredGenresIndex'])->name('expired-genres.index');
    Route::post('/destroy/{genre}', [GenresController::class, 'expiredGenresDestroy'])->name('expired-genres.destroy');
    Route::post('/restore/{genre}', [GenresController::class, 'expiredGenresRestore'])->name('expired-genres.restore');
});

Route::prefix('borrows')
->middleware('auth:librarian')
->group(function(){
    Route::get('/index', [BorrowsController::class, 'index'])->name('borrows.index');
    Route::get('/show/{borrow}', [BorrowsController::class, 'show'])->name('borrows.show');
    Route::post('/acceptPending', [BorrowsController::class, 'acceptPending'])->name('borrows.acceptPending');
    Route::post('/rejectPending', [BorrowsController::class, 'rejectPending'])->name('borrows.rejectPending');
    Route::post('/acceptReturning', [BorrowsController::class, 'acceptReturning'])->name('borrows.acceptReturning');
});



Route::middleware('auth:librarian')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});