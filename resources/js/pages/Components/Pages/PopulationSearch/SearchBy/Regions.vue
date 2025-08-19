<template>
    <div style="margin-top: 15px; overflow-y: auto; flex-grow: 1">
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px">
                <strong style="color: #2c3e50; font-size: 16px">Top Regions by Population</strong>
                <ul style="list-style-type: none; padding-left: 5px; margin-top: 8px">
                    <li
                        v-for="(region, index) in topRegions"
                        :key="index"
                        style="padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between"
                    >
                        <span style="font-weight: 500">{{ index + 1 }}. {{ region.name }}</span>
                        <span style="color: #e74c3c; font-weight: bold">{{ formatNumber(region.population) }}</span>
                    </li>
                </ul>
            </div>
        </div>
</template>

<script>
export default {
    name: 'Regions',
    props: {
        formatNumber: {
            type: Function,
            required: true,
        },
        topRegions: {
            type: Array,
            required: true,
            default: '',
        }
    },
    methods: {
    },
};
</script>
