<?php

namespace App\ApiSource\Api;

use Mockery\Exception;
use GuzzleHttp\Client;

class Api {



    protected $api_search_link;
    protected $page_parameter;
    protected $client_key;
    protected $api_key;

    /*
     * Format is :example.com/api/search?q=SEARCH&clientId=CLIENT_UD&apiKey=API_KEY
     */

    public function setApiSearchLink($link){
        $this->api_search_link = $link;
    }

    /*
     * Format is: &page=PAGE
     */

    public function setPageParameter($string){
        $this->page_parameter = $string;
    }

    public function setClientKey($key){
        $this->client_key = $key;
    }

    public function setApiKey($key){
        $this->api_key = $key;
    }

    function commenceSearch($query, $page = 0){
        $url = $this->api_search_link;
        $url = str_replace('SEARCH', $query, $url);
        $url = str_replace('API_KEY', $this->api_key, $url);
        $url = str_replace('CLIENT_KEY', $this->client_key, $url);
        if ($page != 0) {
            $url .= $this->page_parameter;
            $url = str_replace('PAGE', $page, $url);
        }
        $http = new Client();
        try {
            $responce = $http->request('GET', $url);
        } catch (Exception $e){
            throw new Exception('commenceSearch: Could not resolve host or connection is down');
        }
        $json = (string)$responce->getBody();

        return json_decode($json);
    }

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

    static public function parseAll($query, $page = 0){
        //getting all names of parsing classes from congif
        $parsClasses = config('api.apis');
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

        $qwe = 0;
        $promise = \React\Promise\all($resolvers)->then(function($resolved) use ($qwe){
            $response = array();
            for ($i = 0; $i < count($resolved); $i++)
                $response = array_merge($response, $resolved[$i]);
            return $response;
        });

        return $promise;
    }

}

?>