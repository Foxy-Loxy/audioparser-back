<?php

namespace App\ApiSource\mp3cc;

use App\ApiSource\Parse\Parser;

class mp3cc extends Parser {

    function search($query, $page = 0){
        $this->setTrackSelector('li[data-mp3]');
        $this->setDurationSelector('em > span.playlist-duration');
        $this->setTitleSelector('li[data-mp3] > h2.playlist-name > em > a');
        $this->setArtistSelector('li[data-mp3] > h2.playlist-name > b > a');
        $this->setSearchPageUrl('http://mp3cc.com/search/f/SEARCH/page/PAGE');
        $this->setSearchPageUrl('http://mp3cc.com/search/f/SEARCH');
        $collection = $this->commenceSearch($query, $page);

        $urls = $collection['track_urls']->extract(array('data-mp3'));

        $durations = array();

        foreach ($collection['track_duration'] as $element) {
            $time_seconds = $this->timeStringToSeconds($element->nodeValue);
            array_push($durations, $time_seconds);
        }

        $titles = array();
        foreach ($collection['track_titles'] as $element) {
            array_push($titles, $element->nodeValue);
        };

        $artists = array();
        foreach ($collection['track_artists'] as $element) {
            array_push($artists, $element->nodeValue);
        };

        $response = $this->createResponseArray('mp3cc', $artists, $titles, $urls, $durations, null);


        return $response;
    }
}

?>