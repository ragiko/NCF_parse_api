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

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";

    // request
    $object_name = "Tag";
    $user_id = "3RWoUwexIC";
    $start_date = new DateTime("2014-11-03T02:09:24.620Z");
    $end_date = new DateTime("2014-11-03T02:09:39.895Z");

    // userの取得
    $user_query = ParseUser::query();
    $user = $user_query->get($user_id);

    // ユーザの期間内のGPSデータ取得
    $query = new ParseQuery($object_name);
    $query = getQueryBetweenDate($query, $start_date, $end_date);
    $query->EqualTo("user", $user);
    $user_geos = $query->find();

    // new を loop内で行わない為に
    $query = new ParseQuery($object_name);
    $_query = clone $query;

    // 取得したGPSデータごとに時間と距離の近いユーザを算出
    foreach ($user_geos as $user_geo) {
        $query = $_query;

        $date = $user_geo->getCreatedAt();
        $user_geo_pt = $user_geo->get("location");
        $between_min = 1;

        // 時間と距離の近いユーザを算出
        $query = getQueryBetweenMinutes($query, $date, $between_min);
        $query->notEqualTo("user", $user);
        $query->withinKilometers("location", $user_geo_pt, 1);
        $gps_objects = $query->find("user");

        foreach ($gps_objects as $gps_object) {
            echo "<pre>";
            print_r($user_geo->getCreatedAt());
            echo "<br>";
            print_r($gps_object->get("user")->getObjectId());
            echo "<br>";
            print_r($gps_object->getCreatedAt());
            echo "</pre>";
            echo "<hr>";
        }
    }

    // $between_min = 3;
    // $date = new DateTime("2014-11-03T00:19:04.614Z");

    // $query = getQueryBetweenMinutes($query, $date, $between_min);

    // $r = $query->find();

    // echo "<pre>";
    // print_r($r);
    // echo "</pre>";
});

$app->run();

