<template>
    <div class="min-h-screen w-full flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat relative overflow-hidden font-sans" :style="{ backgroundImage: `url('/images/pepsi_background.png')` }">
        <!-- Subtle gradient overlay for extra depth and contrast -->
        <div class="absolute inset-0 bg-gradient-to-tr from-blue-900/30 via-slate-900/10 to-red-900/20 pointer-events-none"></div>
        
        <!-- Subtle floating bubbles for carbonation effect -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="bubble bubble-1"></div>
            <div class="bubble bubble-2"></div>
            <div class="bubble bubble-3"></div>
            <div class="bubble bubble-4"></div>
        </div>
        
        <div class="w-full max-w-md relative z-10">
            <router-view></router-view>
        </div>
    </div>
</template>

<style scoped>
.bubble {
    position: absolute;
    bottom: -100px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
    animation: rise 15s infinite linear;
}
.bubble-1 {
    width: 30px;
    height: 30px;
    left: 15%;
    animation-duration: 14s;
    animation-delay: 0s;
}
.bubble-2 {
    width: 50px;
    height: 50px;
    left: 80%;
    animation-duration: 18s;
    animation-delay: 2s;
}
.bubble-3 {
    width: 20px;
    height: 20px;
    left: 45%;
    animation-duration: 12s;
    animation-delay: 4s;
}
.bubble-4 {
    width: 40px;
    height: 40px;
    left: 60%;
    animation-duration: 16s;
    animation-delay: 7s;
}

@keyframes rise {
    0% {
        transform: translateY(0) scale(1);
        opacity: 0;
    }
    10% {
        opacity: 0.5;
    }
    90% {
        opacity: 0.5;
    }
    100% {
        transform: translateY(-120vh) scale(1.3);
        opacity: 0;
    }
}
</style>
