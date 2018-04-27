<?php

namespace App\Http\Controllers;

use App\ApiSource\mp3cc\mp3cc;
use App\ApiSource\musicxn41a\musicxn41a;
use App\ApiSource\soundcloud\soundcloud;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    public function getList(Request $request)
    {

        $request->validate([
            'search' => 'required',
            'page' => 'integer'
        ]);
        if ($request->has('page'))
            $page = $request->input('search');
        else
            $page = 0;
        $query = urlencode($request->input('search'));
        $search_hash = md5($query . $page);
        $response = array();
        if (Cache::has($search_hash)){
            $response = Cache::get($search_hash);
            $response = json_decode($response, true);
            return response()->json($response, 201);
        } else {

            $search = new mp3cc();
            $response = $search->search($query);

            $search = new musicxn41a();
            $response = array_merge($response, $search->search($query));

            $search = new soundcloud();
            $response = array_merge($response, $search->search($query));

            Cache::put($search_hash, json_encode($response), 60);
            
            return response()->json($response, 200);
        }
    }

    public function soundcloud($id) {
        $token = '?client_id=qPtWURX3JrkpXGy7vWetJDsiZVcOdpXy';
        return redirect()->away('https://api.soundcloud.com/tracks/' . $id . '/download' . $token);
    }
}
