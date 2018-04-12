<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Redis;

class ApiController extends Controller
{
    public function getList(Request $request)
    {

        $request->validate([
            'search' => 'required',
            'page' => 'integer'
        ]);
        $page = 0;
        $search = urlencode($request->input('search'));
        //mp3cc.com parsing
        $url = 'http://mp3cc.com/search/f/' . $search;
        if ($request->exists('page')) {
            $url .= '/page/' . $request->input('page');
            $page = $request->input('page');
        }

        $search_hash = md5($search . $page);

        $key = Redis::get($search_hash);
        if ($key != null){
            $json = Redis::get($search_hash);
            $json = json_decode($json, true);
            return response()->json($json, 201);
        } else {
            try {
                $html = file_get_contents($url);
            } catch (\Exception $e) {
                $responce['message'] = $e->getMessage();
            }

            $crawler = new Crawler($html);

            //forming URL-SONG_NAME-ARTISTS list
            $mp3cc_songnames = array();
            $mp3cc_urls = $crawler->filter('li[data-mp3]')->extract(array('data-mp3'));
            foreach ($crawler->filter('li[data-mp3] > h2.playlist-name > b > a') as $element) {
                array_push($mp3cc_songnames, $element->nodeValue);
            };

            $responce = array();

            for ($i = 0; array_key_exists($i, $mp3cc_urls); $i++) {
                $track['origin'] = 'mp3cc';
                $track['url'] = $mp3cc_urls[$i];
                $track['title'] = $mp3cc_songnames[$i];
                array_push($responce, $track);
            }

            //SoundCloud api
            $token = 'client_id=qPtWURX3JrkpXGy7vWetJDsiZVcOdpXy';
            $host_name = 'http://localhost:8000/api/soundcloud/';
            $limit = (string)50;
            if ($request->exists('page'))
                $offset = (string)($request->exists('page') * $limit);
            else
                $offset = (string)0;

            $url = 'https://api-v2.soundcloud.com/search?q= ' . $search . '&limit=' . $limit . '&offset=' . $offset . '&' . $token;
            try {
                $json = file_get_contents($url);
            } catch (\Exception $e) {
                $responce['message'] = $e->getMessage();
                return response()->json($responce, 404);
            }
            $json = json_decode($json);

            foreach ($json->collection as $track) {
                if ($track->kind != 'track')
                    continue;
                $tmp_track['origin'] = 'soundcloud';
                $tmp_track['url'] = $host_name . $track->id;
                $tmp_track['title'] = $track->title;
                array_push($responce, $tmp_track);
            }

            Redis::set($search_hash, json_encode($responce));
            Redis::expire($search_hash, 300);

            return response()->json($responce, 200);
        }
    }

    public function soundcloud($id) {
        $token = '?client_id=qPtWURX3JrkpXGy7vWetJDsiZVcOdpXy';
        return redirect()->away('https://api.soundcloud.com/tracks/' . $id . '/stream' . $token);
    }
}
