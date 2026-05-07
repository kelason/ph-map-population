<template>
    <div
        style="
            position: fixed;
            top: 20px;
            right: 20px;
            width: 500px;
            height: calc(100vh - 40px);
            background: white;
            z-index: 1000;
            padding: 10px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
        "
    >
        <div style="flex-shrink: 0">
            <div class="form-group">
                <label for="year-select">Search Philippine Population by:</label>
                <select id="year-select" :value="selectedSearchPopulation" @change="updateSearch">
                    <option value="Cities">Cities</option>
                    <option value="Provinces">Provinces</option>
                    <option value="Regions">Regions</option>
                    <option value="popgro">Population Growth</option>
                </select>
            </div>
        </div>

        <cities 
            v-if="selectedSearchPopulation === 'Cities'"
            :top10Cities="searchBy.Cities.top10Cities"
            :bottom10Cities="searchBy.Cities.bottom10Cities"
            :formatPopulationProvince="formatPopulationProvince"
            :formatNumber="formatNumber"
        />

        <provinces
            v-if="selectedSearchPopulation === 'Provinces'"
            :top10Provinces="searchBy.Provinces.top10Provinces"
            :bottom10Provinces="searchBy.Provinces.bottom10Provinces"
            :formatNumber="formatNumber"
        />

        <regions
            v-if="selectedSearchPopulation === 'Regions'"
            :topRegions="searchBy.Regions.topRegions"
            :formatNumber="formatNumber"
        />

        <population-growth
            v-if="selectedSearchPopulation === 'popgro'"
            :top10CitiesGrowth="searchBy.PopulationGrowth.top10CitiesGrowth"
            :bottom10CitiesGrowth="searchBy.PopulationGrowth.bottom10CitiesGrowth"
            :formatPopulationProvince="formatPopulationProvince"
            :formatNumber="formatNumber"
        />
    </div>
</template>
<script>
import { defineAsyncComponent } from 'vue';

export default {
    name: 'PopulationSearch',
    components: {
        Cities: defineAsyncComponent(() => import('./SearchBy/Cities.vue')),
        Provinces: defineAsyncComponent(() => import('./SearchBy/Provinces.vue')),
        Regions: defineAsyncComponent(() => import('./SearchBy/Regions.vue')),
        PopulationGrowth: defineAsyncComponent(() => import('./SearchBy/PopulationGrowth.vue')),
    },
    props: {
        selectedSearchPopulation: {
            type: String,
            required: true,
        },
        formatPopulationProvince: {
            type: Function,
            required: true,
        },
        formatNumber: {
            type: Function,
            required: true,
        },
        searchBy: {
            type: Object,
            required: true,
            default: '',
        },
    },
    methods: {
        updateSearch(event) {
            this.$emit('update:selectedSearchPopulation', event.target.value);
            this.$emit('updatePopulationData');
        },
    },
};
</script>
