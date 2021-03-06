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

use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Ramsey\Laravel\OAuth2\Instagram\Facades\Instagram;

Route::get('/', function () {
    return view('welcome');
});

Route::get('instagram', 'InstagramController@index');

Route::get('instalogin', function (){
    $ig = new \InstagramAPI\Instagram();

    $username = 'horoofnegar';
    $password = 'Serint9263';

    $ig->setUser($username, $password);
    $ig->login();

//    dd($ig);

    $metadata = [
        'caption' => 'تست آپلود از داشبورد توی صفحه اینستا گرام',
//        'location' => $location, // $location must be an instance of Location class
    ];

    $videoFile = "C:\\video.mp4";
    $photoFile = "C:\\image.jpg";
    $mediaId = "1527404675266099399";

//    $ffmpeg = FFMpeg::create([
//        'ffmpeg.binaries'  => 'C:\ffmpeg\bin\ffmpeg.exe', // the path to the FFMpeg binary
//        'ffprobe.binaries' => 'C:\ffmpeg\bin\ffprobe.exe', // the path to the FFProbe binary
//        'timeout'          => 3600, // the timeout for the underlying process
//        'ffmpeg.threads'   => 12,   // the number of threads that FFMpeg should use
//    ]);
//    $video = $ffmpeg->open($videoFile);


// if you want only a caption, you can simply do this:
//    $metadata = ['caption' => 'My awesome caption'];

//    $result = $ig->uploadTimelineVideo($videoFile, $metadata);
//    $result = $ig->uploadTimelinePhoto($photoFile, $metadata);
    $result = $ig->deleteMedia($mediaId);

    dd($result);
});

Route::get('auth/instagram', function (Request $request){
    $authUrl = Instagram::authorize(['scope' => 'public_content'], function ($url, $provider) use ($request) {
        $request->session()->put('instagramState', $provider->getState());
        return $url;
    });
//    dd($authUrl);
    return redirect()->away($authUrl);
});

Route::get('auth/instagram/callback', function (Request $request){
    if (!$request->has('state') || $request->state !== $request->session()->get('instagramState')) {
        abort(400, 'Invalid state');
    }

    if (!$request->has('code')) {
        abort(400, 'Authorization code not available');
    }

    $token = Instagram::getAccessToken('authorization_code', [
        'code' => $request->code,
    ]);

    $request->session()->put('instagramToken', $token);

    $instagramToken = $request->session()->get('instagramToken');

    $instagramUser = Instagram::getResourceOwner($instagramToken);
    $name = $instagramUser->getName();
    $bio = $instagramUser->getDescription();

    $feedRequest = Instagram::getAuthenticatedRequest(
        'GET',
        'https://api.instagram.com/v1/users/self/media/recent/',
        $instagramToken
//        [
//            'MAX_ID' => '10',
//            'MIN_ID' => '5',
//            'COUNT' => '6'
//        ]
    );

//    dd($feedRequest);

    $client = new \GuzzleHttp\Client();
    try{
        $feedResponse = $client->send($feedRequest);
    }catch (\Exception $exception){
        dd($exception->getMessage());
    }

//    dd($feedResponse);

    $instagramFeed = json_decode($feedResponse->getBody());

    dd($instagramFeed);

});