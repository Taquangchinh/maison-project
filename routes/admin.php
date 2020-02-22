<?php

Route::get('/', 'Admin\AdminController@home')->name('home');

Route::get('image-manager', 'Admin\AdminController@image_manager')->name('image_manager');

Route::get('file-manager', 'Admin\AdminController@file_manager')->name('file_manager');


Route::get('sign-out', array(
    'as' => 'account-sign-out',
    'uses' => 'Admin\AdminController@getSignOut'
));


Route::group(['prefix' => 'users'], function(){
    Route::get('/', 'Admin\UserController@index')->name('admin_users');

    Route::group(['middleware' => ['backend']],function(){

        Route::get('/{id}', 'Admin\UserController@edit')->name('admin_user');
        Route::post('/store/{id}', 'Admin\UserController@store')->name('admin_user_store');


        Route::get('/delete/{id}', 'Admin\UserController@delete')->name('admin_user_delete');
        // Route::post('/profile', 'UserController@profile_update')->name('profile_update');
        // Route::post('/{id}',		'UserController@update')->name('user_update');
        Route::get('/delete-comfirm/{id}',      'Admin\UserController@delete_comfirm')->name('admin_user_delete_comfirm');
    });

});

Route::group(['prefix' => 'articles'], function(){
    Route::get('/', 'Admin\ArticleController@index')->name('admin_articles');


    Route::group(['middleware' => ['article.backend']],function(){
        Route::get('/{id}', 'Admin\ArticleController@edit')->name('admin_article');
        Route::post('/store/{id}', 'Admin\ArticleController@store')->name('admin_article_store');
        Route::get('/delete/{id}', 'Admin\ArticleController@delete')->name('admin_article_delete');
        Route::get('/delete-comfirm/{id}',      'Admin\ArticleController@delete_comfirm')->name('admin_article_delete_comfirm');
    });
});

Route::group(['prefix' => 'slides'], function(){
    Route::get('/', 'Admin\SlideController@index')->name('admin_slides');
    Route::get('/{id}', 'Admin\SlideController@edit')->name('admin_slide');
});

Route::group(['prefix' => 'settings'], function(){
    Route::get('images', 'Admin\SettingController@index')->name('admin_settings');
});


Route::group(['prefix' => 'manager'], function(){
    Route::get('images', 'Admin\AdminController@image_manager')->name('image_manager');

    Route::get('filess', 'Admin\AdminController@file_manager')->name('file_manager');


});



