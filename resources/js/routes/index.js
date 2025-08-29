import { createRouter, createWebHistory } from "vue-router";


//Layout
import AuthLayout from "@/components/layouts/AuthLayout.vue";
import MasterLayout from "@/components/layouts/MasterLayout.vue";

//Auth
import Login from "@/pages/Auth/Login.vue";

//Master
import Dashboard from "@/pages/master/Dashboard.vue";
import PromoCodes from "@/pages/master/PromoCodes.vue";
import AreaCodes from "@/pages/master/AreaCodes.vue";
import UsedCodes from "@/pages/master/UsedCodes.vue";
import InMessages from "@/pages/master/InMessages.vue";
import Airtime from "@/pages/master/Airtime.vue";
import PastWinners from "@/pages/master/PastWinners.vue";
import Blacklisted from "@/pages/master/Blacklisted.vue";
import CodeSearch from "@/pages/master/CodeSearch.vue";
import Graph from "@/pages/master/GraphView.vue";
import AreaView from "@/pages/master/AreaView.vue";

const routes = [
    {
        path: "/",
        redirect: "/login",
        component: AuthLayout,
        meta: { isGuest: true },
        children: [
            { path: "/", name: "home",  component: Login },
            { path: "/login", component: Login },
        ]
    },
    {
        path: "/user",
        component: MasterLayout,
        meta: { isAuth: true },
        children: [
            { path: "/dashboard", name:"dashboard", component: Dashboard },
            { path: "/codes", name:"codes", component: PromoCodes },
            { path: "/area/:area", name:"area-codes", component: AreaCodes },
            { path: "/messages/search/:search", name:"code-search", component: CodeSearch },
            { path: "/codes/used", name:"usedcodes", component: UsedCodes },
            { path: "/messages", name:"messages", component: InMessages },
            { path: "/airtime", name:"airtime", component: Airtime },
            { path: "/past-winners", name:"past-winner", component: PastWinners },
            { path: "/blacklisted", name:"blacklisted", component: Blacklisted },
            { path: "/graph", name:"graph", component: Graph },
            { path: "/area-chart", name:"area-chart", component: AreaView },
        ],
    },


];

const router = createRouter({
    history: createWebHistory(),
    routes
  });

  export default router;