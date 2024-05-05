/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

import ExampleComponent from './components/ExampleComponent.vue';
import StatisticComponent from "./components/StatisticComponent.vue";
import OrderMake from "./components/OrderMake.vue";
import TestModal from "./components/ModalWindows/TestModal.vue";
import EditBase from "./components/EditBase.vue";
import HistoryOrders from "./components/HistoryOrders.vue";
import EditOrder from "./components/EditOrder.vue";

app.component('example-component', ExampleComponent);
app.component('statistic-component', StatisticComponent);
app.component('order-make', OrderMake);
app.component('edit-base' , EditBase)
app.component('test-order', TestModal)
app.component('history-orders', HistoryOrders)
app.component('edit-order', EditOrder)
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount('#app');
