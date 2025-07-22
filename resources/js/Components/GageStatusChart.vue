<script setup>
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

const props = defineProps({
    data: {
        type: Object,
        required: true
    }
})

const chartData = {
  labels: ['✅ On Schedule', '⚠️ Due Soon', '🔴 Overdue'],
  datasets: [
    {
      backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
      borderColor: ['#059669', '#d97706', '#dc2626'],
      borderWidth: 2,
      data: [
        props.data.on_schedule || 0,
        props.data.due_soon || 0,
        props.data.overdue || 0
      ]
    }
  ]
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          const total = context.dataset.data.reduce((a, b) => a + b, 0);
          const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
          return `${context.label}: ${context.parsed} (${percentage}%)`;
        }
      }
    }
  }
}
</script>

<template>
    <div class="h-64">
        <Doughnut
            :data="chartData"
            :options="chartOptions"
        />
    </div>
</template>