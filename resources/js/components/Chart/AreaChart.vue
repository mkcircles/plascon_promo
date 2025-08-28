<template>
  <div v-if="!display">
      <h4>Loading Please Wait .........</h4>
    </div>
    <div v-else id="chart">

      <apexchart v-if="dates.length > 0"
        type="line"
        height="500"
        width="100%"
        :options="chartOptions"
        :series="series"
      />
    </div>
  </template>
  
  <script setup>
  import axios from "axios";
  import {ref, onBeforeMount} from "vue";

  const display = ref(false);

  const dates = ref([]);
  const count = ref([]);
  const jinja = ref([]);
  const arua = ref([]);
  const fort = ref([]);
  const gulu = ref([]);
  const kampala = ref([]);
  const lira = ref([]);
  const masaka = ref([]);
  const mbarara = ref([]);

  const series = ref([
    { name: 'Entries',type: 'column',data: count }, 
    { name: 'Jinja',type: 'line',data: jinja }, 
    { name: 'Arua',type: 'line', data: arua },
    { name: 'Fort Portal',type: 'line',data: fort }, 
    { name: 'Gulu',type: 'line', data: gulu },
    { name: 'Kampala',type: 'line',data: kampala }, 
    { name: 'Lira',type: 'line', data: lira },
    { name: 'Masaka',type: 'line',data: masaka }, 
    { name: 'Mbarara',type: 'line', data: mbarara },
  ]);
          
  const chartOptions = ref({
            chart: {
              height: 350,
              type: 'line',
            },
            stroke: {
              width: [0, 4]
            },
            title: {
              text: 'Traffic Sources'
            },
            dataLabels: {
              enabled: true,
              enabledOnSeries: []
            },
            labels: dates,
            xaxis: {
              type: 'datetime'
            },
            yaxis: [{
              title: {
                text: 'Daily Traffic',
              },
            
            }]
          });

    onBeforeMount(()=> {
      fetchData();
    });

        const fetchData = () => {
          axios.get("/api/chart/area").then((res) => {

            dates.value = res.data.dates;
            count.value = res.data.counts;
            jinja.value = res.data.jinja;
            arua.value = res.data.arua;
            fort.value = res.data.fort;
            gulu.value = res.data.gulu;
            kampala.value = res.data.kampala;
            lira.value = res.data.lira;
            masaka.value = res.data.masaka;
            mbarara.value = res.data.mbarara;

          });
          display.value = true;
         
        }
  </script>
