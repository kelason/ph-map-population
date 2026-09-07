<template>
    <div style="position: relative">
        <population-density
            v-model:selectedYear="selectedYear"
            :cityName="this.populationDensity.cityName"
            :count="this.populationDensity.count"
            @updateMapData="updateMapData"
        />
        <population-search
            v-model:selectedSearchPopulation="selectedSearchPopulation"
            :searchBy="searchBy"
            :formatPopulationProvince="formatPopulationProvince"
            :formatNumber="formatNumber"
            @updateMapData="updateMapData"
        />
        <div>
            <div ref="chart" style="background: linear-gradient(to bottom, #2e9afe, #00bfff, #045fb4); height: 100vh"></div>
        </div>

        <zoom v-if="this.zoomBehavior && this.svg" :zoomBehavior="this.zoomBehavior" :svg="this.svg" />
    </div>
</template>

<script>
import { defineAsyncComponent } from 'vue';
import * as d3 from 'd3';
import * as topojson from 'topojson';
import { muniCitiesTopoJsonName } from '../../../utils/constants.js';

export default {
    name: 'PhMap',
    components: {
        Zoom: defineAsyncComponent(() => import('./Pages/Zoom.vue')),
        PopulationDensity: defineAsyncComponent(() => import('./Pages/PopulationDensity.vue')),
        PopulationSearch: defineAsyncComponent(() => import('./Pages/PopulationSearch/PopulationSearch.vue')),
    },
    props: {
        countryTopoJson: {
            type: Object,
            required: true,
        },
        provincesTopoJson: {
            type: Array,
            required: true,
        },
        populations: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            selectedYear: '2015',
            selectedSearchPopulation: 'Cities',
            svg: null,
            g: null,
            path: null,
            colorScale: null,
            allMuniFeatures: [],
            allProvFeatures: [],
            populationMap: {},
            provincePaths: null,
            zoomBehavior: null,
            projection: null,
            initialScale: null,
            width: 0,
            height: 0,
            labelsInitialized: false,
            searchBy: {
                Cities: {
                    top10Cities: [],
                    bottom10Cities: [],
                },
                Provinces: {
                    top10Provinces: [],
                    bottom10Provinces: [],
                },
                Regions: {
                    topRegions: [],
                },
                PopulationGrowth: {
                    top10CitiesGrowth: [],
                    bottom10CitiesGrowth: [],
                },
            },
            populationDensity: [
                {
                    cityName: null,
                    count: null,
                },
            ],
        };
    },
    created() {
        this.populationMapping();
    },
    mounted() {
        this.initializeMap();
        // Lazy load calculations to keep the UI responsive during initial render
        setTimeout(() => {
            this.getSearchbyData();
        }, 500);
    },
    methods: {
        initializeMap() {
            const container = this.$refs.chart;
            this.width = container.clientWidth;
            this.height = container.clientHeight;

            // Clear any existing SVG
            d3.select(container).selectAll('*').remove();

            // Create SVG
            this.svg = d3
                .select(container)
                .append('svg')
                .attr('width', '100%')
                .attr('height', '100%')
                .attr('viewBox', `0 0 ${this.width} ${this.height}`)
                .style('display', 'block');

            // Create and store zoom behavior
            this.zoomBehavior = d3
                .zoom()
                .scaleExtent([1, 10]) // Limit zoom scale
                .on('zoom', (event) => {
                    this.g.attr('transform', event.transform);
                    this.updateProvinceLabels(event.transform);
                });

            // Apply zoom behavior to the SVG
            this.svg.call(this.zoomBehavior);

            // Disable double-click zoom
            this.svg.on('dblclick.zoom', null);

            // Create a group that will be transformed by zoom
            this.g = this.svg.append('g');

            // Calculate initial scale based on container size
            this.initialScale = 3000;

            // Create projection and path
            this.projection = d3
                .geoMercator()
                .translate([this.width / 2, this.height / 2])
                .scale(this.initialScale)
                .center([122.42715, 12.499176]);

            this.path = d3.geoPath().projection(this.projection);

            // Draw country outline
            const phCountry = topojson.feature(this.countryTopoJson, this.countryTopoJson.objects['PH_Adm1_Regions.shp']).features;
            this.g.selectAll('.ph').data(phCountry).enter().append('path').attr('class', 'ph').attr('d', this.path);

            this.processData();
            // Process and draw provinces
            this.drawProvinces();
        },

        populationMapping() {
            // Create population map
            this.populations.forEach((population) => {
                this.populationMap[population.psgc] = population;
            });
        },
    
        processData() {
            this.processPopulationData();
            this.processProvinceData();
        },

        processPopulationData() {
            // Clear the array before processing new data
            this.allMuniFeatures = [];

            this.provincesTopoJson.forEach((provinceData, index) => {
                const topoJsonFile = provinceData;
                const topoJsonObjectName = muniCitiesTopoJsonName[index];

                if (topoJsonFile.objects[topoJsonObjectName]) {
                    const features = topojson.feature(topoJsonFile, topoJsonFile.objects[topoJsonObjectName]).features;

                    features.forEach((feature) => {
                        feature.properties.population = this.populationMap[feature.id][`population_${this.selectedYear}`];
                        feature.properties.population_growth = this.populationMap[feature.id].population_2020 - this.populationMap[feature.id].population_2015;
                        feature.properties.geographic_level = this.populationMap[feature.id].geographic_level;
                    });

                    this.allMuniFeatures.push(...features);
                }
            });
        },

        processProvinceData() {
            this.allProvFeatures = [];
            this.populations.forEach((provinceData) => {
                provinceData.population = provinceData[`population_${this.selectedYear}`];
                this.allProvFeatures.push(provinceData);
            });
        },

        // Generic function to get top/bottom N items by population
        getTopNByPopulation(data, n = 10, getPopulation, top = true) {
            // Filter out items without population data
            const filteredData = data.filter(item => getPopulation(item) != null);
            
            // Create a copy to avoid mutating original array
            const result = [...filteredData];
            
            // Use quickselect to partially sort the array
            const quickselectIndex = top ? n : n - 1;
            const comparator = top 
                ? (a, b) => getPopulation(b) - getPopulation(a)
                : (a, b) => getPopulation(a) - getPopulation(b);
            
            d3.quickselect(result, quickselectIndex, 0, result.length - 1, comparator);
            
            result.length = Math.min(n, result.length);
            
            result.sort(top 
                ? (a, b) => getPopulation(b) - getPopulation(a)
                : (a, b) => getPopulation(a) - getPopulation(b)
            );
            
            return result;
        },

        getTopPopulationDataByCities() {
            // For top 10 cities by population
            this.searchBy.Cities.top10Cities = this.getTopNByPopulation(
                this.allMuniFeatures,
                10,
                f => f.properties.population,
                true
            );

            // For bottom 10 cities by population
            this.searchBy.Cities.bottom10Cities = this.getTopNByPopulation(
                this.allMuniFeatures,
                10,
                f => f.properties.population,
                false
            );
        },

        getTopPopulationDataByProvinces() {
            const provinceData = this.allProvFeatures.filter(f => f.geographic_level === 'Prov');
            
            // For top 10 provinces by population
            this.searchBy.Provinces.top10Provinces = this.getTopNByPopulation(
                provinceData,
                10,
                f => f.population,
                true
            );

            // For bottom 10 provinces by population
            this.searchBy.Provinces.bottom10Provinces = this.getTopNByPopulation(
                provinceData,
                10,
                f => f.population,
                false
            );
        },

        getTopPopulationDataByRegions() {
            const regionData = this.allProvFeatures.filter(f => f.geographic_level === 'Reg');
            
            // For top regions by population
            this.searchBy.Regions.topRegions = this.getTopNByPopulation(
                regionData,
                17,
                f => f.population,
                true
            );
        },

        getTopPopulationDataByCitiesGrowth() {
            // For top regions by population
            this.searchBy.PopulationGrowth.top10CitiesGrowth = this.getTopNByPopulation(
                this.allMuniFeatures,
                10,
                f => f.properties.population_growth,
                true
            );

            this.searchBy.PopulationGrowth.bottom10CitiesGrowth = this.getTopNByPopulation(
                this.allMuniFeatures,
                10,
                f => f.properties.population_growth,
                false
            );
        },

        getSearchbyData() {
                this.getTopPopulationDataByCities();
                this.getTopPopulationDataByProvinces();
                this.getTopPopulationDataByRegions();
                this.getTopPopulationDataByCitiesGrowth();
        },

        drawProvinces() {
            const va = this;

            // Calculate domain based on current year
            const populationDomain = d3.extent(this.allMuniFeatures, (d) => +this.populationMap[d.id]?.[`population_${this.selectedYear}`] || 0);
            // Get the first and third quartiles
            const q1 = d3.quantile(populationDomain, 0.01);
            const q2 = d3.quantile(populationDomain, 0.03);
            const q3 = d3.quantile(populationDomain, 0.08);
            const q4 = d3.quantile(populationDomain, 0.2);

            // Create or update color scale
            this.colorScale = d3
                .scaleThreshold() // Handles zero/negative values gracefully
                .domain([q1, q2, q3, q4])
                .range(d3.schemeReds[4]);

            // Bind data and create paths if they don't exist
            if (!this.provincePaths) {
                this.provincePaths = this.g
                    .selectAll('.provinces')
                    .data(this.allMuniFeatures)
                    .enter()
                    .append('path')
                    .attr('class', 'provinces')
                    .attr('d', this.path)
                    .on('mouseover', function (d) {
                        d3.select(this).classed('selected', true);
                        va.updateTooltip(d);
                    })
                    .on('mouseout', function (d) {
                        d3.select(this).classed('selected', false);
                    });
            }

            // Update colors based on selected year
            this.updateMapColors();
        },

        updateMapColors() {
            const va = this;
            this.provincePaths.attr('fill', (d) => {
                const pop = +this.populationMap[d.id]?.[`population_${va.selectedYear}`] || 0;
                return pop ? this.colorScale(pop) : '#ccc';
            });
        },

        // Initialize labels with responsive setup
        initProvinceLabels() {
            if (this.labelsInitialized) return;

            this.g.selectAll('.province-labels')
                .data(this.allMuniFeatures)
                .enter()
                .append('text')
                .attr('class', 'province-labels')
                .attr('transform', d => {
                    const centroid = this.path.centroid(d);
                    
                    // Check if centroid is valid
                    if (isNaN(centroid[0]) || isNaN(centroid[1])) {
                        return 'translate(0,0)'; // Fallback
                    }
                    
                    return `translate(${centroid})`;
                })
                .attr('text-anchor', 'middle')
                .attr('dy', '0.35em')
                .text(d => d.properties.adm3_en)
                .style('font-size', `1px`)
                .style('fill', 'black')
                .style('pointer-events', 'none')
                .style('font-weight', 'bold')
                .style('paint-order', 'stroke') // Makes text more readable on complex backgrounds
                .style('stroke', 'white')
                .style('stroke-width', '0.5px')
                .style('stroke-linecap', 'round')
                .style('stroke-linejoin', 'round')
                .style('display', 'none');

            this.labelsInitialized = true;
        },

        updateProvinceLabels(transform) {
            const zoomLevel = transform.k;
            const minZoomLevel = 2; // Adjust this threshold as needed
            
            if (zoomLevel >= minZoomLevel && !this.labelsInitialized) {
                this.initProvinceLabels();
            }

            this.g.selectAll('.province-labels')
                .style('display', zoomLevel >= minZoomLevel ? 'block' : 'none')
                .style('font-size', () => {
                    // Scale font size based on zoom level
                    const baseSize = 1;
                    return `${baseSize * Math.min(zoomLevel, 1)}px`; // Cap at 3px max
                });
        },

        updateTooltip(d) {
            let targetData = d.target.__data__;
            const psgc = targetData.id;
            const pop2015 = this.populationMap[psgc]?.population_2015 || 0;
            const pop2020 = this.populationMap[psgc]?.population_2020 || 0;
            const difference = pop2020 - pop2015;
            
            // Generate comparison icon HTML
            let comparisonIcon = '';
            if (this.selectedYear === '2020') {
                if (difference > 0) {
                comparisonIcon = '<span style="color:#4caf50; font-weight:bold;">↗</span>';
                } else if (difference < 0) {
                comparisonIcon = '<span style="color:#f44336; font-weight:bold;">↘</span>';
                } else {
                comparisonIcon = '<span style="color:#ff9800; font-weight:bold;">＝</span>';
                }
            }
            this.populationDensity.cityName = `<strong>Municipal/City</strong>: ${targetData.properties.adm3_en}, ${this.formatPopulationProvince(targetData.properties.adm2_psgc)}`;
            this.populationDensity.count = `<strong>Population Count (${this.selectedYear})</strong>: ${this.formatNumber(targetData.properties.population)} ${comparisonIcon}`;
        },

        updateMapData() {
            this.processData();
            this.drawProvinces();
            this.getSearchbyData();
        },

        formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        formatPopulationProvince(psgc) {
            //1300000000
            const psgcNCR = '1300000000';
            return this.populationMap[psgc]?.name !== undefined ? this.populationMap[psgc].name : this.populationMap[psgcNCR].name;
        },
    },
};
</script>
<style>
html,
body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    overflow: hidden; /* Prevent page scrolling */
}
.ph {
    fill: grey;
    stroke: #333;
    stroke-width: 2;
}
.provinces {
    stroke: #333;
    stroke-width: 0.5;
}
.selected {
    fill: black;
}
.legend {
    font-family: Arial, sans-serif;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    width: 100%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.legend-title {
    font-weight: bold;
    margin-bottom: 8px;
    text-align: center;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border: 1px solid #999;
    margin-right: 8px;
}

.legend-label {
    font-size: 12px;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: white;
}
/* Zoom controls styling */
.zoom-controls-container {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 1000;
}

.zoom-controls {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.zoom-controls button {
    width: 70px;
    height: 30px;
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.zoom-controls button:hover {
    background-color: #f5f5f5;
}

.province-labels {
    font-size: 10px;
    font-weight: bold;
    paint-order: stroke;
    stroke: white;
    stroke-width: 2px;
    stroke-linecap: round;
    stroke-linejoin: round;
}

@media (max-width: 768px) {
    .province-labels {
        font-size: 8px;
        stroke-width: 1.5px;
    }
}

@media (max-width: 480px) {
    .province-labels {
        font-size: 7px;
        display: none; /* Or show on hover only */
    }
}
</style>
