# Philippines Population Map Visualization

An interactive data visualization project for Philippine population density, built with **Vue 3**, **D3.js**, and **TopoJSON**. This project allows users to visualize population shifts across years (e.g., 2015 vs 2020) and search for demographic insights by City, Province, and Region.

## 🚀 Tech Stack

- **Framework:** [Vue.js 3](https://vuejs.org/)
- **Visualization:** [D3.js](https://d3js.org/) & [TopoJSON](https://github.com/topojson/topojson)
- **Language:** TypeScript / JavaScript
- **Linting & Formatting:** 
  - [ESLint](https://eslint.org/) (Flat Config)
  - [@vue/eslint-config-typescript](https://github.com/vuejs/eslint-config-typescript)
  - [Prettier](https://prettier.io/) (via `eslint-config-prettier`)

## 🛠️ Prerequisites

Ensure you have the following installed:
- Node.js (LTS version recommended)
- npm, yarn, or pnpm

## 📦 Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd ph-map-population
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

## 💻 Development

### Run the development servers

1. **Start the PHP application server:**
```bash
php artisan serve
```

2. **Start the Vite compilation server:**
```bash
npm run dev
```

### Lint and Fix files
The project uses the new ESLint Flat Config.
```bash
npm run lint
```

## 🗺️ Project Structure & Mapping Logic

The core visualization logic resides in `resources/js/pages/Components/PhMap.vue`.

- **Data Binding:** The component accepts `countryTopoJson`, `provincesTopoJson`, and `populations` as props.
- **Projection:** Uses `d3.geoMercator()` centered on coordinates `[122.42715, 12.499176]` to accurately render the Philippine archipelago.
- **Dynamic Scaling:** Population density is visualized using a `d3.scaleThreshold()` with a Red color scheme (`d3.schemeReds`).
- **Features:**
    - **Zoom/Pan:** Powered by `d3.zoom()` with custom scale extents.
    - **Search:** Categorized by Cities, Provinces, and Regions.
    - **Interactive Tooltips:** Real-time population count and growth indicators (↗/↘).

## 🔧 Configuration Notes

- **ESLint:** Configured to work with Vue + TypeScript. If you are using VS Code, ensure the ESLint extension is updated to support Flat Config (`eslint.config.mjs`).
- **Prettier:** Integrated into the linting workflow to turn off conflicting stylistic rules.

## 📄 License

MIT