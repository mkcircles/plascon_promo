<template>
    <div class="min-h-screen w-full flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat relative overflow-hidden font-sans" :style="{ backgroundImage: `url('/images/plascon_background.png')` }">
        <!-- Rich overlay for paint color depth -->
        <div class="absolute inset-0 bg-gradient-to-tr from-slate-950/75 via-blue-950/60 to-red-950/70 pointer-events-none backdrop-blur-[2px]"></div>
        
        <!-- Ambient subtle glow pulses -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="glow-orb orb-1"></div>
            <div class="glow-orb orb-2"></div>
        </div>
        
        <div class="w-full max-w-md relative z-10">
            <router-view></router-view>
        </div>
    </div>
</template>

<style scoped>
.glow-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.35;
    animation: float 10s infinite ease-in-out alternate;
}
.orb-1 {
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, #e31b23 0%, rgba(227, 27, 35, 0) 70%);
    top: 10%;
    left: 15%;
}
.orb-2 {
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, #005cb4 0%, rgba(0, 92, 180, 0) 70%);
    bottom: 10%;
    right: 15%;
    animation-delay: -5s;
}

@keyframes float {
    0% {
        transform: translateY(0) scale(1);
    }
    100% {
        transform: translateY(-30px) scale(1.1);
    }
}
</style>
