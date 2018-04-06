<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class TestController extends Controller
{
    public function index(Request $request) {
        $html = file_get_contents('http://www.example.com/');
        $html = str_replace("\n", '', $html);

        $crawler = new Crawler($html);

//        dd($crawler);

        foreach ($crawler as $domElement) {
            var_dump($domElement->nodeValue);
        }

//        dd($crawler->html());

        die();


    }
}
