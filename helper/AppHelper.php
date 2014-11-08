<?php

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;

function jsonResponse($status_str, $data_array) {
    return json_encode(array("status" => $status_str, "data" => $data_array));
}

// リクエストパラメータ内に欲しいものが全部存在しているか?
// $req_params: リクエストパラメータそのもの
// $check_params: チェックしたいパラメータ array(hoge1, hoge2, ...)
function issetAllParams($req_params, $check_params) {
    $req_param_keys = array_keys($req_params);
    
    // check empty
    if (!$req_param_keys) {
        return false;
    }

    sort($req_param_keys);
    sort($check_params);

    return ($check_params == $req_param_keys); 
}

function prePr($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}

// プレイリストの中にmusic(youtube id)が存在しているか
function existPlayList($youtube_id, $play_list_obj) {
    $exist_youtube_ids = [];

    foreach ($play_list_obj as $music) {
        $exist_youtube_ids[] = $music->get("youtube_id");
    }

    // TODO: クラス作るべき
    return in_array($youtube_id, $exist_youtube_ids);
}

// 音楽をPlayListに保存
// 情報がかぶっている物は入れない
// ちゃんとtry catchで囲むべし
// $music {artist: ..., title: ..., youtube_id:... }
function insertMusicByUserId($user_id, $music) {
    // User取得
    $user_query = ParseUser::query();
    $my_user = $user_query->get($user_id);

    // Playlistにデータを挿入
    // Playlistにデータがあるかどうかを確認
    $query = new ParseQuery("PlayList");
    $query->equalTo("user", $my_user);
    $exist_play_list = $query->find();

    if (!existPlayList($music["youtube_id"], $exist_play_list)) {
        $play_list = new ParseObject("PlayList");
        $play_list->set("artist_name", $music["artist"]);
        $play_list->set("music_title", $music["title"]);
        $play_list->set("youtube_id", $music["youtube_id"]);
        $play_list->set("user", $my_user);
        $play_list->set("share", false);
        $play_list->save();
    }
}
