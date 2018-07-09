<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Auth::routes();

Route::any('home', 'HomeController@index')->name('home');

/** @replies */
Route::post('replies/{reply}/favorites', 'FavoritesController@store');

/** @user */
Route::get('profiles/{user}', 'ProfilesController@show')->name('profile');

/** @threads */
Route::get('threads/create', 'ThreadsController@create');
Route::get('threads/{channel?}', 'ThreadsController@index');
Route::post('threads', 'ThreadsController@store');
Route::post('threads/{channel}/{thread}/replies', 'RepliesController@store');
Route::get('threads/{channel}/{thread}', 'ThreadsController@show');
Route::delete('threads/{channel}/{thread}', 'ThreadsController@destroy');
