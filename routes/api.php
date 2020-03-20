<?php 
Route::post('/test', 'Admin\MovieController@startJob');
Route::post('/convert', 'Admin\MovieController@movieUpload');
Route::post('/upload', 'UploadController@upload');