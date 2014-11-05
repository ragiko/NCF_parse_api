<?php

require __DIR__ . "/../gracenote-rhythm/GracenoteRhythm.class.php";
require __DIR__ . '/../helper/MusicHelper.php';

use Parse\ParseObject;
use Parse\ParseUser;

$app->get('/music', function () use ($app) {
    $input = $app->request()->get();

    if (!issetAllParams($input, array("moodid", "userid"))) {
        echo jsonResponse("NotParameterError", array());
        return;
    }

    if (!isMoodIdExist($input["moodid"])) {
        echo jsonResponse("error [Mood id is wrong]", array());
        return;
    }

    $moodid = $input["moodid"];
    $parse_user_id = $input["userid"];

    $clientID  = "3425280"; // Put your Client ID here.
    $clientTag = "CC3C40AF6BD3CB6C78CE6D5468603199"; // Put your Client Tag here.

    /* You first need to register your client information in order to get a userID.
        Best practice is for an application to call this only once, and then cache the userID in
        persistent storage, then only use the userID for subsequent API calls. The class will cache
        it for just this session on your behalf, but you should store it yourself. */
    try {
        $api = new Gracenote\WebAPI\GracenoteRhythm($clientID, $clientTag); // If you have a userID, you can specify as third parameter to constructor.
        $userID = $api->register();

        $api->setCountry("jpn");
        $api->setLanguage("jpn");
    
        $musics = $api->createStationFromMood($moodid)["ALBUM"];
    } catch (Exception $e) {
        echo jsonResponse("error [$e]", array());
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

    if (!isset($youtube_id)) {
        echo jsonResponse("error [No youtube_id]", array());
        return;
    }

    $data = array(
        "artist" => $artist,
        "title" => $music_title,
        "youtube_id" => $youtube_id
    );

    // User取得
    try {
        // userの取得
        $user_query = ParseUser::query();
        $my_user = $user_query->get($parse_user_id);
    } catch (Exception $e) {
        echo jsonResponse("$e", array());
        return;
    }

    // Playlistにデータを挿入
    try {
        $play_list = new ParseObject("PlayList");
        $play_list->set("artist_name", $artist);
        $play_list->set("music_title", $music_title);
        $play_list->set("youtube_id", $youtube_id);
        $play_list->set("user", $my_user);
        $play_list->set("share", false);
        $play_list->save();
    } catch (Exception $e) {
        echo jsonResponse("$e", array());
        return;
    }

    echo jsonResponse("success", $data);
    
    // TODO 文字数多いやつのけた方がいいかも
    // TODO UNICODEエンコードしてない
});


