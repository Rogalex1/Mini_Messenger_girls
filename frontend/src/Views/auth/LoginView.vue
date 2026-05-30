<template>
  <div class="auth-card">
    <div class="auth-header">
      <h2>Bon retour</h2>
      <p>Connectez-vous à votre compte GlowChat</p>
    </div>

    <!-- Erreur globale -->
    <div v-if="error" class="alert alert-error">
      <i class="ti ti-alert-circle"></i>
      {{ error }}
    </div>

    <form @submit.prevent="handleLogin" class="auth-form">
      <!-- Email -->
      <div class="form-group">
        <label>Email</label>
        <div class="input-wrapper" :class="{ error: errors.email }">
          <i class="ti ti-mail"></i>
          <input
            v-model="form.email"
            type="email"
            placeholder="votre@email.com"
            autocomplete="email"
          />
        </div>
        <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
      </div>

      <!-- Mot de passe -->
      <div class="form-group">
        <div class="label-row">
          <label>Mot de passe</label>
          <RouterLink to="/forgot-password" class="forgot-link">
            Mot de passe oublié ?
          </RouterLink>
        </div>
        <div class="input-wrapper" :class="{ error: errors.password }">
          <i class="ti ti-lock"></i>
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

      <!-- Submit -->
      <button type="submit" class="btn-primary" :disabled="loading">
        <i v-if="loading" class="ti ti-loader-2 spin"></i>
        <span>{{ loading ? 'Connexion...' : 'Se connecter' }}</span>
      </button>
    </form>

    <!-- Séparateur -->
    <div class="divider">
      <span>ou continuer avec</span>
    </div>

    <!-- Social -->
    <div class="social-buttons">
      <button class="btn-social" @click="loginWith('google')">
        <i class="ti ti-brand-google"></i>
        Google
      </button>
      <button class="btn-social" @click="loginWith('facebook')">
        <i class="ti ti-brand-facebook"></i>
        Facebook
      </button>
    </div>

    <!-- Footer -->
    <p class="auth-footer">
      Pas encore de compte ?
      <RouterLink to="/register">S'inscrire</RouterLink>
    </p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'

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

<style scoped>
.auth-card {
  background: #fff;
  border-radius: 20px;
  padding: 40px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08);
}

.auth-header {
  margin-bottom: 28px;
}

.auth-header h2 {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 6px;
}

.auth-header p {
  font-size: 14px;
  color: #6b7280;
}

.alert {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 13px;
  margin-bottom: 16px;
}

.alert-error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.alert i { font-size: 16px; }

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #374151;
}

.label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.forgot-link {
  font-size: 12px;
  color: #6C63FF;
}

.forgot-link:hover { text-decoration: underline; }

.input-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f9fafb;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  padding: 0 14px;
  transition: all .2s;
}

.input-wrapper:focus-within {
  border-color: #6C63FF;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(108,99,255,.1);
}

.input-wrapper.error {
  border-color: #dc2626;
  background: #fef2f2;
}

.input-wrapper i {
  font-size: 16px;
  color: #9ca3af;
  flex-shrink: 0;
}

.input-wrapper input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 14px;
  color: #1a1a2e;
  padding: 12px 0;
  outline: none;
}

.input-wrapper input::placeholder { color: #9ca3af; }

.toggle-pw {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  color: #9ca3af;
  display: flex;
  align-items: center;
}

.toggle-pw i { font-size: 16px; }
.toggle-pw:hover { color: #6C63FF; }

.field-error {
  font-size: 12px;
  color: #dc2626;
}

.btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #6C63FF;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 13px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
  margin-top: 4px;
}

.btn-primary:hover:not(:disabled) { background: #5a52e0; }
.btn-primary:active:not(:disabled) { transform: scale(0.98); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.spin {
  animation: spin .8s linear infinite;
  font-size: 16px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 20px 0;
  color: #9ca3af;
  font-size: 13px;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}

.social-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.btn-social {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 11px;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  background: #fff;
  cursor: pointer;
  transition: all .2s;
}

.btn-social i { font-size: 16px; }
.btn-social:hover { border-color: #6C63FF; color: #6C63FF; background: #f5f3ff; }

.auth-footer {
  text-align: center;
  font-size: 13px;
  color: #6b7280;
  margin-top: 20px;
}

.auth-footer a {
  color: #6C63FF;
  font-weight: 600;
}

.auth-footer a:hover { text-decoration: underline; }
</style>