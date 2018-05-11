<?php

namespace App\Http\Controllers;

use App\ApiSource\Api\Api;
use App\ApiSource\Parse\Parser;

use Illuminate\Support\Facades\Cache;

use App\Http\Requests\ApiListSearchRequest;

class ApiController extends Controller
{
    public function getList(ApiListSearchRequest $request)
    {
        if(!$response = $request->checkCache()) {
            $promises = array();
            $promises[] = Parser::parseAll($request->search, $request->page);
            $promises[] = Api::parseAll($request->search, $request->page);
            $response = array_merge($promises[0]->value, $promises[1]->value);
            Cache::put($request->searchHash, json_encode($response), 60);
            return response()->json($response, 200);
        } else {
            return response()->json($response, 200);
        }
    }

    public function soundcloud($id) {
        $token = '?client_id=qPtWURX3JrkpXGy7vWetJDsiZVcOdpXy';
        return redirect()->away('https://api.soundcloud.com/tracks/' . $id . '/download' . $token);
    }
}
