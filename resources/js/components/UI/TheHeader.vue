<!-- This example requires Tailwind CSS v2.0+ -->
<template>
  <Disclosure as="nav" class="bg-gradient-to-r from-[#002f6c] to-[#005cb4] border-b-4 border-[#e31b23] shadow-lg"
    v-slot="{ open }">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
      <div class="relative flex items-center justify-between h-16">
        <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
          <!-- Mobile menu button-->
          <DisclosureButton
            class="inline-flex items-center justify-center p-2 rounded-md text-blue-100 hover:text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
            <span class="sr-only">Open main menu</span>
            <MenuIcon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
            <XIcon v-else class="block h-6 w-6" aria-hidden="true" />
          </DisclosureButton>
        </div>
        <div class="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">
          <div class="flex-shrink-0 flex items-center">
            <img class="block lg:hidden h-10 w-auto" :src="logo" alt="Pepsi" />
            <img class="hidden lg:block h-10 w-auto" :src="logo" alt="Pepsi" />
          </div>
          <!-- <div class="hidden sm:block sm:ml-6">
            <div class="flex space-x-4">
              <a v-for="item in navigation" :key="item.name" :href="item.href" :class="[item.current ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white', 'px-3 py-2 rounded-md text-sm font-medium']" :aria-current="item.current ? 'page' : undefined">{{ item.name }}</a>
            </div>
          </div> -->
        </div>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
          <input type="text" v-model="search" v-on:keyup.enter="submit()"
            class="bg-blue-950/80 border border-blue-400/30 text-white placeholder-blue-300 px-3 py-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-red-500 w-72"
            placeholder="Search with Code or phone number..." />

          <!-- <button type="button" class="bg-gray-800 p-1 rounded-full text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
            <span class="sr-only">View notifications</span>
            <BellIcon class="h-6 w-6" aria-hidden="true" />
          </button> -->

          <!-- Profile dropdown -->
          <Menu as="div" class="ml-3 relative">
            <div>
              <MenuButton
                class="bg-blue-800 flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-900 focus:ring-white">
                <span class="sr-only">Open user menu</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none"
                  stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5.52 19c.64-2.2 1.84-3 3.22-3h6.52c1.38 0 2.58.8 3.22 3" />
                  <circle cx="12" cy="10" r="3" />
                  <circle cx="12" cy="12" r="10" />
                </svg>
                <!-- <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" /> -->
              </MenuButton>
            </div>
            <transition enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95">
              <MenuItems
                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                <MenuItem v-slot="{ active }">
                  <router-link to="/profile" :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']">Your
                    Profile</router-link>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <a href="#" @click.prevent="signOut"
                    :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']">Sign out</a>
                </MenuItem>
              </MenuItems>
            </transition>
          </Menu>
        </div>
      </div>
    </div>

    <DisclosurePanel class="sm:hidden">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <DisclosureButton v-for="item in navigation" :key="item.name" as="a" :href="item.href"
          :class="[item.current ? 'bg-blue-900 text-white' : 'text-blue-100 hover:bg-blue-800 hover:text-white', 'block px-3 py-2 rounded-md text-base font-medium']"
          :aria-current="item.current ? 'page' : undefined">{{ item.name }}</DisclosureButton>
      </div>
    </DisclosurePanel>
  </Disclosure>

  <!-- <div class="hidden sm:block flex bg-white py-3">
      <div class="container  mx-auto">
          <div class="flex justify-between">
            <div>
              <router-link v-for="item in navigation" :key="item.name" :to="item.to"  :class="[item.current ? 'bg-orange-500 text-white' : 'text-gray-500 hover:text-gray-700', 'px-2 py-1 rounded-full mr-1 text-sm font-normal']" :aria-current="item.current ? 'page' : undefined">
                {{ item.name }}
                
              </router-link>
            </div>
            
          </div>
        
      </div>

    </div> -->
  <div class="hidden md:flex bg-white items-center shadow-sm border-b border-gray-100">
    <div class="py-1 px-4 mx-auto container">
      <div class="flex items-center">
        <ul class="flex flex-row mt-0 mr-6 py-2 space-x-8 text-sm font-medium">
          <li v-for="(menu, index) in navigation" :key="index" class="dropdown inline-block relative">
            <router-link :to="menu.to"
              class="text-slate-600 hover:text-[#005cb4] hover:bg-blue-50/50 px-3 py-2 rounded-lg font-semibold inline-flex items-center transition-colors duration-150"
              active-class="text-[#005cb4] bg-blue-50/80">
              <span class="mr-1">{{ menu.name }}</span>
              <span class="mr-1" v-if="menu.subMenu"> <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M7 10l5 5 5-5" />
                </svg></span>
            </router-link>
            <div v-if="menu.subMenu" class="dropdown-menu absolute z-10 hidden left-0 top-full pt-1.5 w-44">
              <ul class="text-slate-700 bg-white border border-gray-150 rounded-xl shadow-xl overflow-hidden">
                <li class="" v-for="(sub, index) in menu.subMenu" :key="index">
                  <router-link
                    class="hover:bg-blue-50 hover:text-[#005cb4] py-2 px-4 block whitespace-nowrap transition-colors duration-150"
                    :to="sub.to">{{ sub.name }}</router-link>
                </li>
              </ul>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { BellIcon, MenuIcon, XIcon } from '@heroicons/vue/outline'
import { useAuthStore } from '@/store/authStore';
import { ref } from 'vue'
import router from '@/routes'


const search = ref('')




const store = useAuthStore();

let logo = "/images/logo.png";

const navigation = [
  { name: "Dashboard", to: "/dashboard", current: false },
  {
    name: "Promo Codes", to: "/codes", current: false,
    subMenu: [
      { name: "Mirinda Fruity", to: "/brand/Mirinda-Fruity", current: false },
      { name: "Mirinda Green Apple", to: "/brand/Mirinda-Green-Apple", current: false },
      { name: "Mirinda Orange", to: "/brand/Mirinda-Orange", current: false },
      { name: "Mirinda Pineapple", to: "/brand/Mirinda-Pineapple", current: false },
      { name: "All", to: "/codes", current: false },
    ]
  },
  { name: "Used Codes", to: "/codes/used", current: false },
  { name: "Messages", to: "/messages", current: false },
  { name: "Airtime", to: "/airtime", current: false },
  // { name: "Past Winners", to: "/past-winners", current: false },
  // { name: "Blacklisted", to: "/blacklisted", current: false },
  { name: "Graph", to: "/graph", current: false },
  { name: "Reports", to: "/reports", current: false },
];

function submit() {

  router.push({ name: 'code-search', params: { search: search.value } })

}

function signOut() {
  store.logout();
}
</script>
<style scoped>
/* Menu Dropdown Menu */
.dropdown:hover .dropdown-menu {
  display: block;
}
</style>
