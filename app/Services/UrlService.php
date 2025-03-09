<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Str;
use App\Models\RedirectData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class UrlService
{
    protected const CACHE_TTL = 86400;
    public function shortenUrl(string $originalUrl, ?string $customAlias = null): ShortUrl
    {
        Log::info("Shortening URL", ['original_url' => $originalUrl, 'custom_alias' => $customAlias]);
        $alias = $customAlias ?? Str::random(6);
        while (ShortUrl::where('alias', $alias)->exists()) {
            Log::warning("Alias already exists, generating new one", ['alias' => $alias]);
            $alias = Str::random(6);
        }
        $shortUrl = ShortUrl::create([
            'original_url' => $originalUrl,
            'alias' => $alias
        ]);
        Log::info("Before storing in Redis", ['key' => "short_url:{$alias}", 'value' => $shortUrl->original_url]);
        Redis::set("short_url:{$alias}", $shortUrl->original_url);
        Redis::expire("short_url:{$alias}", self::CACHE_TTL);
        Log::info("After storing in Redis", ['key' => "short_url:{$alias}"]);
        return $shortUrl;
    }

    public function getOriginalUrl(string $alias): string
    {
        $cacheKey = "short_url:{$alias}";
        $url = Redis::get($cacheKey);
        if ($url) {
            Log::info("Cache hit", ['key' => $cacheKey]);
            return $url;
        }
        Log::info("Cache miss", ['key' => $cacheKey]);
        $shortUrl = ShortUrl::where('alias', $alias)->firstOrFail();
        Redis::set($cacheKey, $shortUrl->original_url);
        Redis::expire($cacheKey, self::CACHE_TTL);
        return $shortUrl->original_url;
    }

    public function logRedirect(string $alias, Request $request): void
    {
        $shortUrl = ShortUrl::where('alias', $alias)->firstOrFail();
        $ip = "102.186.237.148";
        $data = [
            'short_url_id' => $shortUrl->id,
            'clicked_at' => now(),
            'ip_address' => $ip,
            'geo_location' => $this->getGeoLocation($ip),
            'user_agent' => $request->userAgent(),
        ];
        RedirectData::create($data);
        Log::info("Redirect logged", ['alias' => $alias, 'data' => $data]);

        $cacheKey = "analytics:{$alias}";
        Redis::del($cacheKey);
        Log::info("Analytics cache invalidated", ['key' => $cacheKey]);
    }

    private function getGeoLocation(string $ip): ?string
    {
        try {
            $response = file_get_contents("http://ip-api.com/json/{$ip}");
            $geo = json_decode($response, true);
            return json_encode([
                'country' => $geo['country'] ?? 'Unknown',
                'city' => $geo['city'] ?? 'Unknown',
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to get geo location", ['ip' => $ip, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function getAnalytics(string $alias): array
    {
        $cacheKey = "analytics:{$alias}";

        $cachedData = Redis::get($cacheKey);
        if ($cachedData) {
            Log::info("Analytics retrieved from cache", ['key' => $cacheKey]);
            return json_decode($cachedData, true);
        }
        $analytics = $this->computeAnalytics($alias);
        Redis::setex($cacheKey, 3600, json_encode($analytics));
        Log::info("Analytics computed and cached", ['key' => $cacheKey]);
        return $analytics;
    }

    private function computeAnalytics(string $alias): array
    {
        $shortUrl = ShortUrl::where('alias', $alias)->firstOrFail();

        $totalRedirects = RedirectData::where('short_url_id', $shortUrl->id)->count();

        $redirectsByDay = RedirectData::where('short_url_id', $shortUrl->id)
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            >pluck('count', 'date');

        $geoDistribution = RedirectData::where('short_url_id', $shortUrl->id)
            ->selectRaw('JSON_EXTRACT(geo_location, "$.country") as country, COUNT(*) as count')
            ->groupBy('country')
            ->pluck('count', 'country');

        $userAgents = RedirectData::where('short_url_id', $shortUrl->id)
            ->selectRaw('user_agent, COUNT(*) as count')
            ->groupBy('user_agent')
            ->orderBy('count')
            ->pluck('count', 'user_agent');
        return [
            'alias' => $alias,
            'total_redirects' => $totalRedirects,
            'redirects_by_day' => $redirectsByDay,
            'geo_distribution' => $geoDistribution,
            'user_agents' => $userAgents,
        ];
    }
}
