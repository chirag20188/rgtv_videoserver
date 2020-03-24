<?php

namespace App\Http\Controllers\Admin;

use App\Casts;
use App\Casts_rules;
use App\Http\Controllers\Controller;
use App\Movie;
use App\Subtitle;
use App\Tmdb;
use App\Traits\FFmpegTranscoding;
use App\Video;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use \Done\Subtitles\Subtitles;
use Illuminate\Support\Facades\App;
use App\Jobs\ConvertVideo;

class MovieController extends Controller
{
    use FFmpegTranscoding;

    public $number = 0;
    public $name;   

    /**
     *  Upload To local
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function movieUpload(Request $request)
    {
		$request_param = $request->all(); 
		 
        \Log::info('movieUpload -- username', [shell_exec('who')]);

		// Decode Persets Json
		$resolution = json_decode($request_param['resolution'], true);
		
		if ($resolution[0]['Container'] === 'ts') {

			// Create M3U8 File name
			$randomName = str_random(20);
			$newNameM3U8 = $randomName . '.m3u8';

			// Upload video to Storage
			//$file = $request_param->file('video');
			$path = 'temp_movie/'.$request_param['video_url'];
			$outputPath = 'movies/' . $request_param['m_name'] .'/';


			// FFmpegTranscoding Video
			$transcoding = $this->transcodingToHLS($path, $resolution, $outputPath, $randomName, 'Video Convert To HLS Playlist Wait, its take time', $request_param['tmdb_id']);

			// Store video data
			if ($transcoding) {        
				\Log::info('Transcoding done');
				
				$video_array = array(
					"id" => $request_param['id'], 
					"m_name" => $request_param['m_name'], 
					"formate" => $newNameM3U8, 
					"resolution" => isset($resolution[0]['Resolution'])?$resolution[0]['Resolution']:'720',
				);
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => "https://rg-tv.com/api/v1/save/transcoded/video",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => json_encode($video_array),
				  CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json"
				  ),
				));

				$response = curl_exec($curl);

				curl_close($curl);
				
				unlink(storage_path('app/public/'.$path));

			} else {
				\Log::info('Transcoding failed');
				// Error
				//return $transcoding;
			}
		}
		//   Storage::deleteDirectory('public/temp');
	} 	

	public function startJob(Request $request) {
		\Log::info('Transcoding start');
		
        \Log::info('startJob -- username', [shell_exec('who')]);
		// job for convert video to selected formate
		dispatch(new ConvertVideo($request->all()));
		
		return response()->json(['status' => 'success', 'message' => 'We have started transcoding process.', 'id' => $request->id], 200);
	}	 
}
