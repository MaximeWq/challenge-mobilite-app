import { createRouter, createWebHistory } from 'vue-router';
import Login from '../pages/Login.vue';
import Dashboard from '../pages/Dashboard.vue';
import DeclareActivity from '../pages/DeclareActivity.vue';
import Stats from '../pages/Stats.vue';
import AdminUsers from '../pages/AdminUsers.vue';

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: Login },
  { path: '/dashboard', component: Dashboard },
  { path: '/declare', component: DeclareActivity },
  { path: '/stats', component: Stats },
  { path: '/admin/users', component: AdminUsers },
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

  // Protection de la route admin
  if (to.path === '/admin/users') {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.is_admin) {
      return next('/dashboard');
    }
  }
  
  next();
});

export default router;