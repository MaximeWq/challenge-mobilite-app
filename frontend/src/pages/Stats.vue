<template>
  <div class="stats-page">
    <h1>Statistiques et Classements</h1>
    
    <div class="stats-tabs">
      <button 
        @click="activeTab = 'general'" 
        :class="{ active: activeTab === 'general' }"
        class="tab-button"
      >
        Statistiques générales
      </button>
      <button 
        @click="activeTab = 'users'" 
        :class="{ active: activeTab === 'users' }"
        class="tab-button"
      >
        Classement individuel
      </button>
      <button 
        @click="activeTab = 'teams'" 
        :class="{ active: activeTab === 'teams' }"
        class="tab-button"
      >
        Classement par équipe
      </button>
    </div>

    <!-- Statistiques générales -->
    <div v-if="activeTab === 'general'" class="tab-content">
      <div v-if="loading" class="loading">Chargement...</div>
      <div v-else-if="generalStats" class="general-stats">
        <div class="stats-grid">
          <div class="stat-card">
            <h3>Total participants</h3>
            <p class="stat-value">{{ generalStats.total_users }}</p>
          </div>
          <div class="stat-card">
            <h3>Total kilomètres</h3>
            <p class="stat-value">{{ generalStats.total_distance_km }} km</p>
          </div>
          <div class="stat-card">
            <h3>Moyenne par participant</h3>
            <p class="stat-value">{{ generalStats.total_users > 0 ? (generalStats.total_distance_km / generalStats.total_users).toFixed(2) : 0 }} km</p>
          </div>
          <div class="stat-card">
            <h3>Total activités</h3>
            <p class="stat-value">{{ generalStats.total_activities }}</p>
          </div>
        </div>
        
        <div class="export-section">
          <button @click="exportData" class="btn-export">
            Exporter les données (CSV)
          </button>
        </div>
      </div>
    </div>

    <!-- Classement individuel -->
    <div v-if="activeTab === 'users'" class="tab-content">
      <div v-if="loading" class="loading">Chargement...</div>
      <div v-else-if="usersRanking" class="users-ranking">
        <h2>Top 10 - Classement individuel</h2>
        <div class="ranking-list">
          <div 
            v-for="(user, index) in usersRanking" 
            :key="user.id"
            class="ranking-item"
          >
            <div class="rank">{{ index + 1 }}</div>
            <div class="user-info">
              <div class="user-name">{{ user.nom }}</div>
              <div class="user-team">{{ user.email }}</div>
            </div>
            <div class="user-stats">
              <div class="total-km">{{ user.total_distance }} km</div>
              <div class="activities-count">{{ user.total_activities }} activités</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Classement par équipe -->
    <div v-if="activeTab === 'teams'" class="tab-content">
      <div v-if="loading" class="loading">Chargement...</div>
      <div v-else-if="teamsRanking" class="teams-ranking">
        <h2>Classement par équipe</h2>
        <div class="ranking-list">
          <div 
            v-for="(team, index) in teamsRanking" 
            :key="team.id"
            class="ranking-item"
          >
            <div class="rank">{{ index + 1 }}</div>
            <div class="team-info">
              <div class="team-name">{{ team.name }}</div>
              <div class="team-description">{{ team.description }}</div>
            </div>
            <div class="team-stats">
              <div class="total-km">{{ team.total_distance }} km</div>
              <div class="average-km">{{ team.average_km }} km/membre</div>
              <div class="members-count">{{ team.members_count }} membres</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '../api';

const activeTab = ref('general');
const loading = ref(false);
const generalStats = ref(null);
const usersRanking = ref(null);
const teamsRanking = ref(null);

const loadGeneralStats = async () => {
  try {
    const response = await api.get('/stats/general');
    if (response.data.status === 'success') {
      generalStats.value = response.data.data;
    }
  } catch (error) {
    console.error('Erreur lors du chargement des stats générales:', error);
  }
};

const loadUsersRanking = async () => {
  try {
    const response = await api.get('/stats/users');
    if (response.data.status === 'success') {
      usersRanking.value = response.data.data;
    }
  } catch (error) {
    console.error('Erreur lors du chargement du classement utilisateurs:', error);
  }
};

const loadTeamsRanking = async () => {
  try {
    const response = await api.get('/stats/teams');
    if (response.data.status === 'success') {
      teamsRanking.value = response.data.data;
    }
  } catch (error) {
    console.error('Erreur lors du chargement du classement équipes:', error);
  }
};

const loadTabData = async () => {
  loading.value = true;
  
  switch (activeTab.value) {
    case 'general':
      await loadGeneralStats();
      break;
    case 'users':
      await loadUsersRanking();
      break;
    case 'teams':
      await loadTeamsRanking();
      break;
  }
  
  loading.value = false;
};

const exportData = async () => {
  try {
    const response = await api.get('/stats/export', {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'challenge-mobilite-export.csv');
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Erreur lors de l\'export:', error);
  }
};

onMounted(() => {
  loadTabData();
});

// Recharger les données quand on change d'onglet
watch(activeTab, () => {
  loadTabData();
});
</script>

<style scoped>
.stats-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.stats-tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  border-bottom: 2px solid #e5e7eb;
}

.tab-button {
  padding: 0.75rem 1.5rem;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: 500;
  color: #6b7280;
  border-bottom: 2px solid transparent;
}

.tab-button.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
}

.tab-content {
  min-height: 400px;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
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

.export-section {
  text-align: center;
  margin-top: 2rem;
}

.btn-export {
  background: #059669;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.ranking-list {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

.ranking-item {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.ranking-item:last-child {
  border-bottom: none;
}

.rank {
  font-size: 1.5rem;
  font-weight: bold;
  color: #2563eb;
  min-width: 3rem;
}

.user-info, .team-info {
  flex: 1;
  margin-left: 1rem;
}

.user-name, .team-name {
  font-weight: 600;
  font-size: 1.1rem;
}

.user-team, .team-description {
  color: #6b7280;
  font-size: 0.9rem;
}

.user-stats, .team-stats {
  text-align: right;
}

.total-km {
  font-weight: 600;
  color: #2563eb;
  font-size: 1.1rem;
}

.activities-count, .average-km, .members-count {
  color: #6b7280;
  font-size: 0.9rem;
}
</style>
