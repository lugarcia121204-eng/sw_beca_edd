<script setup>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
  chartData: {
    type: Object,
    required: true
  },
  chartOptions: {
    type: Object,
    default: () => ({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
        },
      },
      scales: {
        y: {
            beginAtZero: true
        }
      }
    })
  }
})
</script>

<template>
  <div class="w-full h-64 md:h-[400px]">
    <Bar
      :id="'solicitudes-chart'"
      :options="chartOptions"
      :data="chartData"
    />
  </div>
</template>
