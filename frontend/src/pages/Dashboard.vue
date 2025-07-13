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
        <h3>Position classement</h3>
        <p class="stat-value">#{{ ranking }}</p>
      </div>
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
import { ref, onMounted } from 'vue';
import api from '../api';

const totalKm = ref(0);
const averageDaily = ref(0);
const ranking = ref('-');

const loadDashboardData = async () => {
  try {
    // Appel à l'API pour récupérer les stats personnelles
    const response = await api.get('/stats/personal');
    if (response.data.status === 'success') {
      const data = response.data.data;
      totalKm.value = data.total_distance_km || 0;
      averageDaily.value = data.daily_average_km || 0;
      ranking.value = data.ranking?.general || '-';
    }
  } catch (error) {
    console.error('Erreur lors du chargement du dashboard:', error);
  }
};

onMounted(() => {
  loadDashboardData();
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
