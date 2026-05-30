<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const currentSlide = ref(0);

const slides = [
  {
    title: "Bienvenue sur GlowChat ✨",
    description:
      "Une application moderne pour discuter avec vos amis et créer des groupes facilement.",
    image: "💬",
  },
  {
    title: "Messagerie instantanée ⚡",
    description:
      "Envoyez et recevez vos messages en temps réel sans recharger la page.",
    image: "📨",
  },
  {
    title: "Créez des groupes 👥",
    description:
      "Organisez vos discussions entre amis dans des groupes simples et efficaces.",
    image: "👨‍👩‍👧‍👦",
  },
  {
    title: "Personnalisez votre profil 🎨",
    description:
      "Ajoutez une photo, une bio et rendez votre expérience unique.",
    image: "🌸",
  },
];

const slide = computed(() => slides[currentSlide.value]);

const nextSlide = () => {
  if (currentSlide.value < slides.length - 1) {
    currentSlide.value++;
  } else {
    router.push("/sessionSlide");
  }
};

const skip = () => {
  router.push("/sessionSlide");
};
</script>

<template>
  <div class="onboarding-container">
    <button class="skip-btn" @click="skip">
      Passer
    </button>

    <div class="slide-content">
      <div class="emoji">
        {{ slide.image }}
      </div>

      <h1>{{ slide.title }}</h1>

      <p>
        {{ slide.description }}
      </p>
    </div>

    <div class="dots">
      <span
        v-for="(item, index) in slides"
        :key="index"
        class="dot"
        :class="{ active: currentSlide === index }"
      />
    </div>

    <button class="next-btn" @click="nextSlide">
      {{ currentSlide === slides.length - 1 ? "Commencer" : "Suivant" }}
    </button>
  </div>
</template>

<style scoped>
.onboarding-container {
  height: 100vh;
  background: linear-gradient(180deg, #ffe4ef, #fff);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 30px;
}

.skip-btn {
  align-self: flex-end;
  border: none;
  background: transparent;
  cursor: pointer;
  color: #ec4899;
  font-weight: bold;
}

.slide-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  text-align: center;
}

.emoji {
  font-size: 100px;
  margin-bottom: 20px;
}

h1 {
  color: #ec4899;
  margin-bottom: 15px;
}

p {
  color: #555;
  line-height: 1.7;
}

.dots {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-bottom: 20px;
}

.dot {
  width: 12px;
  height: 12px;
  background: #d1d5db;
  border-radius: 50%;
}

.dot.active {
  background: #ec4899;
}

.next-btn {
  background: #ec4899;
  color: white;
  border: none;
  padding: 15px;
  border-radius: 15px;
  cursor: pointer;
  font-size: 16px;
}
</style>