<?php

namespace App\ApiSource\Parse;

use Mockery\Exception;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

class Parser
{
    private $search_url;
    private $page_search_url;
    private $track_selector;
    private $artist_selector;
    private $title_selector;
    private $duration_selector;
    private $thumbnail_selector;
    private $client_key;

    public function __construct()
    {

    }

    /*
     * Search URL should be given in format like: example.com/search/QUERY?page=PAGE?client_id=KEY | example.com/f/QUERY?key=KEY/page/PAGE etc.
     *
     * Where QUERY and PAGE will be replaced when method 'search' is called
     */

    public function setSearchUrl($url)
    {
        if(preg_match('^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&\'\(\)\*\+,;=.]+$^', $url) == 0)
            throw new Exception('setSearchUrl:Parameter should be a valid URL');
        $this->search_url = $url;
    }

    public function setSearchPageUrl($url)
    {
        if(preg_match('^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&\'\(\)\*\+,;=.]+$^', $url) == 0)
            throw new Exception('setSearchPageUrl: Parameter should be a valid URL');
        $this->page_search_url = $url;
    }

    public function setTrackSelector($selector)
    {
        $this->track_selector = $selector;
    }

    public function setDurationSelector($selector)
    {
        $this->duration_selector = $selector;
    }

    public function setArtistSelector($selector)
    {
        $this->artist_selector = $selector;
    }

    public function setTitleSelector($selector)
    {
        $this->title_selector = $selector;
    }

    public function setThumbnailSelector($selector)
    {
        $this->thumbnail_selector = $selector;
    }

    public function setClientKey($key){
        $this->client_key = $key;
    }

    /*
     *  'commenceSearch' returns collection of Crawler filtered objects
     */

    public function createResponseArray($origin, $artists, $titles, $urls, $durations, $thumbnails){
        $response = array();
        for ($i = 0; array_key_exists($i, $urls); $i++) {


            $track['artist'] = null;
            $track['url'] = null;
            $track['title'] = null;
            $track['duration'] = null;
            $track['thumbnail'] = null;
            $track['origin'] = $origin;

            if ($artists != null)
                $track['artist'] = $artists[$i];

            if ($urls != null)
                $track['url'] = $urls[$i];

            if ($titles != null)
                $track['title'] = $titles[$i];

            if ($durations != null)
                $track['duration'] = $durations[$i];

            if ($thumbnails != null)
                $track['thumbnail'] = $thumbnails[$i];


            array_push($response, $track);
        }
        return $response;
    }

    public function commenceSearch($query, $page)
    {
        if ($page == 0)
            unset($page);
        // URL to get DOM
        $url = '';
        // If page No. is given
        if (isset($page))
            $url = $this->search_url;
        // If page No. is NOT given
        else
            $url = $this->page_search_url;
        // Populate URL with query,page and client_key
        $url = str_replace('SEARCH', $query, $url);
        if (isset($page))
            $url = str_replace('PAGE', $page, $url);
        if (isset($this->client_key))
            $url = str_replace('KEY', $this->client_key, $url);
        // Create new instance of http client
        $http = new Client();
        try {
            $responce = $http->request('GET', $url);
        } catch (Exception $e){
            throw new Exception('commenceSearch: Could not resolve host or connection is down');
        }
        // Get html of page
        $html = (string)$responce->getBody();
        // Parse it with symphony/dom-crawler
        $crawler = new Crawler($html);
        // Create responce collection
        if (isset($this->track_selector))
            $collection['track_urls'] = $crawler->filter($this->track_selector);
        else
            $collection['track_urls'] = null;

        if (isset($this->title_selector))
            $collection['track_titles'] = $crawler->filter($this->title_selector);
        else
            $collection['track_titles'] = null;

        if (isset($this->artist_selector))
            $collection['track_artists'] = $crawler->filter($this->artist_selector);
        else
            $collection['track_artists'] = null;

        if (isset($this->duration_selector))
            $collection['track_duration'] = $crawler->filter($this->duration_selector);
        else
            $collection['track_duration'] = null;

        if (isset($this->thumbnail_selector))
            $collection['track_thumbnail'] = $crawler->filter($this->thumbnail_selector);
        else
            $collection['track_thumbnail'] = null;

        return $collection;

    }

    public function timeStringToSeconds($string, $type = 'mm:ss'){
        $minutes = 0;
        $seconds = 0;
        $str_time = $string;
        $time_seconds = 0;
        if ($type == 'mm:ss') {
            $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "$1:$2", $str_time);
            sscanf($str_time, "%d:%d", $minutes, $seconds);
            $time_seconds = $minutes * 60 + $seconds;
        } elseif ($type == 'hh:mm:ss') {
            $hours = 0;
            $str_time = preg_replace("/^([\d]{1,2})\:([\d]{1,2})\:([\d]{2})$/", "$1:$2:$3", $str_time);
            sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
            $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
        } else
            throw new Exception('Unknown time format');

        return $time_seconds;
    }


}
