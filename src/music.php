<?php

require __DIR__ . "/../gracenote-rhythm/GracenoteRhythm.class.php";
require __DIR__ . '/../helper/MusicHelper.php';

$app->get('/music', function () use ($app) {
    $input = $app->request()->get();

    if (!isset($input["moodid"])) {
        echo jsonResponse("error [Mood id is nothing]", array());
        return;
    }

    if (!isMoodIdExist($input["moodid"])) {
        echo jsonResponse("error [Mood id is wrong]", array());
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

    if (isset($youtube_id)) {
        $data = array(
            "artist" => $artist,
            "title" => $music_title,
            "youtube_id" => $youtube_id
        );

        echo jsonResponse("success", $data);
        return;
    }
    else {
        echo jsonResponse("error [No youtube_id]", array());
        return;
    }

    // TODO 文字数多いやつのけた方がいいかも
    // TODO UNICODEエンコードしてない
});
