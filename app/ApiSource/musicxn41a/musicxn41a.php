<?php

namespace App\ApiSource\musicxn41a;

use App\ApiSource\Parse\Parser;

class musicxn41a extends Parser{

    public function search($query, $page = 0){
        $this->setTrackSelector('ul.playlist > li[data-mp3]');
        $this->setDurationSelector('em > span.playlist-duration');
        $this->setTitleSelector('ul.playlist > li[data-mp3] > h2.playlist-name > em');
        $this->setArtistSelector('ul.playlist > li[data-mp3] > h2.playlist-name > b');
        $this->setSearchPageUrl('http://music.xn--41a.ws/search/SEARCH/PAGE');
        $this->setSearchUrl('http://music.xn--41a.ws/search/SEARCH');
        $this->setThumbnailSelector('div.playlist-btn > img');
        $collection = $this->commenceSearch($query, $page);

        $urls = $collection['track_urls']->extract(array('data-mp3'));
        for ($i = 0; array_key_exists($i, $urls); $i++)
            $urls[$i] = 'http://music.xn--41a.ws' . $urls[$i];

        $thumbnails = $collection['track_thumbnail']->extract(array('src'));

        $durations = array();

        foreach ($collection['track_duration'] as $element) {
            $time_seconds = $this->timeStringToSeconds($element->nodeValue, 'hh:mm:ss');
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
        $response = $this->createResponseArray('music.я.ws',$artists, $titles, $urls, $durations, $thumbnails);


        return $response;

    }

}

?>