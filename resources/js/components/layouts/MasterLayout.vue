<template>
    <TheHeader/>
    
    <div class="min-h-screen">
       
            <router-view></router-view>
               
    </div>
    
    <TheFooter/>

</template>
<script setup>
    import TheHeader from '../UI/TheHeader.vue';
    import TheFooter from '../UI/TheFooter.vue';

</script>
