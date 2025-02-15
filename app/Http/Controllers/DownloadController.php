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
        $downloadableProduct = DownloadableProduct::where('product_id', $productId)
            ->firstOrFail();

        if (!$downloadableProduct->isDownloadable() || !$this->authorizeDownload($request->user(), $downloadableProduct)) {
            abort(403, 'Download limit reached or not authorized.');
        }

        $temporaryUrl = Storage::disk('local')->temporaryUrl(
            $downloadableProduct->file_url, 
            now()->addMinutes(5)
        );

        return response()->json([
            'url' => $temporaryUrl,
            'expires_in' => 300, // 5 minutes in seconds
            'downloads_remaining' => $downloadableProduct->download_limit - $downloadableProduct->downloads_count
        ]);
    }

    public function serveFile(Request $request, $productId)
    {
        $downloadableProduct = DownloadableProduct::where('product_id', $productId)
            ->firstOrFail();

        if (!$downloadableProduct->isDownloadable() || !$this->authorizeDownload($request->user(), $downloadableProduct)) {
            abort(403, 'Download limit reached or not authorized.');
        }

        $downloadableProduct->incrementDownloadCount();

        return Storage::disk('local')->download(
            $downloadableProduct->file_url,
            null,
            ['Content-Type' => Storage::disk('local')->mimeType($downloadableProduct->file_url)]
        );
    }

    private function authorizeDownload($user, DownloadableProduct $downloadableProduct)
    {
        if (!$user) {
            return false;
        }

        // Check if user has purchased the product
        return $user->orders()
            ->whereHas('items', function ($query) use ($downloadableProduct) {
                $query->where('product_id', $downloadableProduct->product_id);
            })
            ->where('status', 'completed')
            ->exists();
    }
}