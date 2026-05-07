<?php

namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProvinceService
{
    protected $phHostName = 'https://raw.githubusercontent.com/faeldon/philippines-json-maps/refs/heads/master/2023/topojson/provdists/lowres/';
    protected $muniCities = [
        'municities-provdist-1001300000.topo.0.001.json',
        'municities-provdist-1001800000.topo.0.001.json',
        'municities-provdist-1003500000.topo.0.001.json',
        'municities-provdist-1004200000.topo.0.001.json',
        'municities-provdist-1004300000.topo.0.001.json',
        'municities-provdist-102800000.topo.0.001.json',
        'municities-provdist-102900000.topo.0.001.json',
        'municities-provdist-103300000.topo.0.001.json',
        'municities-provdist-105500000.topo.0.001.json',
        'municities-provdist-1102300000.topo.0.001.json',
        'municities-provdist-1102400000.topo.0.001.json',
        'municities-provdist-1102500000.topo.0.001.json',
        'municities-provdist-1108200000.topo.0.001.json',
        'municities-provdist-1108600000.topo.0.001.json',
        'municities-provdist-1204700000.topo.0.001.json',
        'municities-provdist-1206300000.topo.0.001.json',
        'municities-provdist-1206500000.topo.0.001.json',
        'municities-provdist-1208000000.topo.0.001.json',
        'municities-provdist-1303900000.topo.0.001.json',
        'municities-provdist-1307400000.topo.0.001.json',
        'municities-provdist-1307500000.topo.0.001.json',
        'municities-provdist-1307600000.topo.0.001.json',
        'municities-provdist-1400100000.topo.0.001.json',
        'municities-provdist-1401100000.topo.0.001.json',
        'municities-provdist-1402700000.topo.0.001.json',
        'municities-provdist-1403200000.topo.0.001.json',
        'municities-provdist-1404400000.topo.0.001.json',
        'municities-provdist-1408100000.topo.0.001.json',
        'municities-provdist-1600200000.topo.0.001.json',
        'municities-provdist-1600300000.topo.0.001.json',
        'municities-provdist-1606700000.topo.0.001.json',
        'municities-provdist-1606800000.topo.0.001.json',
        'municities-provdist-1608500000.topo.0.001.json',
        'municities-provdist-1704000000.topo.0.001.json',
        'municities-provdist-1705100000.topo.0.001.json',
        'municities-provdist-1705200000.topo.0.001.json',
        'municities-provdist-1705300000.topo.0.001.json',
        'municities-provdist-1705900000.topo.0.001.json',
        'municities-provdist-1900700000.topo.0.001.json',
        'municities-provdist-1903600000.topo.0.001.json',
        'municities-provdist-1906600000.topo.0.001.json',
        'municities-provdist-1907000000.topo.0.001.json',
        'municities-provdist-1908700000.topo.0.001.json',
        'municities-provdist-1908800000.topo.0.001.json',
        'municities-provdist-1909900000.topo.0.001.json',
        'municities-provdist-200900000.topo.0.001.json',
        'municities-provdist-201500000.topo.0.001.json',
        'municities-provdist-203100000.topo.0.001.json',
        'municities-provdist-205000000.topo.0.001.json',
        'municities-provdist-205700000.topo.0.001.json',
        'municities-provdist-300800000.topo.0.001.json',
        'municities-provdist-301400000.topo.0.001.json',
        'municities-provdist-304900000.topo.0.001.json',
        'municities-provdist-305400000.topo.0.001.json',
        'municities-provdist-306900000.topo.0.001.json',
        'municities-provdist-307100000.topo.0.001.json',
        'municities-provdist-307700000.topo.0.001.json',
        'municities-provdist-401000000.topo.0.001.json',
        'municities-provdist-402100000.topo.0.001.json',
        'municities-provdist-403400000.topo.0.001.json',
        'municities-provdist-405600000.topo.0.001.json',
        'municities-provdist-405800000.topo.0.001.json',
        'municities-provdist-500500000.topo.0.001.json',
        'municities-provdist-501600000.topo.0.001.json',
        'municities-provdist-501700000.topo.0.001.json',
        'municities-provdist-502000000.topo.0.001.json',
        'municities-provdist-504100000.topo.0.001.json',
        'municities-provdist-506200000.topo.0.001.json',
        'municities-provdist-600400000.topo.0.001.json',
        'municities-provdist-600600000.topo.0.001.json',
        'municities-provdist-601900000.topo.0.001.json',
        'municities-provdist-603000000.topo.0.001.json',
        'municities-provdist-604500000.topo.0.001.json',
        'municities-provdist-607900000.topo.0.001.json',
        'municities-provdist-701200000.topo.0.001.json',
        'municities-provdist-702200000.topo.0.001.json',
        'municities-provdist-704600000.topo.0.001.json',
        'municities-provdist-706100000.topo.0.001.json',
        'municities-provdist-802600000.topo.0.001.json',
        'municities-provdist-803700000.topo.0.001.json',
        'municities-provdist-804800000.topo.0.001.json',
        'municities-provdist-806000000.topo.0.001.json',
        'municities-provdist-806400000.topo.0.001.json',
        'municities-provdist-807800000.topo.0.001.json',
        'municities-provdist-907200000.topo.0.001.json',
        'municities-provdist-907300000.topo.0.001.json',
        'municities-provdist-908300000.topo.0.001.json',
        'municities-provdist-990100000.topo.0.001.json'
    ];
    
    public function getProvincesTopoJSON()
    {
        $provinces = $this->getProvinces();
        return $provinces;
    }

    public function getProvinces(): array
    {
        $cacheKey = 'provinces_data_' . md5(implode(',', $this->muniCities));
        
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            // Use HTTP Pool to fetch all files concurrently
            $responses = Http::pool(fn ($pool) => 
                collect($this->muniCities)->map(fn ($file) => 
                    $pool->as($file)->get($this->phHostName . $file)
                )
            );

            return collect($responses)
                ->filter(fn ($response) => $response->successful())
                ->map(fn ($response) => $response->json())
                ->values()
                ->all();
        });
    }
}