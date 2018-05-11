<?php

namespace App\ApiSource\soundcloud;

use App\ApiSource\Api\Api;

class soundcloud extends Api
{

    function search($query, $page = 0)
    {
        $host_name = '127.0.0.1:8000/api/soundcloud/';
        $this->setApiSearchLink('https://api-v2.soundcloud.com/search/tracks?q=SEARCH&client_id=CLIENT_KEY&limit=50');
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

        for ($i = 0; array_key_exists($i, $search->collection); $i++) {
            $urls[$i] = $host_name . $search->collection[$i]->id;
            $titles[$i] = $search->collection[$i]->title;
            $durations[$i] = (int)round(($search->collection[$i]->duration) / 1000) + 0;
            if (isset($search->collection[$i]->publisher_metadata->artist))
                $artists[$i] = $search->collection[$i]->publisher_metadata->artist;
            else
                $artists[$i] = null;
            $thumbnails[$i] = $search->collection[$i]->artwork_url;
        }

        $response = $this->createResponseArray('soundcloud', $artists, $titles, $urls, $durations, $thumbnails);

        return $response;

    }
}

?>