<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;

class SquadsController extends Controller
{
    public function index(): View
    {
        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->withHeaders([
                'x-apisports-key' => config('services.api_sports.key'),
            ])
            ->get('https://v3.football.api-sports.io/players/squads', [
                'team' => config('services.api_sports.team_id'),
            ]);

        return view('squads', [
            'data' => $response->successful() ? $response->json() : null,
            'error' => $response->successful() ? null : $response->body(),
            'status' => $response->status(),
        ]);
    }
}
