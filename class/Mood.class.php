<?php
class Mood {
    private $mood = array(
        array(
            "moodid" => 65322, 
            "moodname" => "Peaceful",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 65323, 
            "moodname" => "Romantic",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 65324, 
            "moodname" => "Sentimental",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 42942, 
            "moodname" => "Tender",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 42946, 
            "moodname" => "Easygoing",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 65325, 
            "moodname" => "Yearning",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 42954, 
            "moodname" => "Sophisticated",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 42947, 
            "moodname" => "Sensual",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 65326, 
            "moodname" => "Cool",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 65327, 
            "moodname" => "Gritty",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 42948, 
            "moodname" => "Somber",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 42949, 
            "moodname" => "Melancholy",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 65328, 
            "moodname" => "Serious",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 65329, 
            "moodname" => "Brooding",
            "attribute" => "negative"
        ),
        array(
            "moodid" => 42953, 
            "moodname" => "Fiery",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 42955, 
            "moodname" => "Urgent",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 42951, 
            "moodname" => "Defiant",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 42958, 
            "moodname" => "Aggressive",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 65330, 
            "moodname" => "Rowdy",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 42960, 
            "moodname" => "Excited",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 42961, 
            "moodname" => "Energizing",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 42945, 
            "moodname" => "Empowering",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 65331, 
            "moodname" => "Stirring",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 65332, 
            "moodname" => "Lively",
            "attribute" => "positive"
        ),
        array(
            "moodid" => 65333, 
            "moodname" => "Upbeat",
            "attribute" => "positive"
        ),
    );

    function __construct() {
    }

    public function getIds() {
        return array_map(function ($mood) {
            return $mood['moodid'];
        }, $this->mood);
    }

    public function isMoodIdExist($moodid) {
        $moodids = $this->getIds();
           
        return in_array($moodid, $moodids);
    }

    public function getPositiveMusicId() {
        return array_filter($this->mood,
            function ($mood) {
                return $mood["attribute"] === "positive";
            });
    }

    public function getNegativeMusicId() {
        return array_filter($this->mood,
            function ($mood) {
                return $mood["attribute"] === "negative";
            });
    }

    public function choosePositiveMusicIdByRnd() {
        $m = $this->getPositiveMusicId();
        shuffle($m);
        return $m[0];
    }

    public function chooseNegativeMusicIdByRnd() {
        $m = $this->getNegativeMusicId();
        shuffle($m);
        return $m[0];
    }
}
