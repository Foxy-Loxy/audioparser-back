<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Parser;
use PhpParser\Node\Expr\Array_;

class mp3cc extends Parser {

    function search($query, $page = 0){
        $this->setTrackSelector('li[data-mp3]');
        $this->setDurationSelector('em > span.playlist-duration');
        $this->setTitleSelector('li[data-mp3] > h2.playlist-name > em > a');
        $this->setArtistSelector('li[data-mp3] > h2.playlist-name > b > a');
        $this->setSearchPageUrl('http://mp3cc.com/search/f/SEARCH/page/PAGE');
        $this->setSearchPageUrl('http://mp3cc.com/search/f/SEARCH');
        $colection = $this->commenceSearch($query, $page);

        $urls = $colection['track_urls']->extract(array('data-mp3'));

//        dd($colection);

        $durations = array();
        foreach ($colection['track_duration'] as $element) {
            $minutes = 0;
            $seconds = 0;
            $str_time = $element->nodeValue;
            $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "$1:$2", $str_time);
            sscanf($str_time, "%d:%d", $minutes, $seconds);
            $time_seconds = $minutes * 60 + $seconds;
            array_push($durations, $time_seconds);
        }

        $titles = array();
        foreach ($colection['track_titles'] as $element) {
            array_push($titles, $element->nodeValue);
        };

        $artists = array();
        foreach ($colection['track_artists'] as $element) {
            array_push($artists, $element->nodeValue);
        };

        $responce = array();
        for ($i = 0; array_key_exists($i, $urls); $i++) {
            $track['origin'] = 'mp3cc';
            $track['artist'] = $artists[$i];
            $track['url'] = $urls[$i];
            $track['title'] = $titles[$i];
            $track['duration'] = $durations[$i];
            $track['thumbnail'] = null;
            array_push($responce, $track);
        }


        return $responce;
    }
}

?>