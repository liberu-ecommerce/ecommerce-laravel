<?php

namespace App\Http\Controllers;

use App\Services\GdprExportService;
use Illuminate\Http\Request;

class AccountDataExportController extends Controller
{
    public function __invoke(Request $request, GdprExportService $service)
    {
        $data = $service->export($request->user());

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="my-data.json"');
    }
}
