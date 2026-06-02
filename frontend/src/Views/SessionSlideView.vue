<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const currentSlide = ref(0);

const slides = [
  {
    title: "Bienvenue sur GlowChat",
    description:
      "Une application moderne pour discuter avec vos amis et créer des groupes facilement.",
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>`
  },
  {
    title: "Messagerie instantanée",
    description:
      "Envoyez et recevez vos messages en temps réel sans recharger la page.",
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
    </svg>`
  },
  {
    title: "Créez des groupes",
    description:
      "Organisez vos discussions entre amis dans des groupes simples et efficaces.",
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
      <circle cx="9" cy="7" r="4"/>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    </svg>`
  },
  {
    title: "Personnalisez votre profil",
    description:
      "Ajoutez une photo, une bio et rendez votre expérience unique.",
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
      <circle cx="12" cy="7" r="4"/>
    </svg>`
  },
];

const slide = computed(() => slides[currentSlide.value]);

const nextSlide = () => {
  if (currentSlide.value < slides.length - 1) {
    currentSlide.value++;
  } else {
    router.push("/messages");
  }
};

const skip = () => {
  router.push("/messages");
};
</script>

<template>
  <div class="onboarding-container">
    <button class="skip-btn" @click="skip">
      Passer
    </button>

    <div class="slide-content">
      <div class="svg-container" v-html="slide.svg"></div>
      <h1>{{ slide.title }}</h1>
      <p>{{ slide.description }}</p>
    </div>

    <div class="dots">
      <span
        v-for="(item, index) in slides"
        :key="index"
        class="dot"
        :class="{ active: currentSlide === index }"
      ></span>
    </div>

    <button class="next-btn" @click="nextSlide">
      {{ currentSlide === slides.length - 1 ? "Commencer" : "Suivant" }}
      <svg v-if="currentSlide < slides.length - 1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="5" y1="12" x2="19" y2="12"/>
        <polyline points="12 5 19 12 12 19"/>
      </svg>
    </button>
  </div>
</template>

<style scoped>
.onboarding-container {
  height: 100vh;
  background: linear-gradient(180deg, #fff5f7 0%, #ffffff 50%, #fffaf0 100%);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: var(--gc-spacing-xl);
  max-width: 500px;
  margin: 0 auto;
}

.skip-btn {
  align-self: flex-end;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--gc-primary);
  font-weight: 700;
  font-size: var(--gc-font-size-sm);
  padding: var(--gc-spacing-sm);
  border-radius: var(--gc-radius-lg);
  transition: all var(--gc-transition-fast);
}

.skip-btn:hover {
  background: var(--gc-accent);
}

.slide-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  gap: var(--gc-spacing-lg);
}

.svg-container {
  width: 140px;
  height: 140px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  border-radius: var(--gc-radius-2xl);
  color: white;
  box-shadow: 0 20px 40px rgba(236, 72, 153, 0.3);
  margin-bottom: var(--gc-spacing-lg);
}

.svg-container svg {
  width: 70px;
  height: 70px;
  stroke-width: 2;
}

h1 {
  font-size: var(--gc-font-size-2xl);
  color: var(--gc-gray-800);
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.5px;
}

p {
  color: var(--gc-gray-500);
  line-height: 1.7;
  font-size: var(--gc-font-size-base);
  margin: 0;
  max-width: 360px;
}

.dots {
  display: flex;
  justify-content: center;
  gap: var(--gc-spacing-sm);
  margin-bottom: var(--gc-spacing-xl);
}

.dot {
  width: 10px;
  height: 10px;
  background: var(--gc-gray-200);
  border-radius: 50%;
  transition: all var(--gc-transition-base);
}

.dot.active {
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  width: 32px;
  border-radius: var(--gc-radius-full);
}

.next-btn {
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  color: white;
  border: none;
  padding: var(--gc-spacing-md) var(--gc-spacing-xl);
  border-radius: var(--gc-radius-xl);
  cursor: pointer;
  font-size: var(--gc-font-size-base);
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--gc-spacing-sm);
  transition: all var(--gc-transition-base);
  box-shadow: 0 8px 20px rgba(236, 72, 153, 0.35);
}

.next-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(236, 72, 153, 0.45);
}

.next-btn svg {
  width: 20px;
  height: 20px;
}

@media (min-width: 768px) {
  .onboarding-container {
    padding: var(--gc-spacing-2xl);
  }
  
  .svg-container {
    width: 180px;
    height: 180px;
    border-radius: var(--gc-radius-2xl);
  }
  
  .svg-container svg {
    width: 90px;
    height: 90px;
  }
  
  h1 {
    font-size: 2.25rem;
  }
}
</style>
