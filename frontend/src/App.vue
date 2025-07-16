<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from './stores/user';

const router = useRouter();
const userStore = useUserStore();

const isLoggedIn = computed(() => !!userStore.token);
const isAdmin = computed(() => userStore.user && (userStore.user.is_admin === true || userStore.user.is_admin === 1));

const logout = () => {
  userStore.clearUser();
  router.push('/login');
};
</script>

<template>
  <div id="app">
    <nav v-if="isLoggedIn" class="navbar">
      <div class="nav-container">
        <div class="nav-brand">
          <router-link to="/dashboard">Challenge Mobilité</router-link>
        </div>
        <div class="nav-links">
          <router-link to="/dashboard">Dashboard</router-link>
          <router-link to="/declare">Déclarer</router-link>
          <router-link to="/stats">Classements</router-link>
          <router-link v-if="isAdmin" to="/admin/users">Gestion utilisateurs</router-link>
          <button @click="logout" class="logout-btn">Déconnexion</button>
        </div>
      </div>
    </nav>
    
    <router-view />
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background-color: #f9fafb;
  color: #1f2937;
}

.navbar {
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  padding: 1rem 0;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-brand a {
  font-size: 1.5rem;
  font-weight: bold;
  color: #2563eb;
  text-decoration: none;
}

.nav-links {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.nav-links a {
  color: #6b7280;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  color: #2563eb;
}

.logout-btn {
  background: #dc2626;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.logout-btn:hover {
  background: #b91c1c;
}

@media (max-width: 768px) {
  .nav-container {
    flex-direction: column;
    gap: 1rem;
  }
  
  .nav-links {
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>
