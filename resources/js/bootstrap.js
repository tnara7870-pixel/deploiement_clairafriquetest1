import axios from 'axios';
window.axios = axios;

// Indique à Laravel qu'il s'agit d'une requête AJAX/Fetch
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Envoie automatiquement les cookies de session avec les requêtes
window.axios.defaults.withCredentials = true;

// Récupère et injecte le jeton CSRF présent dans le <meta name="csrf-token">
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}