<template>
  <div class="declare-activity">
    <h1>Déclarer une activité</h1>
    <form v-else @submit.prevent="submitActivity" class="activity-form">
      <div class="form-group">
        <label for="date">Date de l'activité</label>
        <input 
          id="date"
          v-model="form.date" 
          type="date" 
          :max="today"
          required
        />
      </div>

      <div class="form-group">
        <label for="type">Type d'activité</label>
        <select id="type" v-model="form.type" required>
          <option value="">Sélectionner un type</option>
          <option value="velo">Vélo</option>
          <option value="marche_course">Marche/Course</option>
        </select>
      </div>

      <div class="form-group">
        <label v-if="form.type === 'velo'" for="distance">Distance (km)</label>
        <label v-else-if="form.type === 'marche_course'" for="steps">Nombre de pas</label>
        <input 
          v-if="form.type === 'velo'"
          id="distance"
          v-model="form.distance" 
          type="number" 
          step="0.1"
          min="0"
          placeholder="Ex: 5.2"
          required
        />
        <input 
          v-else-if="form.type === 'marche_course'"
          id="steps"
          v-model="form.steps" 
          type="number" 
          min="0"
          placeholder="Ex: 7500"
          required
        />
      </div>

      <div v-if="form.type === 'marche_course' && form.steps" class="conversion-info">
        <p>Conversion : {{ form.steps }} pas = {{ (form.steps / 1500).toFixed(2) }} km</p>
      </div>

      <div class="form-actions">
        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Enregistrement...' : 'Déclarer l\'activité' }}
        </button>
        <button type="button" @click="$router.push('/dashboard')" class="btn-secondary">
          Annuler
        </button>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>
      <div v-if="success" class="success-message">
        {{ success }}
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();
const loading = ref(false);
const error = ref('');
const success = ref('');

const form = ref({
  date: '',
  type: '',
  distance: '',
  steps: ''
});

const today = computed(() => {
  return new Date().toISOString().split('T')[0];
});

onMounted(() => {
  form.value.date = today.value;
});

const submitActivity = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    const activityData = {
      date: form.value.date,
      type: form.value.type,
      distance_km: form.value.type === 'velo' ? parseFloat(form.value.distance) : null,
      pas: form.value.type === 'marche_course' ? parseInt(form.value.steps) : null
    };

    const response = await api.post('/activities', activityData);
    
    if (response.data.status === 'success') {
      success.value = 'Activité déclarée avec succès !';
      setTimeout(() => {
        router.push('/dashboard');
      }, 1500);
    } else {
      error.value = 'Erreur lors de la déclaration';
    }
  } catch (err) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else {
      error.value = 'Erreur lors de la déclaration de l\'activité';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.declare-activity {
  padding: 2rem;
  max-width: 600px;
  margin: 0 auto;
}

.activity-form {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

input, select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
}

.conversion-info {
  background: #f3f4f6;
  padding: 1rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
}

.form-actions {
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

.btn-primary:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.error-message {
  color: #dc2626;
  background: #fef2f2;
  padding: 1rem;
  border-radius: 6px;
  margin-top: 1rem;
}

.success-message {
  color: #059669;
  background: #f0fdf4;
  padding: 1rem;
  border-radius: 6px;
  margin-top: 1rem;
}

.already-declared-message {
  display: none;
}
</style>
