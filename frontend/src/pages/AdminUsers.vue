<template>
  <div class="admin-users">
    <h1>Gestion des utilisateurs</h1>
    <div v-if="loading" class="loading">Chargement...</div>
    <div v-else>
      <table class="users-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Équipe</th>
            <th>Rôle</th>
            <th>Date de création</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.nom }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.equipe?.nom || '-' }}</td>
            <td>{{ user.is_admin ? 'Admin' : 'Utilisateur' }}</td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
              <button @click="editUser(user)" class="btn-edit">Modifier</button>
              <button @click="deleteUser(user)" class="btn-delete" :disabled="user.is_admin && adminCount <= 1">Supprimer</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="error" class="error-message">{{ error }}</div>
    </div>

    <!-- Modal d'édition -->
    <div v-if="showEditModal" class="modal-overlay">
      <div class="modal">
        <h2>Modifier l'utilisateur</h2>
        <form @submit.prevent="submitEdit">
          <div class="form-group">
            <label>Nom</label>
            <input v-model="editForm.nom" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="editForm.email" type="email" required />
          </div>
          <div class="form-group">
            <label>Équipe</label>
            <select v-model="editForm.equipe_id" required>
              <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.nom }}</option>
            </select>
          </div>
          <div class="form-group">
            <label>Rôle</label>
            <select v-model="editForm.is_admin">
              <option :value="true">Admin</option>
              <option :value="false">Utilisateur</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="submit" class="btn-primary">Enregistrer</button>
            <button type="button" @click="closeEditModal" class="btn-secondary">Annuler</button>
          </div>
        </form>
        <div v-if="editError" class="error-message">{{ editError }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../api';
import { useRouter } from 'vue-router';

const router = useRouter();
const users = ref([]);
const teams = ref([]);
const loading = ref(true);
const error = ref('');
const adminCount = ref(0);

const showEditModal = ref(false);
const editForm = ref({});
const editError = ref('');

// Vérifier que l'utilisateur est admin
const user = JSON.parse(localStorage.getItem('user') || '{}');
if (!user.is_admin) {
  router.push('/dashboard');
}

const fetchUsers = async () => {
  loading.value = true;
  error.value = '';
  try {
    const res = await api.get('/admin/users');
    if (res.data.status === 'success') {
      users.value = res.data.data;
      adminCount.value = users.value.filter(u => u.is_admin).length;
    } else {
      error.value = 'Erreur lors du chargement des utilisateurs';
    }
  } catch (e) {
    error.value = 'Erreur lors du chargement des utilisateurs';
  } finally {
    loading.value = false;
  }
};

const fetchTeams = async () => {
  try {
    const res = await api.get('/stats/teams');
    if (res.data.status === 'success') {
      teams.value = res.data.data;
    }
  } catch {}
};

onMounted(() => {
  fetchUsers();
  fetchTeams();
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString();
};

const editUser = (user) => {
  editForm.value = { ...user, is_admin: !!user.is_admin };
  showEditModal.value = true;
  editError.value = '';
};

const closeEditModal = () => {
  showEditModal.value = false;
  editForm.value = {};
  editError.value = '';
};

const submitEdit = async () => {
  editError.value = '';
  try {
    const res = await api.put(`/admin/users/${editForm.value.id}`, {
      nom: editForm.value.nom,
      email: editForm.value.email,
      equipe_id: editForm.value.equipe_id,
      is_admin: editForm.value.is_admin,
    });
    if (res.data.status === 'success') {
      await fetchUsers();
      closeEditModal();
    } else {
      editError.value = 'Erreur lors de la modification';
    }
  } catch (e) {
    editError.value = e.response?.data?.message || 'Erreur lors de la modification';
  }
};

const deleteUser = async (user) => {
  if (!confirm('Supprimer cet utilisateur ?')) return;
  try {
    const res = await api.delete(`/admin/users/${user.id}`);
    if (res.data.status === 'success') {
      await fetchUsers();
    } else {
      error.value = res.data.message || 'Erreur lors de la suppression';
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de la suppression';
  }
};
</script>

<style scoped>
.admin-users {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}
.users-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
}
.users-table th, .users-table td {
  border: 1px solid #e5e7eb;
  padding: 0.75rem;
  text-align: left;
}
.users-table th {
  background: #f3f4f6;
}
.btn-edit, .btn-delete {
  margin-right: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}
.btn-edit {
  background: #2563eb;
  color: white;
}
.btn-delete {
  background: #dc2626;
  color: white;
}
.btn-edit:hover {
  background: #1d4ed8;
}
.btn-delete:disabled {
  background: #fca5a5;
  cursor: not-allowed;
}
.loading {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}
.error-message {
  color: #dc2626;
  background: #fef2f2;
  padding: 1rem;
  border-radius: 6px;
  margin-top: 1rem;
}
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  min-width: 350px;
  max-width: 90vw;
}
.form-group {
  margin-bottom: 1rem;
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
.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}
.btn-primary {
  background: #2563eb;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
}
.btn-secondary {
  background: #6b7280;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
}
</style> 