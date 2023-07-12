<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\{AuthController, UserController, EventController, RedirectController };
use App\Models\User;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1')->group(function () {

    /// Declare the heartbeat route for the API
    Route::any('/', function () {
        return response()->json(['message' => 'Welcome to Open Tickets Apis'], 200);
    });



    // Declare unauthenticated routes
    Route::group(['middleware' => 'guest'], function () {

        // Place your unauthenticated routes here
        Route::post('register', [AuthController::class, 'register'])->name('register');

        Route::post('login', [AuthController::class, 'login'])->name('login');

        Route::post('users/{id}', [UserController::class, 'show'])->name('show');

        Route::get('events/{slug}', [EventController::class, 'slug'])->name('slug');

        Route::post('events/{id}', [EventController::class, 'show'])->name('show');

        Route::get('e/{shortlink}', [EventController::class, 'redirect'])->name('redirect');

    });


    //Declare Authenticated routes
    Route::group(['middleware' => 'auth:api'], static function () {

        //User routes
        Route::prefix('users')->middleware(['role:user'])->group(function () {
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('index');
            Route::put('/{id}', [UserController::class, 'update'])->name(' update');
        });


        //Admin routes
        Route::prefix('admin')->middleware(['role:admin'])->group(function () {
            Route::apiResource('users', UserController::class)->except(['update', 'destroy']);

            Route::apiResource('events', EventController::class)->except(['show', 'slug', 'redirect']);
        });


        //Events routes
        Route::prefix('events')->group(function (){
            Route::post('/', [EventController::class, 'store'])->name('store');

            
            Route::group(['middleware' => 'isOwner'], function () {
                Route::put('/{id}', [EventController::class, 'update'])->name('update');
                Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy');
            });
        }); 
    });
});
