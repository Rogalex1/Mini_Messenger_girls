<template>
  <div class="auth-card">

    <div v-if="error" class="gc-alert gc-alert-error">
      <i class="ti ti-alert-circle"></i>
      {{ error }}
    </div>

    <div v-if="success" class="gc-alert gc-alert-success">
      <i class="ti ti-circle-check"></i>
      Compte créé avec succès ! Redirection...
    </div>

    <form @submit.prevent="handleRegister" novalidate>

      <!-- Nom d'utilisateur -->
      <div class="form-group">
        <label>Nom d'utilisateur</label>
        <div class="gc-input" :class="{ 'is-error': errors.username }">
          <i class="ti ti-at"></i>
          <input
            v-model="form.username"
            type="text"
            placeholder="alice"
            autocomplete="username"
          />
        </div>
        <span v-if="errors.username" class="gc-error-msg">{{ errors.username[0] }}</span>
      </div>

      <!-- Email -->
      <div class="form-group">
        <label>Email</label>
        <div class="gc-input" :class="{ 'is-error': errors.email }">
          <i class="ti ti-mail"></i>
          <input
            v-model="form.email"
            type="email"
            placeholder="votre@email.com"
            autocomplete="email"
          />
        </div>
        <span v-if="errors.email" class="gc-error-msg">{{ errors.email[0] }}</span>
      </div>

      <!-- Téléphone -->
      <div class="form-group">
        <label>
          Téléphone
          <span class="gc-optional">optionnel</span>
        </label>
        <div class="gc-input" :class="{ 'is-error': errors.phone }">
          <i class="ti ti-phone"></i>
          <input
            v-model="form.phone"
            type="tel"
            placeholder="+229 6000 0001"
            autocomplete="tel"
          />
        </div>
        <span v-if="errors.phone" class="gc-error-msg">{{ errors.phone[0] }}</span>
      </div>

      <!-- Mot de passe -->
      <div class="form-group">
        <label>Mot de passe</label>
        <div class="gc-input" :class="{ 'is-error': errors.password }">
          <i class="ti ti-lock"></i>
          <input
            v-model="form.password"
            :type="showPw ? 'text' : 'password'"
            placeholder="Min. 8 caractères"
            autocomplete="new-password"
          />
          <button type="button" @click="showPw = !showPw" class="gc-eye-btn" aria-label="Afficher le mot de passe">
            <i :class="showPw ? 'ti ti-eye-off' : 'ti ti-eye'"></i>
          </button>
        </div>
        <span v-if="errors.password" class="gc-error-msg">{{ errors.password[0] }}</span>
      </div>

      <!-- Jauge force -->
      <div v-if="form.password" class="gc-pw-strength">
        <div class="gc-pw-bars">
          <div
            v-for="i in 4" :key="i"
            class="gc-pw-bar"
            :class="{ active: pwStrength >= i, [`lvl-${pwStrength}`]: pwStrength >= i }"
          ></div>
        </div>
        <span class="gc-pw-text">{{ pwLabel }}</span>
      </div>

      <!-- Confirmation -->
      <div class="form-group">
        <label>Confirmation</label>
        <div class="gc-input" :class="{ 'is-error': pwMismatch }">
          <i class="ti ti-lock-check"></i>
          <input
            v-model="form.password_confirmation"
            :type="showPw ? 'text' : 'password'"
            placeholder="Répétez le mot de passe"
            autocomplete="new-password"
          />
        </div>
        <span v-if="pwMismatch" class="gc-error-msg">Les mots de passe ne correspondent pas.</span>
      </div>

      <!-- Submit -->
      <button type="submit" class="gc-btn-primary" :disabled="loading || pwMismatch">
        <i v-if="loading" class="ti ti-loader-2 gc-spin"></i>
        {{ loading ? 'Création...' : 'Créer mon compte' }}
      </button>
    </form>

    <div class="gc-divider"><span>Ou continuer avec</span></div>

    <div class="gc-social-row">
      <button class="gc-social-btn" @click="loginWith('google')">
        <i class="ti ti-brand-google gc-google-color"></i>
        Google
      </button>
      <button class="gc-social-btn" @click="loginWith('facebook')">
        <i class="ti ti-brand-facebook gc-facebook-color"></i>
        Facebook
      </button>
    </div>

    <p class="gc-footer-text">
      Vous avez déjà un compte?
      <RouterLink to="/login" class="gc-link-accent">Se connecter</RouterLink>
    </p>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const showPw  = ref(false)
const loading = ref(false)
const error   = ref('')
const success = ref(false)
const errors  = ref({})

const form = reactive({
  username: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const pwMismatch = computed(() =>
  form.password_confirmation.length > 0 &&
  form.password !== form.password_confirmation
)

const pwStrength = computed(() => {
  const p = form.password
  if (!p) return 0
  let s = 0
  if (p.length >= 8)           s++
  if (/[A-Z]/.test(p))         s++
  if (/[0-9]/.test(p))         s++
  if (/[^a-zA-Z0-9]/.test(p)) s++
  return s
})

const pwLabel = computed(() =>
  ['', 'Faible', 'Moyen', 'Fort', 'Très fort'][pwStrength.value] ?? ''
)

async function handleRegister() {
  error.value  = ''
  errors.value = {}
  if (pwMismatch.value) return

  loading.value = true
  const result = await auth.register(form)

  if (result.success) {
    success.value = true
  } else {
    errors.value = result.errors?.errors ?? {}
    error.value  = result.errors?.message ?? ''
  }

  loading.value = false
}

function loginWith(provider) {
  window.location.href = `${import.meta.env.VITE_API_URL}/auth/${provider}/redirect`
}
</script>

<style scoped>
/* ─── Card identique au Login ─────────────────── */
.auth-card {
  background: #ffffff;
  border-radius: 24px;
  padding: 32px 28px 24px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 8px 40px rgba(168, 85, 247, 0.12);
}

.gc-alert {
  display: flex; align-items: center; gap: 8px;
  padding: 11px 14px; border-radius: 10px;
  font-size: 13px; margin-bottom: 16px;
}
.gc-alert i { font-size: 16px; }
.gc-alert-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.gc-alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }

.form-group label {
  font-size: 13px; font-weight: 500; color: #374151;
  display: flex; align-items: center; gap: 8px;
}

.gc-optional {
  font-size: 11px; font-weight: 400; color: #9ca3af;
  background: #f3f4f6; padding: 1px 7px; border-radius: 20px;
}

.gc-input {
  display: flex; align-items: center; gap: 10px;
  border: 1.5px solid #e5e7eb; border-radius: 12px;
  padding: 0 14px; background: #fff; transition: all .2s;
}
.gc-input:focus-within {
  border-color: #a855f7;
  box-shadow: 0 0 0 3px rgba(168,85,247,.12);
}
.gc-input.is-error { border-color: #dc2626; background: #fef2f2; }
.gc-input > i { font-size: 17px; color: #c4b5fd; flex-shrink: 0; }
.gc-input input {
  flex: 1; border: none; background: transparent;
  font-size: 14px; color: #1a1a2e;
  padding: 12px 0; outline: none; min-width: 0;
}
.gc-input input::placeholder { color: #9ca3af; }

.gc-eye-btn {
  background: none; border: none; cursor: pointer;
  color: #c4b5fd; display: flex; align-items: center;
  padding: 0; font-size: 17px; transition: color .2s;
}
.gc-eye-btn:hover { color: #a855f7; }

.gc-error-msg { font-size: 12px; color: #dc2626; }

/* Jauge */
.gc-pw-strength {
  display: flex; align-items: center; gap: 10px;
  margin: -6px 0 14px;
}
.gc-pw-bars { display: flex; gap: 4px; flex: 1; }
.gc-pw-bar {
  flex: 1; height: 3px; background: #e5e7eb;
  border-radius: 99px; transition: background .3s;
}
.gc-pw-bar.active.lvl-1 { background: #ef4444; }
.gc-pw-bar.active.lvl-2 { background: #f59e0b; }
.gc-pw-bar.active.lvl-3 { background: #3b82f6; }
.gc-pw-bar.active.lvl-4 { background: #22c55e; }
.gc-pw-text { font-size: 11px; color: #6b7280; white-space: nowrap; }

.gc-btn-primary {
  width: 100%; padding: 14px;
  background: linear-gradient(90deg, #ec4899, #a855f7);
  color: #fff; font-size: 15px; font-weight: 600;
  border: none; border-radius: 12px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: opacity .2s, transform .15s;
}
.gc-btn-primary:hover:not(:disabled)  { opacity: .9; }
.gc-btn-primary:active:not(:disabled) { transform: scale(0.98); }
.gc-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.gc-spin { animation: spin .8s linear infinite; font-size: 16px; }
@keyframes spin { to { transform: rotate(360deg); } }

.gc-divider {
  display: flex; align-items: center; gap: 12px;
  margin: 20px 0; font-size: 12px; color: #9ca3af;
}
.gc-divider::before, .gc-divider::after {
  content: ''; flex: 1; height: 1px; background: #e5e7eb;
}

.gc-social-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }

.gc-social-btn {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 11px; border: 1.5px solid #e5e7eb; border-radius: 12px;
  font-size: 13px; font-weight: 500; color: #374151;
  background: #fff; cursor: pointer; transition: all .2s;
}
.gc-social-btn i { font-size: 17px; }
.gc-social-btn:hover { border-color: #a855f7; background: #fdf4ff; }

.gc-google-color   { color: #ea4335; }
.gc-facebook-color { color: #1877f2; }

.gc-footer-text { text-align: center; font-size: 13px; color: #6b7280; }
.gc-link-accent { color: #a855f7; font-weight: 600; }
.gc-link-accent:hover { text-decoration: underline; }
</style>