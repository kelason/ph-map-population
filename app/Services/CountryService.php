<?php

namespace App\Services;
use Illuminate\Support\Facades\Cache;

class CountryService
{
    protected $PhHostName = 'https://raw.githubusercontent.com/faeldon/philippines-json-maps/refs/heads/master/2023/topojson/country/lowres/country.topo.0.001.json';
    
    public function getCountryTopoJSON():object
    {
        $cacheKey = 'country_data';
        
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            $jsonStr = file_get_contents($this->PhHostName);
            $country = json_decode($jsonStr);
            return $country;
        });
    }
}