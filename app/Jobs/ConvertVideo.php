<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Traits\FFmpegTranscoding;

class ConvertVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FFmpegTranscoding;
	
	private $data = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        //
		$this->data = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$curl = curl_init();
		
		\Log::info('Cron has sent request for tanscoding');

		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://ec2-3-135-237-146.us-east-2.compute.amazonaws.com/api/convert",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($this->data),
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json"
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
    }
}
