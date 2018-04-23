<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Parser;

class mp3cc extends Parser {

    function search($query, $page = 0){
        $this->setTrackSelector('li[data-mp3]');
        $this->setDurationSelector('em > span.playlist-duration');
        $this->setTitleSelector('li[data-mp3] > h2.playlist-name > em > a');
        $this->setArtistSelector('li[data-mp3] > h2.playlist-name > b > a');
        $this->setSearchPageUrl('http://mp3cc.com/search/f/SEARCH/page/PAGE');
        $this->setSearchPageUrl('http://mp3cc.com/search/f/SEARCH');
        $colection = $this->commenceSearch($query, $page);
        dd($colection);
    }
}

?>