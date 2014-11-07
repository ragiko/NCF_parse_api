<?php

// 時刻と時刻の間に挿入されたデータを取得するクエリを発行
function getQueryBetweenDate($query, $start_date, $end_date) {
    $start = clone $start_date;
    $end = clone $end_date;
    $q = clone $query;

    $q->lessThanOrEqualTo("createdAt", $end);
    $q->greaterThanOrEqualTo('createdAt', $start);
    $q->limit(1000); // default 100

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

// N分間の中で中央のデータを取得する事でデータ量を減らす
// 1. Date1 ~ Date2, Date2 ~ Date3 の区間ごとに存在するGPSオブジェクトをsplit
// 2. splitした区間内のGPSの中で順番的に中央の物を抽出
function cutGeoObjectFromBetweenTime($geo_objects, $start_date, $end_date, $between_min = 5) {

    $user_geo_objects = $geo_objects;
    $_start_date = clone $start_date;

    // // befour DEBUG
    // prePr(count($user_geo_objects));
    // foreach ($user_geo_objects as $user_geo_object) {
    //     prePr($user_geo_object->getCreatedAt());
    //     prePr($user_geo_object->get("location"));
    // }

    // 指定した間隔からループ回数を決定
    $since_start = $_start_date->diff($end_date);
    $m = $since_start->days * 24 * 60;
    $m += $since_start->h * 60;
    $m += $since_start->i;
    $loop_count = floor($m/$between_min);

    // 圧縮したGPS OBJECT
    $simple_gps_objs = [];

    for ($i = 0; $i < $loop_count; $i++) {
        // Date1 ~ Date2 の区間決定
        $block_start_date = clone $_start_date;
        $_start_date->add(new DateInterval("PT". $between_min . "M"));
        $block_end_date = $_start_date;

        // Date1 ~ Date2 の間のgps_objsを取得
        $gps_objs_between_date = array_filter($user_geo_objects,
            function ($geo) use ($block_start_date, $block_end_date) {
                return $geo->getCreatedAt() > $block_start_date && $geo->getCreatedAt() < $block_end_date;
            }); 

        if (empty($gps_objs_between_date)) { continue; }

        // arrayを連番にformat
        // http://www.key-p.com/blog/staff/archives/882
        $gps_objs_between_date = array_merge($gps_objs_between_date);

        // Date1 ~ Date2 の間のgps_objsで中央のデータを取得
        $index = count($gps_objs_between_date) == 0 ? 0 : floor(count($gps_objs_between_date)/2);
        $simple_gps_objs[] = $gps_objs_between_date[$index];
    }

    // // after DEBUG
    // echo '<hr>';
    // prePr(count($simple_gps_objs));
    // foreach ($simple_gps_objs as $obj) {
    //     prePr($obj->getCreatedAt());
    // }

    return $simple_gps_objs;
}

