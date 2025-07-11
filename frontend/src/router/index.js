import { createRouter, createWebHistory } from 'vue-router';
import Login from '../pages/Login.vue';
import Dashboard from '../pages/Dashboard.vue';
import DeclareActivity from '../pages/DeclareActivity.vue';
import Stats from '../pages/Stats.vue';

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: Login },
  { path: '/dashboard', component: Dashboard },
  { path: '/declare', component: DeclareActivity },
  { path: '/stats', component: Stats },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Protection des routes
router.beforeEach((to, from, next) => {
  const publicPages = ['/login'];
  const authRequired = !publicPages.includes(to.path);
  const loggedIn = !!localStorage.getItem('token');
  
  if (authRequired && !loggedIn) {
    return next('/login');
  }
  
  if (to.path === '/login' && loggedIn) {
    return next('/dashboard');
  }
  
  next();
});

export default router;