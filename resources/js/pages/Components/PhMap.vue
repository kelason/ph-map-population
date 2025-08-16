<template>
<div style="position: relative;">
  <div style="position: fixed; top: 20px; right: 20px; width: 300px; background: white; z-index: 1000; padding: 10px 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 8px;">
    <h1>Philippines Population Map</h1>
    <br>
    <div class="form-group">
      <label for="year-select">Select Year:</label>
      <select id="year-select" v-model="selectedYear" @change="updateMapData">
        <option value="2015">2015</option>
        <option value="2020">2020</option>
      </select>
    </div>
    <br>
    <div class="legend">
      <div class="legend-title">Population Density</div>
      <div class="legend-item">
        <div class="legend-color" style="background-color: grey;"></div>
        <span class="legend-label">No Data</span>
      </div>
      <div class="legend-item">
        <div class="legend-color" style="background-color: #fee5d9;"></div>
        <span class="legend-label">Low</span>
      </div>
      <div class="legend-item">
        <div class="legend-color" style="background-color: #fcae91;"></div>
        <span class="legend-label">Medium</span>
      </div>
      <div class="legend-item">
        <div class="legend-color" style="background-color: #fb6a4a;"></div>
        <span class="legend-label">High</span>
      </div>
      <div class="legend-item">
        <div class="legend-color" style="background-color: #cb181d;"></div>
        <span class="legend-label">Very High</span>
      </div>
    </div>
    <br>
    <div class="legend">
      <div ref="desc"></div>
      <div ref="population"></div>
    </div>
  </div>
  
  <div> 
    <div>
      <div ref="chart" style="background: linear-gradient(to bottom, #2E9AFE, #00BFFF, #045FB4); height: 100vh;"></div>
    </div>
  </div>

   <!-- Zoom controls container (new) -->
  <div class="zoom-controls-container">
    <div class="zoom-controls">
      <button @click="zoomIn">+</button>
      <button @click="zoomOut">-</button>
      <button @click="resetZoom">Reset</button>
    </div>
  </div>
</div>
</template>

<script>
import * as d3 from 'd3';
import * as topojson from 'topojson';
import { muniCitiesTopoJsonName } from '../../../utils/constants.js'

export default {
  name: 'PhMap',
  props: {
    country: {
      type: Object,
      required: true
    },
    provinces: {
      type: Array,
      required: true
    },
    populations: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      selectedYear: '2015',
      svg: null,
      g: null, 
      path: null,
      colorScale: null,
      allMuniFeatures: [],
      populationMap: {},
      provincePaths: null,
      zoom: null,
      projection: null,
      initialScale: null, 
      width: 0,
      height: 0,
      countryLocal: [],
      provincesLocal: [],
      populationsLocal: []
    }
  },
  mounted() {
    this.initializeMap();
  },
  methods: {

    initializeMap() {
      const container = this.$refs.chart;
      this.width = container.clientWidth;
      this.height = container.clientHeight;
      
      // Clear any existing SVG
      d3.select(container).selectAll("*").remove();
      
      // Create SVG
      this.svg = d3.select(container)
        .append('svg')
        .attr('width', '100%')
        .attr('height', '100%')
        .attr('viewBox', `0 0 ${this.width} ${this.height}`)
        .style('display', 'block');

      // Create and store zoom behavior
      this.zoomBehavior = d3.zoom()
        .scaleExtent([1, 8]) // Limit zoom scale
        .on('zoom', (event) => {
          this.g.attr('transform', event.transform);
        });

      // Apply zoom behavior to the SVG
      this.svg.call(this.zoomBehavior);

      // Disable double-click zoom
      this.svg.on("dblclick.zoom", null);

      // Create a group that will be transformed by zoom
      this.g = this.svg.append('g');

      // Calculate initial scale based on container size
      this.initialScale = 3000;

      // Create projection and path
      this.projection = d3.geoMercator()
        .translate([this.width / 2, this.height / 2])
        .scale(this.initialScale)
        .center([122.427150, 12.499176]);

      this.path = d3.geoPath().projection(this.projection);

      // Draw country outline
      const phCountry = topojson.feature(this.country, this.country.objects['PH_Adm1_Regions.shp']).features;
      this.g.selectAll('.ph')
        .data(phCountry)
        .enter().append('path')
        .attr('class', 'ph')
        .attr('d', this.path);

      // Create population map
      this.populationMap = {};
      this.populations.forEach(population => {
        this.populationMap[population.psgc] = population;
      });

      // Process and draw provinces
      this.processProvinceData();
      this.drawProvinces();
    },
    
    processProvinceData() {
      const newProvince = this.provinces.map((item, i) => Object.assign({}, item, this.populations[i]));
      this.allMuniFeatures = [];

      newProvince.forEach((provinceData, index) => {
        const topoJsonFile = provinceData;
        const topoJsonObjectName = muniCitiesTopoJsonName[index];
        
        if (topoJsonFile.objects[topoJsonObjectName]) {
          const features = topojson.feature(topoJsonFile, topoJsonFile.objects[topoJsonObjectName]).features;
          
          features.forEach(feature => {
            feature.properties.population = provinceData[`population_${this.selectedYear}`];
          });
          
          this.allMuniFeatures.push(...features);
        }
      });
    },
    
    drawProvinces() {
      const va = this;
      
      // Calculate domain based on current year
      const populationDomain = d3.extent(this.allMuniFeatures, d => 
        +this.populationMap[d.id]?.[`population_${this.selectedYear}`] || 0
      );
      // Get the first and third quartiles
      const q1 = d3.quantile(populationDomain, 0.01);
      const q2 = d3.quantile(populationDomain, 0.03);
      const q3 = d3.quantile(populationDomain, 0.08);
      const q4 = d3.quantile(populationDomain, 0.20);
      
      console.log('Population Domain:', [q1, q1, q3, q4]);
      // Create or update color scale
      this.colorScale = d3.scaleThreshold() // Handles zero/negative values gracefully
        .domain([q1, q2, q3, q4])
        .range(d3.schemeReds[4]);
      
      // Bind data and create paths if they don't exist
      if (!this.provincePaths) {
        this.provincePaths = this.g.selectAll('.provinces')
          .data(this.allMuniFeatures)
          .enter().append('path')
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
      this.provincePaths
        .attr('fill', d => {
          const pop = +this.populationMap[d.id]?.[`population_${va.selectedYear}`] || 0;
          return pop ? this.colorScale(pop) : '#ccc';
        });
    },
    
    updateTooltip(d) {
      let targetData = d.target.__data__;
      this.$refs.desc.innerHTML = `<strong>Municipal/City</strong>: ${targetData.properties.adm3_en}`;
      this.$refs.population.innerHTML = `<strong>Population Count (${this.selectedYear})</strong>: ${this.populationMap[targetData.id]?.[`population_${this.selectedYear}`] || 'N/A'}`;
    },
    
    updateMapData() {
      this.processProvinceData();
      this.drawProvinces();
    },

    // Zoom methods
    zoomIn() {
      this.svg.transition()
        .duration(250)
        .call(this.zoomBehavior.scaleBy, 1.5);
    },
    
    zoomOut() {
      this.svg.transition()
        .duration(250)
        .call(this.zoomBehavior.scaleBy, 0.75);
    },
    
    resetZoom() {
      this.svg.transition()
        .duration(250)
        .call(this.zoomBehavior.transform, d3.zoomIdentity);
    },
  }
};
</script>
<style>
  html, body {
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
    stroke-width: 1;
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
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.zoom-controls button:hover {
  background-color: #f5f5f5;
}
</style>