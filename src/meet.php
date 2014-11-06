<?php

require __DIR__ . '/../helper/MeetHelper.php';

use Parse\ParseClient;
use Parse\ParseUser;
use Parse\ParseQuery;
use Parse\ParseException;
use Parse\ParseObject;
use Parse\ParseACL;
use Parse\ParsegeoPoint;

ParseClient::initialize('LicvGZYQ3x9rDtfiDnaNy42GmIJdP0TuoVBJBFZi', 'QmaCbGyfU0chwYJ4n77cM1lv3pZeeVxNfa0FGrLE', 'pwLv0m1SioJBnuqlI3mvZ0Cv6jDRoC0BIRImMgGO');
// ParseClientのデフォルトのtimestampがUTCのため
date_default_timezone_set('UTC');

$app->get('/meet', function () use ($app) {
    // Sample input data
    // 3RWoUwexI
    // 2014-11-03T02:09:24.620Z
    // 2014-11-03T02:09:39.895Z

    $input = $app->request()->get();

    if (!issetAllParams($input, array("user_id", "start_date", "end_date"))) {
        echo jsonResponse("NotParameterError", array());
        return;
    }

    $input_user_id= $input["user_id"];
    $input_start_date = $input["start_date"];
    $input_end_date = $input["end_date"];

    // Dateが正しいかチェック
    try {
        $start_date = new DateTime($input_start_date); // DEBUG
        $end_date = new DateTime($input_end_date);   // DEBUG
    } catch (Exception $e) {
        echo jsonResponse("$e", array());
        return;
    }

    // Userが正しいかチェック
    try {
        // userの取得
        $user_id = $input_user_id;
        $user_query = ParseUser::query();
        $my_user = $user_query->get($user_id);
    } catch (Exception $e) {
        echo jsonResponse("$e", array());
        return;
    }

    // ユーザの期間内のGPSデータ取得
    $query = new ParseQuery("Tag");
    $query = getQueryBetweenDate($query, $start_date, $end_date);
    $query->EqualTo("user", $my_user);
    $user_geo_objects = $query->find();

    $between_min = 5; // 5分間隔

    // GeoObjectの数をよしなに減らす
    $user_geo_objects = cutGeoObjectFromBetweenTime($user_geo_objects, $start_date, $end_date, $between_min);
    
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

    echo jsonResponse("success", $user_musics);
    
    // 10秒かかかるww 
});

// test uri
$app->get('/test', function () use ($app) {
});

