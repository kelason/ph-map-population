<?php

namespace App\Http\Controllers;
use App\Services\CountryService;
use App\Services\ProvinceService;
use App\Services\PopulationService;
use Illuminate\Http\Request;

class PhilippinesMapController extends Controller
{
    protected $countryService;
    protected $provinceService;
    protected $populationService;

    public function __construct(
        CountryService $countryService,
        ProvinceService $provinceService,
        PopulationService $populationService
    ) {
        $this->countryService = $countryService;
        $this->provinceService = $provinceService;
        $this->populationService = $populationService;
    }

    public function index()
    {
        $country = $this->countryService->getCountryTopoJSON();
        $provinces = $this->provinceService->getProvincesTopoJSON();
        $populations = $this->populationService->getPopulationCSV();
        return inertia(
            'Index/Index',
            [
                'country' => $country,
                'provinces' => $provinces,
                'populations' => $populations
            ]
        );
    }
}
