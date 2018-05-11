<?php

namespace App\ApiSource\Parse;

use Mockery\Exception;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

class Parser
{
    protected $search_url;
    protected $page_search_url;
    protected $track_selector;
    protected $artist_selector;
    protected $title_selector;
    protected $duration_selector;
    protected $thumbnail_selector;
    protected $client_key;

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
        if (!isset($page))
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
//            dd($this);
            $response = $http->request('GET', $url);
        } catch (Exception $e){
            throw new Exception('commenceSearch: Could not resolve host or connection is down');
        }

        // Get html of page
        $html = (string)$response->getBody();
        // Parse it with symphony/dom-crawler
        $crawler = new Crawler($html);
        // Create response collection
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

    static public function parseAll($query, $page = 0){
        //getting all names of parsing classes from congif
        $parsClasses = config('parser.parsers');
        //making array of resolvers
        $resolvers = array();
        //making defers array
        $defers = array();

        //filling array with defers
        for ($i = 0; $i < count($parsClasses); $i++)
            $defers[$i] = new \React\Promise\Deferred();
        //filling array of resolvers with promises
        for ($i = 0; $i < count($parsClasses); $i++)
            $resolvers[$i] = $defers[$i]->promise();

        //making promises for each of objects
        for ($i = 0; $i < count($parsClasses); $i++)
            $defers[$i]->resolve((app()->make($parsClasses[$i]))->search($query, $page));

        $promise = \React\Promise\all($resolvers)->then(function($resolved){
            $response = array();
            for ($i = 0; $i < count($resolved); $i++)
                $response = array_merge($response, $resolved[$i]);
            return $response;
        });

        return $promise;
    }


}
