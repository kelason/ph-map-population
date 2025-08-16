<template>
  <div id="app">
    <BarChart :country="country" :provinces="provinces" :populations="populations" />
    <br>
  </div>
</template>
<script setup>
import BarChart from '../Components/PhMap.vue';
defineProps({
  country: Object,
  provinces: Object,
  populations: Array
})
</script>