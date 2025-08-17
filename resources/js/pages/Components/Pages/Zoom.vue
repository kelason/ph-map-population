<template>
    <!-- Zoom controls container (new) -->
    <div class="zoom-controls-container">
        <div class="zoom-controls">
            <button @click="zoomIn">+</button>
            <button @click="zoomOut">-</button>
            <button @click="resetZoom">Reset</button>
        </div>
    </div>
</template>

<script>
import * as d3 from 'd3';

export default {
    name: 'PhMap',
    props: {
        zoomBehavior: {
            type: Function,
            required: true,
        },
        svg: {
            type: Object,
            required: true,
        },
    },
    methods: {
        // Zoom methods
        zoomIn() {
            this.svg.transition().duration(250).call(this.zoomBehavior.scaleBy, 1.5);
        },

        zoomOut() {
            this.svg.transition().duration(250).call(this.zoomBehavior.scaleBy, 0.75);
        },

        resetZoom() {
            this.svg.transition().duration(250).call(this.zoomBehavior.transform, d3.zoomIdentity);
        },
    },
};
</script>

<style>
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
