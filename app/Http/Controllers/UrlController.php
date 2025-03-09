<?php

namespace App\Http\Controllers;

use App\Services\UrlService;
use Illuminate\Http\Request;
use App\Http\Requests\ShortenUrlRequest;
use App\Http\Resources\UrlAnalyticsResource;

class UrlController extends Controller
{
    protected $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    public function store(ShortenUrlRequest $request)
    {
        $url = $this->urlService->shortenUrl($request->original_url, $request->alias);
        $shortUrl = url("/{$url->alias}");
        return response()->json([
            'short_url' => $shortUrl,
            'original_url' => $url->original_url,
        ], 201);
    }

    public function redirect($alias, Request $request)
    {
        $this->urlService->logRedirect($alias, $request);
        $originalUrl = $this->urlService->getOriginalUrl($alias);
        return redirect($originalUrl, 301);
    }
    public function analytics($alias)
    {
        $analytics = $this->urlService->getAnalytics($alias);
        return new UrlAnalyticsResource($analytics);
    }
}
