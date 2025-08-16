<?php

namespace App\Services;
use Illuminate\Support\Facades\Cache;
ini_set('memory_limit', '2048M');

class PopulationService
{
    protected $populationDir = __DIR__ . '/../../public/population/population.csv';
    
    /**
     * Get population data from CSV file
     * 
     * @return array
     * @throws \Exception If file cannot be opened or read
     */
    public function getPopulationCSV(): array
    {
        $cacheKey = 'populations_data';
        
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            // Check if file exists first
            if (!file_exists($this->populationDir)) {
                throw new \Exception("Population file not found at: " . $this->populationDir);
            }

            $file = fopen($this->populationDir, 'r');
            if ($file === false) {
                throw new \Exception("Could not open the population file.");
            }

            $populationData = [];
            $headers = null;
            
            while (($row = fgetcsv($file)) !== false) {
                // Skip empty rows and brgy
                if (empty($row) || (count($row) === 1 && $row[0] === null) || strpos($row[0], 'Bgy') !== false) {
                    continue;
                }
                
                // First non-empty row is headers
                if ($headers === null) {
                    $headers = $row;
                    continue;
                }
                
                // Combine headers with row data
                $combined = array_combine($headers, $row);
                
                // Skip records with geographic_level = "Bgy" or missing population data
                // Also skip if population data for 2015 or 2020 is missing
                // This ensures we only keep valid province or city data
                if (
                    (isset($combined['geographic_level']) && $combined['geographic_level'] === 'Bgy') ||
                    empty($combined['population_2015']) ||
                    empty($combined['population_2020'])
                ) {
                    continue;
                }
                
                $populationData[] = $combined;
            }
            
            fclose($file);
            
            return $populationData;
        });
    }
}