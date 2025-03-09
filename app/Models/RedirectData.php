<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class RedirectData extends Model
{
    use HasFactory;

    protected $fillable = ['short_url_id', 'clicked_at', 'ip_address', 'geo_location', 'user_agent'];

    public function shortUrl()
    {
        return $this->belongsTo(ShortUrl::class);
    }
}
