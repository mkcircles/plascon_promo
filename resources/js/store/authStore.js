import { defineStore } from 'pinia'
import router from '../routes';


export const useAuthStore = defineStore('auth', {
    state: () => {
        return {
            isAuth: localStorage.getItem('isAuth') || false,
            user: localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null,
            token: localStorage.getItem('token') || null,
        }
      },
      getters: {
        getisAuth: state => state.isAuth,
        getUser: state => state.user,
        getToken: state => state.token,
        },
    
    actions: {
        login(payload) {
            return new Promise((resolve, reject) => {
                const user = { "email": payload.username, "password": payload.password };
                axios.post('/api/login', user)
                    .then(response => {
                        this.$state.isAuth = true;
                        this.$state.user = response.data.user;
                        this.$state.token = response.data.token;
                        localStorage.setItem('isAuth', true);
                        localStorage.setItem('user', JSON.stringify(response.data.user));
                        localStorage.setItem('token', response.data.token);
                        resolve(response);
                    }).catch(error => {
                        reject(error);
                    });
            });
        },
        logout() {
            this.$state.isAuth = false;
            this.$state.user = null;
            this.$state.token = null;
            localStorage.removeItem('isAuth');
            localStorage.removeItem('user');
            localStorage.removeItem('token');
            router.push('/login');
        }

    },
    
  })