/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
window.Vue = require('vue');

import Vue from "vue";
import './extensions.js';
import commonPlugin from './CommonPlugin.js';
import reactiveStorage from "vue-reactive-storage";
import VueLazyload from 'vue-lazyload';
Vue.use(reactiveStorage, {
    "count": localStorage.count,
});
Vue.use(VueLazyload,{
    error:'images/error.jpg',
    listenEvents:['scroll', 'wheel', 'mousewheel', 'resize', 'animationend', 'transitionend', 'touchmove','click']
});
Vue.prototype.$course = window.course;

Vue.component('modal', require('./components/UI/panels/modal.vue').default);
Vue.component('orders', require('./components/orders/orders.vue').default);
Vue.component('article-editor', require('./components/article-editor/article-editor.vue').default);
Vue.component('chat',require('./components/chat/chat.vue').default);
Vue.component('zones-editor',require("./components/zonesEditor/zones-editor.vue").default);
Vue.component('zone-list-item',require("./components/zonesEditor/zone-list-item.vue").default);
Vue.component('zones-editor-page',require("./components/zonesEditor/zones-editor-page.vue").default);
Vue.component('warehouse',require('./components/warehouse/warehouse.vue').default);
Vue.component('warehousepacks',require('./components/warehouse/packs/warehousepacks.vue').default);
Vue.component('v-html',require("./components/UI/mini/v-html.js").default);
Vue.component('v-style',require("./components/UI/mini/v-style.js").default);
Vue.component('group-box', require("./components/UI/mini/group-box.vue").default);
Vue.component('async-button', require("./components/UI/mini/async-button.vue").default);
Vue.component('circle-loading', require("./components/UI/mini/circle-loading.vue").default);
Vue.component('error-icon', require("./components/UI/mini/error-icon.vue").default);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
var url = "http://ishop.loc/api/";
window.onload = function () {
    Vue.use(commonPlugin);
    let main = new Vue({
        data(){
            let currencyEnabled = this.$course>0;
            return{
                currencyEnabled, UAH:localStorage.UAH=="true" && currencyEnabled,
            }
        },
        watch:{
            UAH(){
                window.dispatchEvent(new CustomEvent("currency-switch",{ detail:{ UAH:this.UAH } }));
                localStorage.UAH = this.UAH;
            },
        }
    });
    Vue.filter('currencydecimal', function (value, recalculateCurrency = false, fractionsDigits = 2) {
        if (!value) return "";
        let val = parseFloat(value);
        if(val === NaN) return value;
        value = val;
        let course = main.UAH && recalculateCurrency?Vue.prototype.$course:1;
        return (main.UAH?Math.ceil(value/course):value).toFixed(fractionsDigits) + `\u00A0${main.UAH?'₴':'₽'}`;
    });
    main.$mount("#goods");
    
}
