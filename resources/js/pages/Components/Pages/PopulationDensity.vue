<template>
    <div
        style="
            position: fixed;
            top: 20px;
            left: 20px;
            width: 300px;
            background: white;
            z-index: 1000;
            padding: 10px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        "
    >
        <strong>Philippines Population Map</strong>
        <br /><br />
        <div class="form-group">
            <label for="year-select">Select Year:</label>
            <select id="year-select" :value="selectedYear" @change="updateYear">
                <option value="2015">2015</option>
                <option value="2020">2020</option>
            </select>
        </div>
        <br />
        <div class="legend">
            <div class="legend-title">Population Density</div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: grey"></div>
                <span class="legend-label">No Data</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #fee5d9"></div>
                <span class="legend-label">Low</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #fcae91"></div>
                <span class="legend-label">Medium</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #fb6a4a"></div>
                <span class="legend-label">High</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #cb181d"></div>
                <span class="legend-label">Very High</span>
            </div>
        </div>
        <br />
        <div class="legend">
            <div v-html="this.cityName"></div>
            <div v-html="this.count"></div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'PopulationDensity',
    props: {
        selectedYear: {
            type: String,
            required: true,
        },
        cityName: {
            type: String,
            required: true,
            default: '',
        },
        count: {
            type: String,
            required: true,
            default: '',
        },
    },
    methods: {
        updateYear(event) {
            this.$emit('update:selectedYear', event.target.value);
            this.$emit('updateMapData');
        },
    },
};
</script>
