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
        $start_date = new DateTime(jpnDateStrToUtcDateStr($input_start_date)); // DEBUG
        $end_date = new DateTime(jpnDateStrToUtcDateStr($input_end_date));   // DEBUG
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

    $between_min = 8; // 10分間隔

    // GeoObjectの数をよしなに減らす
    $user_geo_objects = cutGeoObjectFromBetweenTime($user_geo_objects, $start_date, $end_date, $between_min);
    
    // new を loop内で行わない為に
    $_query = new ParseQuery("Tag");

    $other_user_ids = [];
    $between_min = 3;
    $around_kilometer = 1;

    // 取得したGPSデータごとに時間と距離の近いユーザを算出
    foreach ($user_geo_objects as $user_geo_object) {
        $query = $_query;

        $date = $user_geo_object->getCreatedAt();
        $user_geo_pt = $user_geo_object->get("location");

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
        $playlist_objects = $query->find();

        if (!empty($playlist_objects)) {
            // ランダムで一つ抽出
            shuffle($playlist_objects);
            $playlist_object = $playlist_objects[0];

            $user_musics[] = array(
                "user_id" => $user_id,
                "artist" => $playlist_object->get("artist_name"),
                "youtube_id" => $playlist_object->get("youtube_id"),
                "title" => $playlist_object->get("music_title")
            );
        }

        foreach ($playlist_objects as $playlist_object) {
        }
    }

    // 取得した音楽をPlaylistにインサート
    try {
        foreach ($user_musics as $m) {
            insertMusicByUserId($input_user_id, array(
                "artist" => $m['artist'],
                "title" => $m['title'],
                "youtube_id" => $m['youtube_id']
            ));
        }
    } catch (Exception $e) {
        echo jsonResponse("PlayListInsertError", array());
    }

    echo jsonResponse("success", $user_musics);
});

// test uri
$app->get('/test/meet', function () use ($app) {
    echo jpnDateStrToUtcDateStr("2014-11-05 20:53:40.0000000");
});


