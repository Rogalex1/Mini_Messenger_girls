<template>
  <div >
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
      <div v-if="error" class="gc-alert gc-alert-error">
        <i class="ti ti-alert-circle"></i>
        {{ error }}
      </div>

      <div v-if="success" class="gc-alert gc-alert-success">
        <i class="ti ti-circle-check"></i>
        Compte créé avec succès ! Redirection...
      </div>

      <form @submit.prevent="handleRegister" novalidate class="auth-form">
        
        <div class="form-group">
          <label class="form-label">Nom d'utilisateur</label>
          <div class="input-wrapper" :class="{ 'error': errors.username }">
            <i class="ti ti-at icon-placeholder"></i>
            <input
              v-model="form.username"
              type="text"
              placeholder="alice"
              autocomplete="username"
            />
          </div>
          <span v-if="errors.username" class="field-error">{{ errors.username[0] }}</span>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <div class="input-wrapper" :class="{ 'error': errors.email }">
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
          <label class="form-label">
            Téléphone
            <span class="gc-optional">optionnel</span>
          </label>
          <div class="input-wrapper" :class="{ 'error': errors.phone }">
            <i class="ti ti-phone icon-placeholder"></i>
            <input
              v-model="form.phone"
              type="tel"
              placeholder="+229 6000 0001"
              autocomplete="tel"
            />
          </div>
          <span v-if="errors.phone" class="field-error">{{ errors.phone[0] }}</span>
        </div>

        <div class="form-group">
          <label class="form-label">Mot de passe</label>
          <div class="input-wrapper" :class="{ 'error': errors.password }">
            <i class="ti ti-lock icon-placeholder"></i>
            <input
              v-model="form.password"
              :type="showPw ? 'text' : 'password'"
              placeholder="Min. 8 caractères"
              autocomplete="new-password"
            />
            <button type="button" @click="showPw = !showPw" class="toggle-pw" aria-label="Afficher le mot de passe">
              <i :class="showPw ? 'ti ti-eye-off' : 'ti ti-eye'"></i>
            </button>
          </div>
          <span v-if="errors.password" class="field-error">{{ errors.password[0] }}</span>
        </div>

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

        <div class="form-group">
          <label class="form-label">Confirmation</label>
          <div class="input-wrapper" :class="{ 'error': pwMismatch }">
            <i class="ti ti-lock-check icon-placeholder"></i>
            <input
              v-model="form.password_confirmation"
              :type="showPw ? 'text' : 'password'"
              placeholder="Répétez le mot de passe"
              autocomplete="new-password"
            />
          </div>
          <span v-if="pwMismatch" class="field-error">Les mots de passe ne correspondent pas.</span>
        </div>

        <button type="submit" class="btn-submit-gradient" :disabled="loading || pwMismatch">
          <i v-if="loading" class="ti ti-loader-2 spin"></i>
          <span>{{ loading ? 'Création...' : 'Créer mon compte' }}</span>
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
        Vous avez déjà un compte ? 
        <RouterLink to="/login" class="signup-link">Se connecter</RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
/* --- FOND DE PAGE GLOBAL --- */
.auth-page-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #fdf4f8 0%, #f4f3ff 100%);
  padding: 40px 24px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* --- LOGO & TITRES --- */
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

/* --- LA CARTE BLANCHE --- */
.auth-card {
  background: #ffffff;
  border-radius: 24px;
  padding: 40px 36px;
  /* width: 100%; */
  /* max-width: 450px; */
  box-shadow: 0 10px 40px rgba(154, 131, 163, 0.06);
}

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

.form-label {
  font-size: 13.5px;
  font-weight: 600;
  color: #3c4257;
  padding-left: 2px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.gc-optional {
  font-size: 11px;
  font-weight: 400;
  color: #a3aabf;
  background: #f7f1f5;
  padding: 2px 8px;
  border-radius: 20px;
}

/* --- INPUTS --- */
.input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #ffffff;
  border: 1px solid #fbe3f2;
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

/* --- JAUGE DE FORCE MOT DE PASSE --- */
.gc-pw-strength {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: -6px;
  padding: 0 2px;
}
.gc-pw-bars {
  display: flex;
  gap: 5px;
  flex: 1;
}
.gc-pw-bar {
  flex: 1;
  height: 4px;
  background: #f0e6ed;
  border-radius: 99px;
  transition: background 0.3s ease;
}
.gc-pw-bar.active.lvl-1 { background: #ff5c5c; }
.gc-pw-bar.active.lvl-2 { background: #ffb03a; }
.gc-pw-bar.active.lvl-3 { background: #4da3ff; }
.gc-pw-bar.active.lvl-4 { background: #32d74b; }

.gc-pw-text {
  font-size: 11.5px;
  color: #718096;
  font-weight: 500;
  white-space: nowrap;
}

/* --- BOUTON PRINCIPAL --- */
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
  margin-top: 8px;
}

.btn-submit-gradient:hover:not(:disabled) { opacity: 0.95; }
.btn-submit-gradient:active:not(:disabled) { transform: scale(0.99); }
.btn-submit-gradient:disabled { opacity: 0.5; cursor: not-allowed; }

/* --- SEPARATEUR --- */
.divider {
  display: flex;
  align-items: center;
  gap: 16px;
  margin: 24px 0;
  color: #718096;
  font-size: 13px;
}
.divider::before, .divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #f3e8f0;
}

/* --- BOUTONS SOCIAUX --- */
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
  border: 1px solid #fbe3f2;
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

/* --- FOOTER CARD --- */
.auth-footer {
  text-align: center;
  font-size: 13.5px;
  color: #4f566b;
  margin: 26px 0 0 0;
}

.signup-link {
  color: #e251b2;
  text-decoration: none;
  font-weight: 600;
  margin-left: 4px;
}
.signup-link:hover { text-decoration: underline; }

/* --- ALERTES & UTILS --- */
.gc-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 13.5px;
  margin-bottom: 18px;
}
.gc-alert-error { background: #fff5f5; color: #e53e3e; border: 1px solid #fed7d7; }
.gc-alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.field-error { font-size: 12px; color: #e53e3e; margin-top: 2px; padding-left: 2px; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>



























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