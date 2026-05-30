<template>
  <div class="auth-page-wrapper">
    <div class="auth-header-global">
      <div class="brand-logo-container">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="brand-icon">
          <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
      </div>
      <h1 class="brand-title">GlowChat</h1>
      <p class="brand-subtitle">Bienvenue dans votre espace de messagerie premium</p>
    </div>

    <div class="auth-card">
      <div v-if="error" class="alert alert-error">
        <i class="ti ti-alert-circle"></i>
        {{ error }}
      </div>

      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group">
          <label class="form-label">Email</label>
          <div class="input-wrapper" :class="{ error: errors.email }">
            <i class="ti ti-mail icon-placeholder"></i>
            <input
              v-model="form.email"
              type="email"
              placeholder="votre@email.com"
              autocomplete="email"
            />
          </div>
          <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
        </div>

        <div class="form-group">
          <label class="form-label">Mot de passe</label>
          <div class="input-wrapper" :class="{ error: errors.password }">
            <i class="ti ti-lock icon-placeholder"></i>
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="current-password"
            />
            <button type="button" class="toggle-pw" @click="showPassword = !showPassword">
              <i :class="showPassword ? 'ti ti-eye-off' : 'ti ti-eye'"></i>
            </button>
          </div>
          <span v-if="errors.password" class="field-error">{{ errors.password[0] }}</span>
        </div>

        <div class="form-options-row">
          <label class="checkbox-container">
            <input type="checkbox" v-model="form.remember" />
            <span class="checkbox-label">Se souvenir de moi</span>
          </label>
          <RouterLink to="/forgot-password" class="forgot-link">
            Mot de passe oublié ?
          </RouterLink>
        </div>

        <button type="submit" class="btn-submit-gradient" :disabled="loading">
          <i v-if="loading" class="ti ti-loader-2 spin"></i>
          <span>{{ loading ? 'Connexion...' : 'Se connecter' }}</span>
        </button>
      </form>

      <div class="divider">
        <span>Ou continuer avec</span>
      </div>

      <div class="social-grid">
        <button type="button" class="btn-social" @click="loginWith('google')">
          <i class="ti ti-brand-google brand-g"></i>
          <span>Google</span>
        </button>
        <button type="button" class="btn-social" @click="loginWith('facebook')">
          <i class="ti ti-brand-facebook brand-fb"></i>
          <span>Facebook</span>
        </button>
      </div>

      <p class="auth-footer">
        Vous n'avez pas de compte ? 
        <RouterLink to="/register" class="signup-link">S'inscrire</RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
/* Conteneur principal de la page (effet fond rosé très doux) */
.auth-page-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #fdf4f8 0%, #f4f3ff 100%);
  padding: 24px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* --- HEADER GLOBAL (Logo + Titres) --- */
.auth-header-global {
  text-align: center;
  margin-bottom: 24px;
}

.brand-logo-container {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, #f784ce 0%, #b876ff 100%);
  border-radius: 22px;
  margin-bottom: 20px;
  box-shadow: 0 8px 20px rgba(184, 118, 255, 0.25);
}

.brand-icon {
  width: 32px;
  height: 32px;
  color: #ffffff;
}

.brand-title {
  font-size: 42px;
  font-weight: 700;
  background: linear-gradient(to right, #e251b2, #aa52fa);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0 0 8px 0;
  letter-spacing: -0.5px;
}

.brand-subtitle {
  font-size: 14px;
  color: #4f566b;
  margin: 0;
  font-weight: 400;
}

/* --- CARTE BLANCHE --- */
.auth-card {
  background: #ffffff;
  border-radius: 24px;
  padding: 44px 36px;
  width: 100%;
  max-width: 450px;
  box-shadow: 0 10px 40px rgba(154, 131, 163, 0.06);
}

/* Formulaire */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  font-size: 13.5px;
  font-weight: 600;
  color: #3c4257;
  padding-left: 2px;
}

/* Inputs de la maquette (Bordure fine rosée, intérieur blanc) */
.input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #ffffff;
  border: 1px solid #fbe3f2; /* Bordure douce rosée */
  border-radius: 12px;
  padding: 0 16px;
  height: 50px;
  transition: all 0.2s ease;
}

.input-wrapper:focus-within {
  border-color: #b876ff;
  box-shadow: 0 0 0 4px rgba(184, 118, 255, 0.1);
}

.input-wrapper.error {
  border-color: #ff6464;
  background: #fffafa;
}

.icon-placeholder {
  font-size: 18px;
  color: #9098b1;
  flex-shrink: 0;
}

.input-wrapper input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 14.5px;
  color: #2a2f45;
  outline: none;
  width: 100%;
}

.input-wrapper input::placeholder {
  color: #b2b9ce;
}

.toggle-pw {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #9098b1;
  display: flex;
  align-items: center;
  transition: color 0.2s;
}

.toggle-pw:hover {
  color: #b876ff;
}

/* Ligne d'options (Checkbox + Link) */
.form-options-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 2px;
  font-size: 13px;
}

.checkbox-container {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: #4f566b;
  user-select: none;
}

.checkbox-container input {
  cursor: pointer;
  accent-color: #e251b2;
  width: 15px;
  height: 15px;
}

.forgot-link {
  color: #e251b2;
  text-decoration: none;
  font-weight: 500;
}

.forgot-link:hover {
  text-decoration: underline;
}

/* Bouton principal en dégradé */
.btn-submit-gradient {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: linear-gradient(to right, #ec5bb0, #b266f8);
  color: #ffffff;
  border: none;
  border-radius: 14px;
  height: 50px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s, transform 0.1s;
  box-shadow: 0 4px 15px rgba(236, 91, 176, 0.2);
  margin-top: 10px;
}

.btn-submit-gradient:hover:not(:disabled) {
  opacity: 0.95;
}

.btn-submit-gradient:active:not(:disabled) {
  transform: scale(0.99);
}

.btn-submit-gradient:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Séparateur */
.divider {
  display: flex;
  align-items: center;
  gap: 16px;
  margin: 26px 0;
  color: #718096;
  font-size: 13px;
  text-transform: lowercase;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #f3e8f0;
}

/* Boutons Sociaux */
.social-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.btn-social {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  height: 48px;
  border: 1px solid #fbe3f2; /* Même bordure subtile rosée */
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  color: #2d3748;
  background: #ffffff;
  cursor: pointer;
  transition: background-color 0.2s, border-color 0.2s;
}

.btn-social:hover {
  background-color: #fffafd;
  border-color: #f784ce;
}

.brand-g { color: #ea4335; }
.brand-fb { color: #1877f2; }

/* Footer de la carte */
.auth-footer {
  text-align: center;
  font-size: 13.5px;
  color: #4f566b;
  margin: 28px 0 0 0;
}

.signup-link {
  color: #e251b2;
  text-decoration: none;
  font-weight: 600;
  margin-left: 4px;
}

.signup-link:hover {
  text-decoration: underline;
}

/* Alertes & Utilitaires */
.alert {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  border-radius: 10px;
  font-size: 13px;
  margin-bottom: 16px;
}
.alert-error {
  background: #fff5f5;
  color: #e53e3e;
  border: 1px solid #fed7d7;
}
.field-error {
  font-size: 12px;
  color: #e53e3e;
  margin-top: -2px;
}
.spin {
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>




















<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
const router = useRouter();

const auth = useAuthStore()

const form = reactive({ email: '', password: '' })
const errors = ref({})
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)

async function handleLogin() {
  error.value = ''
  errors.value = {}
  loading.value = true

  const result = await auth.login(form)
  router.push("/sessionSlide")
  if (!result.success) {
    if (result.errors?.errors) {
      errors.value = result.errors.errors
    } else {
      error.value = result.message || 'Une erreur est survenue.'
    }
  }

  loading.value = false
}

function loginWith(provider) {
  window.location.href = `${import.meta.env.VITE_API_URL}/auth/${provider}/redirect`
}
</script>