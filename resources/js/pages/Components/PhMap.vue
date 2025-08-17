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
            :top10Cities="top10Cities"
            :bottom10Cities="bottom10Cities"
            :formatPopulationProvince="formatPopulationProvince"
            :formatNumber="formatNumber"
        />
        <div>
            <div ref="chart" style="background: linear-gradient(to bottom, #2e9afe, #00bfff, #045fb4); height: 100vh"></div>
        </div>

        <zoom v-if="this.zoomBehavior && this.svg" :zoomBehavior="this.zoomBehavior" :svg="this.svg" />
    </div>
</template>

<script>
import * as d3 from 'd3';
import * as topojson from 'topojson';
import { muniCitiesTopoJsonName } from '../../../utils/constants.js';
import PopulationDensity from './Pages/PopulationDensity.vue';
import PopulationSearch from './Pages/PopulationSearch.vue';
import Zoom from './Pages/Zoom.vue';

export default {
    name: 'PhMap',
    components: {
        Zoom,
        PopulationDensity,
        PopulationSearch,
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
            populationMap: {},
            provincePaths: null,
            zoomBehavior: null,
            projection: null,
            initialScale: null,
            width: 0,
            height: 0,
            top10Cities: [],
            bottom10Cities: [],
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
        this.getTopPopulationDataByCities();
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
                .scaleExtent([1, 8]) // Limit zoom scale
                .on('zoom', (event) => {
                    this.g.attr('transform', event.transform);
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

            // Process and draw provinces
            this.processProvinceData();
            this.drawProvinces();
        },

        populationMapping() {
            // Create population map
            this.populations.forEach((population) => {
                this.populationMap[population.psgc] = population;
            });
        },

        processProvinceData() {
            this.provincesTopoJson.forEach((provinceData, index) => {
                const topoJsonFile = provinceData;
                const topoJsonObjectName = muniCitiesTopoJsonName[index];

                if (topoJsonFile.objects[topoJsonObjectName]) {
                    const features = topojson.feature(topoJsonFile, topoJsonFile.objects[topoJsonObjectName]).features;

                    features.forEach((feature) => {
                        feature.properties.population = this.populationMap[feature.id][`population_${this.selectedYear}`];
                    });

                    this.allMuniFeatures.push(...features);
                }
            });
        },

        getTopPopulationDataByCities() {
            // For top 10 populations
            this.top10Cities = [...this.allMuniFeatures].filter((f) => f.properties.population != null);
            d3.quickselect(this.top10Cities, 10, 0, this.top10Cities.length - 1, (a, b) => b.properties.population - a.properties.population);
            this.top10Cities.length = 10;
            this.top10Cities.sort((a, b) => b.properties.population - a.properties.population);

            // For bottom 10 populations
            this.bottom10Cities = [...this.allMuniFeatures].filter((f) => f.properties.population != null);
            d3.quickselect(this.bottom10Cities, 9, 0, this.bottom10Cities.length - 1, (a, b) => a.properties.population - b.properties.population);
            this.bottom10Cities.length = 10;
            this.bottom10Cities.sort((a, b) => a.properties.population - b.properties.population);
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

        updateTooltip(d) {
            let targetData = d.target.__data__;
            this.populationDensity.cityName = `<strong>Municipal/City</strong>: ${targetData.properties.adm3_en}, ${this.formatPopulationProvince(targetData.properties.adm2_psgc)}`;
            this.populationDensity.count = `<strong>Population Count (${this.selectedYear})</strong>: ${this.formatNumber(targetData.properties.population)}`;
        },

        updateMapData() {
            this.processProvinceData();
            this.drawProvinces();
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
</style>
