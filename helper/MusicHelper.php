<?php

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

