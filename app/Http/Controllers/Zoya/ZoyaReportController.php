<?php

namespace App\Http\Controllers\Zoya;

use App\Http\Controllers\Controller;
use App\Services\ZoyaService;

class ZoyaReportController extends Controller
{
    protected $zoya;

    public function __construct(ZoyaService $zoya)
    {
        $this->zoya = $zoya;
    }

    // Get all compliant stocks
    public function getAllReports()
    {
        $data = $this->zoya->getAllReports();  // ZoyaService call
        return response()->json($data);
    }

    // Get single stock report
    public function getSingleReport($symbol)
    {
        $data = $this->zoya->getSingleReport($symbol);
        return response()->json($data);
    }
}
