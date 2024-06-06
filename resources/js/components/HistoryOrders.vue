<script>
//import Vue from 'vue';
import axios from "axios";
//import OrderMake from "@/components/OrderMake.vue";
import EditOrder from "./EditOrder.vue";
//import TextareaAutosize from "vue-textarea-autosize";
//import VueTextareaAutosizeEsm from "vue-textarea-autosize";

//import Autosize from "autosize/dist/autosize.js";
//Vue.use(VueTextareaAutosize)
export default {
  components: {
    EditOrder,
    //Autosize,
    //TextareaAutosize,
    //OrderMake
    //
  },
  data(){
    return {
      orders: [],
      clients: [],
      specializations: [],
      services:[],
      materials: [],

      selectedOrder: '',

      orderDetails: '',

      isOrderOpened: false,
      isOrdersListVisible: true,
      isEditOrderDivVisible: false,
    }
  },

    watch: {
        'selectedOrder.materials': function(newVal) {
            this.$nextTick(() => {
                const materialsTextArea = document.getElementById('materialsTextArea');
                if (materialsTextArea) this.autoResize({ target: materialsTextArea });
            });
        },
        'selectedOrder.comments': function(newVal) {
            this.$nextTick(() => {
                const commentsTextArea = document.getElementById('commentsTextArea');
                if (commentsTextArea) this.autoResize({ target: commentsTextArea });
            });
        }
    },

  methods: {

      autoResize(event){
          const textarea = event.target;
          textarea.style.height = 'auto';
          textarea.style.height = textarea.scrollHeight + 'px';
      },

    formatDate(dateTime){
      const options = { year: 'numeric', month: 'long', day: 'numeric'}
      return new Date(dateTime).toLocaleString('ru-RU', options)
    },

    loadAllOrders(){
      axios.get('http://localhost:8000/get_all_orders')
          .then(response => {
            this.orders = response.data
            console.log(response.data)
          })
          .catch(err => {
            console.error(err.message)
          })
    },

    loadOrderByUser(){
      axios.get(this.$Url + '/get_orders_by_user')
          .then(response => {
              this.orders = response.data
              this.orders.reverse()
              console.log(response.data)
          })
          .catch(err => {
              console.error(err.message)
          })
    },

    loadMaterialsByOrder(){
      axios.get(this.$Url + `/api/get_materials_by_order/${this.selectedOrder.id}`)
          .then(response => {
              this.materials = response.data
              console.log("запрашиваем материалы ордера с id: ", this.selectedOrder.id)
              console.log(this.materials)
          })
          .catch(err => {
              console.error(err.message)
          })
    },


    loadAllClients(){
      axios.get('http://localhost:8000/api/get_all_clients')
          .then(response => {
            this.clients = response.data
          })
          .catch(err => {
            console.error(err.message)
          })
    },
    loadSpecializations() {
      axios.get('http://localhost:8000/api/get_all_specializations')
          .then(response => {
            this.specializations = response.data
          })
    },

    showOrder(orderId){
      this.selectedOrder = this.orders.find(order => order.id === orderId)
      console.log('открытие ордера с id: ', orderId)
      this.isOrderOpened = true
      this.isOrdersListVisible = false
      axios.get(this.$Url + `/api/get_services/${orderId}`)
          .then(response => {
            this.services = response.data
          }).catch(err => {
            console.error(err.message)
      })
      this.loadMaterialsByOrder(orderId)
    },
    closeOrderDetailsDiv(){
      this.isOrderOpened = false
      this.isOrdersListVisible = true
    },
    openEditOrder(){
      this.isOrderOpened = false
      this.isEditOrderDivVisible = true
    },
    closeEditOrderDiv(){
      this.isOrderOpened = true
      this.isEditOrderDivVisible = false
    },
    deleteOrder(){
      axios.delete(this.$Url + `/api/delete_order/${this.selectedOrder.id}`)
          .then(response => {
            console.log(response.data)
          })
      location.reload()
    },

    getSpecializationName(specializationId){
      const specialization = this.specializations.find((specialization) => specialization.id === specializationId)
      return specialization ? specialization.specializationName : 'неизвестная специализация'
    },
    getClientName(clientId){
      const client = this.clients.find((client) => client.id === clientId)
      return client ? client.name : 'неизвестный клиент'
    },
    getOrderDetails(){
      //
    },
  },

  filters: {
    formatData(datetime){
      const options = { year: 'numeric', month: 'long', day: 'numeric',}
      return new Date(datetime).toLocaleString('en-US', options)
    }
  },

  mounted() {
    this.loadOrderByUser()

    //события для ресайза текстогого поля
    document.querySelectorAll('textarea').forEach((element) => {
        element.addEventListener('input', this.autoResize);
    });
  },

  computed: {
      totalServicePrice(){
          let sum = 0;
          for (let service of this.services){
              sum += parseFloat(service.price)
          }
          return sum;
      },

      totalMaterialPrice(){
         console.log("материалы: ", this.materials)
         let sum = 0;
         console.log()
         for (let material of this.materials){
             sum += material.price * material.amount
         }
         return sum
      }

  }
}
</script>

<template>
<div>
  <div id="openOrderDiv" v-if="isOrderOpened">
    <div >
      <div>
        <div class="col-md-4">
          номер заказ-наряда:
          {{ this.selectedOrder.user_order_number }}
        </div>
        <div class="col-md-4">
          клиент:
          {{ this.selectedOrder.client_name }}
        </div>
        <div class="col-md-4">
          специализация:
          {{ this.selectedOrder.specialization_name }}
        </div>
        <div class="col-md-4">
          дата:
          {{ formatDate(this.selectedOrder.created_at) }}
        </div>
          <br>

      </div>

    </div>

    <div class="container">
      <div>

        <div id="serviceLabel" class="d-flex justify-content-center">Работа: </div>

        <div id="serviceItem" v-for="service in services" :key="service.id">
          <div class="d-flex justify-content-between align-items-center">
            <div>{{ service.service }}</div>
            <div>{{ service.price }}</div>
          </div>
        </div>
        <div id="serviceTotalSum"> итого по работе: {{totalServicePrice}}</div>

          <br>
          <div id="materialsLabel" class="d-flex justify-content-center">Материалы: </div>

          <div v-for="material in materials" >
              <div class="d-flex justify-content-between align-items-center">
                  <div> {{material.name}} </div>
                  <div class="d-flex align-items-center">
                      <div id="price">{{material.price}}р</div>
                      <div id="counter">X{{material.amount}}=</div>
                      <div id="total">  {{material.price * material.amount}}р</div>
<!--                      <button class="btn btn-danger" @click="deleteMaterial(material.id)"> - </button>-->
                  </div>
              </div>
          </div>
          <div id="materialTotalSum">итого по материалам: {{totalMaterialPrice}}</div>
          <div id="totalSum"> Всего к оплате: {{totalMaterialPrice + totalServicePrice}}</div>

      </div>
    </div>

    <br>
    <div>

      <br>

      <textarea id="commentsTextArea"
                       v-model="this.selectedOrder.comments"
                       placeholder="нет комментарии"
                       readonly
                       class="form-control form-control-sm"
      ></textarea>
    </div>

    <div class="fixed-bottom">
      <div class="justify-content-end">
        <div class="md auto">
          <button class="btn btn-danger" @click="deleteOrder">удалить</button>
          <button class="btn btn-secondary" @click="closeOrderDetailsDiv">закрыть</button>
          <button class="btn btn-primary" @click="openEditOrder">редактировать</button>
        </div>
      </div>
    </div>
  </div>

  <div id="ordersDiv" v-if="isOrdersListVisible">
    <div>
      <div id="orderItem" v-for="order in orders" :key="order.id" @click="showOrder(order.id)">
        <div class="d-flex justify-content-between align-items-center">
          <div> {{ formatDate(order.created_at) }}</div>
          <div> {{ order.specialization_name }} | "{{ order.client_name }}" </div>
          <div>{{ order.total_amount }}</div>
        </div>
      </div>
    </div>
  </div>

  <div id="editOrderDiv" v-if="isEditOrderDivVisible">
    редактор ордера

    <EditOrder :orderToEdit="selectedOrder" :already-added-services="services"/>

    <button class="btn btn-danger fixed-bottom mb-3 col-3 mt-4" @click="closeEditOrderDiv">отмена</button>
  </div>

</div>
</template>

<style>

#orderItem:hover {
  background-color: #6c757d;
  color: white;
  cursor: pointer;
}

#orderItem:active {
  background-color: red;
  color: white;
}

textarea {
    width: 100%;
}

</style>
