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
            <strong>Search Philippine Population by:</strong>
            <br /><br />
            <div class="form-group">
                <label for="year-select">Search Philippine Population by:</label>
                <select id="year-select" :value="selectedSearchPopulation" @change="updateSearch">
                    <option value="Cities">Cities</option>
                    <option value="Provinces">Provinces</option>
                    <option value="Region">Region</option>
                    <option value="Rising">Rising</option>
                </select>
            </div>
        </div>

        <div v-if="selectedSearchPopulation === 'Cities'" style="margin-top: 15px; overflow-y: auto; flex-grow: 1">
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px">
                <strong style="color: #2c3e50; font-size: 16px">Top 10 Cities by Population</strong>
                <ul style="list-style-type: none; padding-left: 5px; margin-top: 8px">
                    <li
                        v-for="(city, index) in top10Cities"
                        :key="index"
                        style="padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between"
                    >
                        <span style="font-weight: 500"
                            >{{ index + 1 }}. {{ city.properties.adm3_en }}, {{ formatPopulationProvince(city.properties.adm2_psgc) }}</span
                        >
                        <span style="color: #e74c3c; font-weight: bold">{{ formatNumber(city.properties.population) }}</span>
                    </li>
                </ul>
            </div>

            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px">
                <strong style="color: #2c3e50; font-size: 16px">Bottom 10 Cities by Population</strong>
                <ul style="list-style-type: none; padding-left: 5px; margin-top: 8px">
                    <li
                        v-for="(city, index) in bottom10Cities"
                        :key="index"
                        style="padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between"
                    >
                        <span style="font-weight: 500"
                            >{{ index + 1 }}. {{ city.properties.adm3_en }}, {{ formatPopulationProvince(city.properties.adm2_psgc) }}</span
                        >
                        <span style="color: #3498db; font-weight: bold">{{ formatNumber(city.properties.population) }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div v-if="selectedSearchPopulation === 'Provinces'" style="margin-top: 15px; overflow-y: auto; flex-grow: 1">
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px">
                <strong style="color: #2c3e50; font-size: 16px">Top 10 Cities by Population</strong>
                <ul style="list-style-type: none; padding-left: 5px; margin-top: 8px">
                    <li
                        v-for="(city, index) in top10Cities"
                        :key="index"
                        style="padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between"
                    >
                        <span style="font-weight: 500">{{ index + 1 }}. {{ city.properties.adm3_en }}</span>
                        <span style="color: #e74c3c; font-weight: bold">{{ formatNumber(city.properties.population) }}</span>
                    </li>
                </ul>
            </div>

            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px">
                <strong style="color: #2c3e50; font-size: 16px">Bottom 10 Cities by Population</strong>
                <ul style="list-style-type: none; padding-left: 5px; margin-top: 8px">
                    <li
                        v-for="(city, index) in bottom10Cities"
                        :key="index"
                        style="padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between"
                    >
                        <span style="font-weight: 500">{{ index + 1 }}. {{ city.properties.adm3_en }}</span>
                        <span style="color: #3498db; font-weight: bold">{{ formatNumber(city.properties.population) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'PopulationSearch',
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
        top10Cities: {
            type: Array,
            required: true,
            default: '',
        },
        bottom10Cities: {
            type: Array,
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
