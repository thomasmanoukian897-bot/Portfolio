<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'adminCount' => User::where('role', UserRole::Admin)->count(),
            'recentUsers' => User::query()->latest()->limit(5)->get(),
        ]);
    }
}
