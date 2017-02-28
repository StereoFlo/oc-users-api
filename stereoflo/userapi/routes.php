<?php

// get params. Used by cool hackers
Route::get('/api/users/{act}', 'Stereoflo\Userapi\Controllers\FrontController@stub')->where('act', '[A-Za-z]+');

// post params
Route::post('/api/users/login', 'Stereoflo\Userapi\Controllers\FrontController@login');
Route::post('/api/users/logout', 'Stereoflo\Userapi\Controllers\FrontController@logout');
Route::post('/api/users/update', 'Stereoflo\Userapi\Controllers\FrontController@update');
Route::post('/api/users/register', 'Stereoflo\Userapi\Controllers\FrontController@register');
Route::post('/api/users/resetPassword', 'Stereoflo\Userapi\Controllers\FrontController@resetPassword');