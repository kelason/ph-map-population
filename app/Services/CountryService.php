<?php

namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CountryService
{
    protected $PhHostName = 'https://raw.githubusercontent.com/faeldon/philippines-json-maps/refs/heads/master/2023/topojson/country/lowres/country.topo.0.001.json';
    
    public function getCountryTopoJSON():object
    {
        $cacheKey = 'country_data';
        
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            try {
                $response = Http::get($this->PhHostName);
                return $response->successful() ? $response->object() : null;
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return null;
            }
        });
    }
}