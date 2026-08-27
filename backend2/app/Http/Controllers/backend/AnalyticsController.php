<?php

namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;

use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function activeUsersBySource()
    {
        $data = $this->analyticsService->getActiveUsersBySource();
        return response()->json($data);
    }
}
