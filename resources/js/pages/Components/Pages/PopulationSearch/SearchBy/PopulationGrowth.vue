<template>
    <div style="margin-top: 15px; overflow-y: auto; flex-grow: 1">
        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px">
            <strong style="color: #2c3e50; font-size: 16px">Top 10 Population Growth from 2015 to 2020</strong>
            <ul style="list-style-type: none; padding-left: 5px; margin-top: 8px">
                <li
                    v-for="(city, index) in top10CitiesGrowth"
                    :key="index"
                    style="padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between"
                >
                    <span style="font-weight: 500"
                        >{{ index + 1 }}. {{ city.properties.adm3_en }}, {{ formatPopulationProvince(city.properties.adm2_psgc) }}</span
                    >
                    <span style="color: #e74c3c; font-weight: bold">{{ formatNumber(city.properties.population_growth) }}</span>
                </li>
            </ul>
        </div>

        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px">
            <strong style="color: #2c3e50; font-size: 16px">Bottom 10 Population Growth from 2015 to 2020</strong>
            <ul style="list-style-type: none; padding-left: 5px; margin-top: 8px">
                <li
                    v-for="(city, index) in bottom10CitiesGrowth"
                    :key="index"
                    style="padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between"
                >
                    <span style="font-weight: 500"
                        >{{ index + 1 }}. {{ city.properties.adm3_en }}, {{ formatPopulationProvince(city.properties.adm2_psgc) }}</span
                    >
                    <span style="color: #3498db; font-weight: bold">{{ formatNumber(city.properties.population_growth) }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PopulationGrowth',
    props: {
        formatPopulationProvince: {
            type: Function,
            required: true,
        },
        formatNumber: {
            type: Function,
            required: true,
        },
        top10CitiesGrowth: {
            type: Array,
            required: true,
            default: '',
        },
        bottom10CitiesGrowth: {
            type: Array,
            required: true,
            default: '',
        },
    },
    methods: {
    },
};
</script>
