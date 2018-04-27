<?php

namespace App\ApiSource\soundcloud;

use App\ApiSource\Api\Api;
use Composer\Cache;
use GuzzleHttp\Client;

class soundcloud extends Api
{

    function search($query, $page = 0)
    {
        $host_name = '127.0.0.1:8000/api/soundcloud/';
        $this->setApiSearchLink('https://api-v2.soundcloud.com/search?q=SEARCH&client_id=CLIENT_KEY&limit=50');
        $this->setPageParameter('?offset=PAGE');
        $this->setClientKey('qPtWURX3JrkpXGy7vWetJDsiZVcOdpXy');
        if ($page != 0)
            $page *= 50;
        $search = $this->commenceSearch($query, $page);

        $urls = array();
        $artists = array();
        $durations = array();
        $titles = array();
        $thumbnails = array();
        $obj = $this;

        for ($i = 0; array_key_exists($i, $search->collection); $i++) {
            if ($search->collection[$i]->kind != 'track')
                continue;
            $urls[$i] = $host_name . $search->collection[$i]->id;
            $titles[$i] = $search->collection[$i]->title;
            $durations[$i] = (int)round(($search->collection[$i]->duration) / 1000) + 0;
            if (isset($search->collection[$i]->publisher_metadata->artist))
                $artists[$i] = $search->collection[$i]->publisher_metadata->artist;
            else
                $artists[$i] = null;
            $thumbnails[$i] = $this->getThumbnail($search, $i, $this);
        }

        $response = $this->createResponseArray('soundcloud', $artists, $titles, $urls, $durations, $thumbnails);

        return $response;

    }

    function getThumbnail($search, $i, $obj){
        $http = new Client();
        $response = $http->request('GET', 'https://api.soundcloud.com/tracks/' . $search->collection[$i]->id . '?client_id=' . $obj->client_key);
        $json = (string)$response->getBody();
        $json = json_decode($json, true);
        return $json['artwork_url'];
    }

    function flush(){
        Cache::flush();
    }

}

?>