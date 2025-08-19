<template>
    <div style="margin-top: 15px; overflow-y: auto; flex-grow: 1">
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
</template>

<script>
export default {
    name: 'City',
    props: {
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
    },
};
</script>
