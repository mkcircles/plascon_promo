import "./bootstrap";

import { createApp } from "vue";
import App from "./components/App.vue";
import { createPinia } from "pinia";
import VueApexCharts from "vue3-apexcharts";


import router from "./routes/";

const app = createApp(App);
const pinia = createPinia();


app.use(router);
app.use(pinia);
app.use(VueApexCharts);

app.mount("#app");
