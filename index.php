<?php
require 'vendor/autoload.php';
include("./gracenote-rhythm/GracenoteRhythm.class.php");

use Parse\ParseClient;
use Parse\ParseUser;
use Parse\ParseQuery;
use Parse\ParseException;
use Parse\ParseObject;
use Parse\ParseACL;
use Parse\ParsegeoPoint;

ParseClient::initialize('LicvGZYQ3x9rDtfiDnaNy42GmIJdP0TuoVBJBFZi', 'QmaCbGyfU0chwYJ4n77cM1lv3pZeeVxNfa0FGrLE', 'pwLv0m1SioJBnuqlI3mvZ0Cv6jDRoC0BIRImMgGO');

$app = new \Slim\Slim();

// 時刻と時刻の間に挿入されたデータを取得するクエリを発行
function getQueryBetweenDate($query, $start_date, $end_date) {
    $start = clone $start_date;
    $end = clone $end_date;
    $q = clone $query;

    $q->lessThanOrEqualTo("createdAt", $end);
    $q->greaterThanOrEqualTo('createdAt', $start);

    return $q; 
}


// 時刻から前後n分に挿入されたデータを取得するクエリ発行
function getQueryBetweenMinutes($query, $date, $between_min) {
    $start = clone $date;
    $end = clone $date;
    $q = clone $query;

    $start->modify("+$between_min minutes");
    $q->lessThanOrEqualTo("createdAt", $start);

    $end->modify("-$between_min minutes");
    $q->greaterThanOrEqualTo('createdAt', $end);

    return $q; 
}

$app->get('/', function () {
    echo "Start";

    // request
    $user_id = "3RWoUwexIC";
    $start_date = new DateTime("2014-11-03T02:09:24.620Z"); // DEBUG
    $end_date = new DateTime("2014-11-03T02:09:39.895Z");   // DEBUG

    // userの取得
    $user_query = ParseUser::query();
    $my_user = $user_query->get($user_id);

    // ユーザの期間内のGPSデータ取得
    $query = new ParseQuery("Tag");
    $query = getQueryBetweenDate($query, $start_date, $end_date);
    $query->EqualTo("user", $my_user);
    $user_geo_objects = $query->find();

    // new を loop内で行わない為に
    $_query = new ParseQuery("Tag");

    $other_user_ids = [];
    // 取得したGPSデータごとに時間と距離の近いユーザを算出
    foreach ($user_geo_objects as $user_geo_object) {
        $query = $_query;

        $date = $user_geo_object->getCreatedAt();
        $user_geo_pt = $user_geo_object->get("location");
        $between_min = 1;
        $around_kilometer = 1;

        // 時間と距離の近いユーザを算出
        $query = getQueryBetweenMinutes($query, $date, $between_min);
        $query->notEqualTo("user", $my_user);
        $query->withinKilometers("location", $user_geo_pt, $around_kilometer);
        $gps_objects = $query->find("user");

        foreach ($gps_objects as $gps_object) {
            $other_user_ids[] = $gps_object->get("user")->getObjectId();

            // DEBUG
            // echo "<pre>";
            // print_r($user_geo->getCreatedAt());
            // echo "<br>";
            // print_r($gps_object->get("user")->getObjectId());
            // echo "<br>";
            // print_r($gps_object->getCreatedAt());
            // echo "</pre>";
            // echo "<hr>";
        }
    }

    $other_user_ids = array_unique($other_user_ids);

    $user_musics = [];
    $_query = new ParseQuery("PlayList");

    foreach ($other_user_ids as $user_id) {
        // userの取得
        $user_query = ParseUser::query();
        $user = $user_query->get($user_id);

        $query = $_query;
        $query->equalTo("user", $user);
        $query->equalTo("share", true);
        // TODO: 抽出当たりのアルゴリズムを改良、とりあえず全部取る
        $playlist_objects = $query->find();

        foreach ($playlist_objects as $playlist_object) {
            $user_musics[] = array(
                "youtube_id" => $playlist_object->get("youtube_id"),
                "user_id" => $user_id
            );
        }
    }

    echo "<pre>";
    print_r($user_musics);
    echo "</pre>";
    
    // 10秒かかかるww 
});

/*
 * gracenote
 */
$app->get('/gracenote', function () use ($app) {
    $input = $app->request()->get();

    if (!isset($input["moodid"])) {
        $res = json_encode(array("status" => "error [Mood id is nothing]", "data" => array()));
        echo $res;
        return;
    }

    if (!isMoodIdExist($input["moodid"])) {
        $res = json_encode(array("status" => "error [Mood id is wrong]", "data" => array()));
        echo $res;
        return;
    }

    $moodid = $input["moodid"];

    $clientID  = "3425280"; // Put your Client ID here.
    $clientTag = "CC3C40AF6BD3CB6C78CE6D5468603199"; // Put your Client Tag here.

    try {
        /* You first need to register your client information in order to get a userID.
        Best practice is for an application to call this only once, and then cache the userID in
        persistent storage, then only use the userID for subsequent API calls. The class will cache
        it for just this session on your behalf, but you should store it yourself. */
        $api = new Gracenote\WebAPI\GracenoteRhythm($clientID, $clientTag); // If you have a userID, you can specify as third parameter to constructor.
        $userID = $api->register();

        $api->setCountry("jpn");
        $api->setLanguage("jpn");
    
        $musics = $api->createStationFromMood($moodid)["ALBUM"];
    } catch (Exception $e) {
        $res = json_encode(array("status" => "error [$e]", "data" => array()));
        echo $res;
        return;
    }

    foreach ($musics as $music) {
        $artist = $music["ARTIST"][0]["VALUE"];
        $music_title = $music["TITLE"][0]["VALUE"];
        $youtube_id = getVideoId($artist, $music_title);

        if ($youtube_id !== "error!") {
            break;
        }
    }

    if (isset($youtube_id)) {
        $res = json_encode(array(
            "status" => "success", 
            "data" => 
            array(
                "artist" => $artist,
                "title" => $music_title,
                "youtube_id" => $youtube_id
            )
        ));
        echo $res;
        return;
    }
    else {
        $res = json_encode(array("status" => "error [No youtube_id]", "data" => array()));
        echo $res;
        return;
    }

    // TODO 文字数多いやつのけた方がいいかも
    // TODO クラスかすべし
});

function isMoodIdExist($moodid) {
    $moodids = array(
        65322,
        65323,
        65324,
        42942,
        42946,
        65325,
        42954,
        42947,
        65326,
        65327,
        42948,
        42949,
        65328,
        65329,
        42953,
        42955,
        42951,
        42958,
        65330,
        42960,
        42961,
        42945,
        65331,
        65332,
        65333
    );

    return in_array($moodid, $moodids);
}


/*
 * youtube
 */
function getVideoId($artist, $musicTitle){
    // This code will execute if the user entered a search query in the form
    // and submitted the form. Otherwise, the page displays the form above.
    if ($artist != '' && $musicTitle != '') {
        // Call set_include_path() as needed to point to your client library.
        $serchQuery = $artist." ".$musicTitle;
        $maxResults = 1;
        /*
         * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
         * Google Developers Console <https://cloud.google.com/console>
         * Please ensure that you have enabled the YouTube Data API for your project.
         */
        $DEVELOPER_KEY = 'AIzaSyDOWXXqjSREftoZ78WxCJNHWvVyQcL2ogc';
        $client = new Google_Client();
        $client->setDeveloperKey($DEVELOPER_KEY);
        // Define an object that will be used to make all API requests.
        $youtube = new Google_Service_YouTube($client);
        // Call the search.list method to retrieve results matching the specified
        // query term.
        $searchResponse = $youtube->search->listSearch('id,snippet', array(
            'q' => $serchQuery,
            'maxResults' => $maxResults,
        ));
        // Add each result to the appropriate list, and then display the lists of
        // matching videos, channels, and playlists.
        foreach ($searchResponse['items'] as $searchResult) {
            switch ($searchResult['id']['kind']) {
            case 'youtube#video':
                return $searchResult['id']['videoId'];
            }
        }
    }
    return "error!";
}

$app->run();
