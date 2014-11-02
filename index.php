<?php
require 'vendor/autoload.php';
use Parse\ParseClient;
use Parse\ParseUser;
use Parse\ParseQuery;
use Parse\ParseException;
use Parse\ParseObject;
use Parse\ParseACL;
use Parse\ParseGeoPoint;

ParseClient::initialize('LicvGZYQ3x9rDtfiDnaNy42GmIJdP0TuoVBJBFZi', 'QmaCbGyfU0chwYJ4n77cM1lv3pZeeVxNfa0FGrLE', 'pwLv0m1SioJBnuqlI3mvZ0Cv6jDRoC0BIRImMgGO');

// ==
// $userObject = ParseUser::logIn("my name", "my pass");
// $gps = new ParseGeoPoint(37.708813, -122.526398);
// $userObject->set("location", $gps);
// $userObject->save();

$app = new \Slim\Slim();

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";

    $query = new ParseQuery("Tag");
    try {
          $r = $query->find();
          print_r($r);
    } catch (ParseException $ex) {
    }
});

$app->run();

