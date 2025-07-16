<template>
  <div class="dashboard">
    <h1>Tableau de bord</h1>
    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total kilomètres</h3>
        <p class="stat-value">{{ totalKm }} km</p>
      </div>
      <div class="stat-card">
        <h3>Moyenne quotidienne</h3>
        <p class="stat-value">{{ averageDaily }} km</p>
      </div>
      <div class="stat-card">
        <h3>Position classement général</h3>
        <p class="stat-value">#{{ ranking }}</p>
      </div>
      <div class="stat-card">
        <h3>Position classement équipe</h3>
        <p class="stat-value">#{{ teamRanking }}</p>
      </div>
      <div class="stat-card">
        <h3>Kilomètres en vélo</h3>
        <p class="stat-value">{{ veloKm }} km</p>
      </div>
      <div class="stat-card">
        <h3>Kilomètres en marche/course</h3>
        <p class="stat-value">{{ marcheKm }} km</p>
      </div>
    </div>

    <div class="chart-section">
      <h2>Évolution sur les 30 derniers jours</h2>
      <canvas id="evolutionChart" height="80"></canvas>
    </div>

    <div class="actions">
      <button @click="$router.push('/declare')" class="btn-primary">
        Déclarer une activité
      </button>
      <button @click="$router.push('/stats')" class="btn-secondary">
        Voir les classements
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '../api';
import Chart from 'chart.js/auto';

const totalKm = ref(0);
const averageDaily = ref(0);
const ranking = ref('-');
const teamRanking = ref('-');
const veloKm = ref(0);
const marcheKm = ref(0);
const last30Days = ref([]);
let chartInstance = null;

const loadDashboardData = async () => {
  try {
    const response = await api.get('/stats/personal');
    if (response.data.status === 'success') {
      const data = response.data.data;
      totalKm.value = data.total_distance_km || 0;
      averageDaily.value = data.daily_average_km || 0;
      ranking.value = data.ranking?.general || '-';
      teamRanking.value = data.ranking?.team || '-';
      veloKm.value = data.velo_stats?.total_distance || 0;
      marcheKm.value = data.marche_stats?.total_distance || 0;
      last30Days.value = data.last_30_days || [];
      renderChart();
    }
  } catch (error) {
    console.error('Erreur lors du chargement du dashboard:', error);
  }
};

const renderChart = () => {
  const ctx = document.getElementById('evolutionChart');
  if (!ctx) return;
  if (chartInstance) {
    chartInstance.destroy();
  }
  const labels = last30Days.value.map(item => item.date);
  const data = last30Days.value.map(item => item.distance_km);
  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Distance parcourue (km)',
          data,
          fill: false,
          borderColor: '#2563eb',
          backgroundColor: '#2563eb',
          tension: 0.2,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
        },
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Date',
          },
        },
        y: {
          title: {
            display: true,
            text: 'Km',
          },
          beginAtZero: true,
        },
      },
    },
  });
};

onMounted(() => {
  loadDashboardData();
});

watch(last30Days, () => {
  renderChart();
});
</script>

<style scoped>
.dashboard {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin: 2rem 0;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  text-align: center;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #2563eb;
  margin: 0.5rem 0;
}

.chart-section {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
}

.actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-primary, .btn-secondary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.btn-primary {
  background: #2563eb;
  color: white;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-primary:hover {
  background: #1d4ed8;
}

.btn-secondary:hover {
  background: #4b5563;
}
</style>
