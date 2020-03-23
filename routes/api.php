<?php 

    Route::group(['prefix' => 'movies'], function () {
		Route::post('/start', 'Admin\MovieController@startJob');
		Route::post('/convert', 'Admin\MovieController@movieUpload');
    });

    Route::group(['prefix' => 'series'], function () {
		Route::post('/start', 'Admin\SeriesController@startJob');
		Route::post('/convert', 'Admin\SeriesController@UploadEpisodeVideos');
    });

	Route::post('/upload', 'UploadController@upload');