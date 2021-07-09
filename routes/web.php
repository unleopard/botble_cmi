<?php


Route::group(['namespace' => 'Botble\Cmi\Http\Controllers', 'middleware' => ['core']], function () {

    Route::group(['prefix' => 'checkout'], function () {
        Route::group(['prefix' => 'cmi'], function () {
            Route::post('/success', 'CMIController@success'); // checkout/cmi/success
            Route::post('/fail', 'CMIController@fail'); // checkout/cmi/fail
            Route::post('/callback', 'CMIController@callback'); // checkout/cmi/fail
            Route::get('/testes', 'CMIController@testes'); // checkout/cmi/fail
        });
    });
});



