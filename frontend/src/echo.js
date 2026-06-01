import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const echo = new Echo({
  broadcaster:      'reverb',
  key:              import.meta.env.VITE_REVERB_APP_KEY,
  wsHost:           import.meta.env.VITE_REVERB_HOST,
  wsPort:           import.meta.env.VITE_REVERB_PORT,
  wssPort:          import.meta.env.VITE_REVERB_PORT,
  forceTLS:         import.meta.env.VITE_REVERB_SCHEME === 'https',
  enabledTransports: ['ws', 'wss'],

  // Authentification des canaux privés
  authEndpoint: `${import.meta.env.VITE_API_URL.replace('/api', '')}/broadcasting/auth`,
  auth: {
    headers: {
      Authorization: `Bearer ${localStorage.getItem('token')}`,
    },
  },
  // Fonction pour récupérer le token dynamiquement si nécessaire (certaines versions de Echo le supportent via une fonction dans headers)
})

// Intercepteur pour mettre à jour le token si nécessaire
echo.connector.pusher.connection.bind('state_change', (states) => {
    echo.options.auth.headers.Authorization = `Bearer ${localStorage.getItem('token')}`;
});

export default echo