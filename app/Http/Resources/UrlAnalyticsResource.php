<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UrlAnalyticsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'alias' => $this->resource['alias'],
            'total_redirects' => $this->resource['total_redirects'],
            'redirects_by_day' => $this->resource['redirects_by_day'],
            'geo_distribution' => $this->resource['geo_distribution'],
            'user_agents' => $this->resource['user_agents'],
        ];
    }
}
