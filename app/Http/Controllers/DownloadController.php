<?php

namespace App\Http\Controllers;

use App\Models\DownloadableProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function generateSecureLink(Request $request, $productId)
    {
        $downloadableProduct = DownloadableProduct::where('product_id', $productId)->firstOrFail();
        if (!$this->checkDownloadLimits($downloadableProduct) || !$this->authorizeDownload($request->user(), $downloadableProduct)) {
            abort(403, 'Download limit reached or not authorized.');
        }
        $temporaryUrl = Storage::disk('local')->temporaryUrl(
            $downloadableProduct->file_url, now()->addMinutes(5)
        );
        return response()->json(['url' => $temporaryUrl]);
    }

    private function checkDownloadLimits(DownloadableProduct $downloadableProduct)
    {
        return $downloadableProduct->download_limit > $downloadableProduct->downloads_count && (!$downloadableProduct->expiration_time || $downloadableProduct->expiration_time->isFuture());
    }

    public function serveFile(Request $request, $productId)
    {
        $downloadableProduct = DownloadableProduct::where('product_id', $productId)->firstOrFail();
        if (!$this->checkDownloadLimits($downloadableProduct) || !$this->authorizeDownload($request->user(), $downloadableProduct)) {
            abort(403, 'Download limit reached or not authorized.');
        }
        $downloadableProduct->increment('downloads_count');
        return Storage::disk('local')->download($downloadableProduct->file_url);
    }

    private function authorizeDownload($user, DownloadableProduct $downloadableProduct)
    {
        // Implement logic to verify user's purchase
        return true; // Placeholder for purchase verification logic
    }
}
