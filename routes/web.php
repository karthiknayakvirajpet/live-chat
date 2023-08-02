<?php

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

//redirect routes
//Route::redirect('/', '/login');
//Route::redirect('/login', '/login');

//LoginController routes
Route::controller(App\Http\Controllers\Auth\LoginController::class)->group(function () 
{
    //registration form
    Route::get('/register', 'registerForm')->name('register.form');

    //register
    Route::post('/register', 'register')->name('register');

    //login form
    Route::get('/login', 'loginForm')->name('login.form');

    //login
    Route::post('/login', 'login')->name('login');

    //logout
    Route::any('/logout', 'logout')->name('logout');
});


//Common Routes to all Roles
Route::group(['middleware' => ['auth:sanctum', 'verified']], function()
{
    //AdminController routes
    Route::controller(App\Http\Controllers\HomeController::class)->group(function () 
    {
        //Home
        Route::get('home', 'index')->name('home');
    });


    //LiveChatController routes
    Route::controller(App\Http\Controllers\LiveChatController::class)->group(function () 
    {   
        //chat index page
        Route::get('/chat/{user_id?}', 'index')->name('chat.index');

        //sending message
        Route::post('/chat-send', 'sendMessage')->name('send.message');

        //fetch messages
        Route::get('/chat/messages/{user_chat_id}', 'getMessages');

        //clear chat history
        Route::get('/chat/clear/{customerID}', 'clearChat');
    });
});


//Support Agent Routes
Route::middleware(['auth', 'support_agent'])->group(function () 
{
    //LiveChatController routes
    Route::controller(App\Http\Controllers\LiveChatController::class)->group(function () 
    {
        //update agent active status
        Route::post('/update-status', 'updateStatus');
    });

});

//Customer Routes
Route::middleware(['auth', 'customer'])->group(function () 
{

});
