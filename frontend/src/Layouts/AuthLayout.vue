<template>
  <div class="min-h-screen w-full grid grid-cols-1 md:grid-cols-2 font-sans overflow-hidden bg-[#FBF9FE]">
    
    <!-- Partie Gauche : Vitrine & Features (Invisible sur mobile) -->
    <div class="relative hidden md:flex flex-col justify-between p-16 bg-gradient-to-br from-[#1E1B4B] via-[#2E1065] to-[#3B0764] text-white overflow-hidden select-none">
      
      <!-- Éléments de décor lumineux en arrière-plan (Glow effect) -->
      <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] bg-[#C566FF]/20 rounded-full blur-[120px] pointer-events-none"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-[#FF65B2]/15 rounded-full blur-[100px] pointer-events-none"></div>

      <!-- Marque / Logo -->
      <div class="relative z-10 flex flex-col gap-4 max-w-md animate-fade-in">
        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center shadow-lg">
          <!-- Remplacement de Tabler Icons par un SVG inline propre et moderne -->
          <svg class="w-7 h-7 text-[#FF65B2]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
          </svg>
        </div>
        
        <div>
          <h1 class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-white via-white to-purple-200 bg-clip-text text-transparent">
            GlowChat
          </h1>
          <p class="mt-2 text-base text-purple-200/80 leading-relaxed font-medium">
            Connectez-vous avec ceux qui comptent dans un espace sécurisé et moderne.
          </p>
        </div>
      </div>

      <!-- Liste des fonctionnalités clés -->
      <div class="relative z-10 flex flex-col gap-6 max-w-sm mb-12 animate-fade-in" style="animation-delay: 150ms;">
        
        <!-- Feature 1 -->
        <div class="flex items-center gap-4 group">
          <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-purple-300 group-hover:bg-[#C566FF]/20 group-hover:text-white transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
            </svg>
          </div>
          <span class="text-[15px] font-semibold text-purple-100/90 tracking-wide">Messages en temps réel</span>
        </div>

        <!-- Feature 2 -->
        <div class="flex items-center gap-4 group">
          <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-purple-300 group-hover:bg-[#C566FF]/20 group-hover:text-white transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
          </div>
          <span class="text-[15px] font-semibold text-purple-100/90 tracking-wide">Appels audio & vidéo HD</span>
        </div>

        <!-- Feature 3 -->
        <div class="flex items-center gap-4 group">
          <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-purple-300 group-hover:bg-[#C566FF]/20 group-hover:text-white transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1333 9-8.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286z" />
            </svg>
          </div>
          <span class="text-[15px] font-semibold text-purple-100/90 tracking-wide">Chiffrement de bout en bout</span>
        </div>

      </div>

      <!-- Optionnel : Un petit copyright discret en bas -->
      <div class="relative z-10 text-xs text-purple-300/50 font-medium tracking-wider">
        &copy; 2026 GlowChat. Tous droits réservés.
      </div>
    </div>

    <!-- Partie Droite : Conteneur dynamique (RouterView) -->
    <div class="relative flex items-center justify-center p-6 sm:p-12 md:p-16 bg-gradient-to-br from-[#FFF5F8] via-[#F4EDFC] to-[#EBE9FC] overflow-y-auto">
      
      <!-- On s'assure que le contenu injecté par le Router prend toute la largeur nécessaire sans casser la mise en page -->
      <div class="w-full max-w-[440px] flex flex-col justify-center min-h-full py-8">
        <RouterView />
      </div>

    </div>

  </div>
</template>

<script setup>
// Pas besoin de logique complexe ici, le layout gère la structure.
</script>

<style scoped>
/* Petit effet d'entrée propre pour le texte à gauche */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>