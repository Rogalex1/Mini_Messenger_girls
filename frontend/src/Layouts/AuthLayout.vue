<template>
  <div class="auth-wrapper">
    <div class="auth-left">
      <div class="auth-brand">
        <div class="brand-icon">
          <i class="ti ti-bolt"></i>
        </div>
        <h1>GlowChat</h1>
        <p>Connectez-vous avec ceux qui comptent.</p>
      </div>
      <div class="auth-features">
        <div class="feature-item">
          <i class="ti ti-message-circle"></i>
          <span>Messages en temps réel</span>
        </div>
        <div class="feature-item">
          <i class="ti ti-video"></i>
          <span>Appels audio & vidéo</span>
        </div>
        <div class="feature-item">
          <i class="ti ti-shield-check"></i>
          <span>Chiffrement de bout en bout</span>
        </div>
      </div>
    </div>
    <div class="auth-right">
      <RouterView />
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 1fr;
}

.auth-left {
  background: #6C63FF;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 60px;
  gap: 48px;
}

.auth-brand {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.brand-icon {
  width: 56px;
  height: 56px;
  background: rgba(255,255,255,0.2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-icon i {
  font-size: 28px;
  color: #fff;
}

.auth-brand h1 {
  font-size: 36px;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.5px;
}

.auth-brand p {
  font-size: 16px;
  color: rgba(255,255,255,0.75);
  line-height: 1.6;
}

.auth-features {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 14px;
  color: rgba(255,255,255,0.9);
  font-size: 15px;
}

.feature-item i {
  font-size: 20px;
  color: rgba(255,255,255,0.7);
}

.auth-right {
  background: #f4f6f9;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

@media (max-width: 768px) {
  .auth-wrapper { grid-template-columns: 1fr; }
  .auth-left { display: none; }
}
</style>