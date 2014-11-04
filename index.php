<?php
require 'vendor/autoload.php';

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

    print_r($user_musics);
    
    // 10秒かかかるww 
});

$app->run();

