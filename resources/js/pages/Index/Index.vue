<template>
  <div id="app">
    <PhMap :countryTopoJson="countryTopoJson" :provincesTopoJson="provincesTopoJson" :populations="populations" />
    <br>
  </div>
</template>
<script setup>
import PhMap from '../Components/PhMap.vue';
defineProps({
  countryTopoJson: Object,
  provincesTopoJson: Object,
  populations: Array
})
</script>