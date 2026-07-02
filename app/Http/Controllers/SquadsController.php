<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SquadsController extends Controller
{
    public function index()
    {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://v3.football.api-sports.io/players/squads?team=34',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'x-apisports-key: a3dc7fe78be5ee17aeaeaddc0808001f'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

}
