import axios from 'axios';
import store from './store/authStore';




const axiosClient = axios.create({
    baseURL: 'https://plascon-web.test/api/login',
    //baseURL: 'https://3c67-154-66-216-195.ngrok.io',
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        "Access-Control-Allow-Origin": "*"
    },
    timeout: 10000,
    withCredentials: true,
});

axiosClient.interceptors.request.use
    ((config) => {
        const token = sessionStorage.getItem('TOKEN');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        else{
            const key= '0a3815347109f86b9336c36e6822a144777390cc872954a373e83250d98588c8';
            const secret= 'c7ba9db154d41844835ef69d1fa10e0da14f7407af65651abfdad74f8195073b';
            config.headers.Authorization = `Basic ${btoa(key + ':' + secret)}`;
        }
        return config;  
    }
);

export default axiosClient;