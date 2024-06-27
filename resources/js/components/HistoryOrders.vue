<script>
//import Vue from 'vue';
import axios from "axios";
//import OrderMake from "@/components/OrderMake.vue";
import EditOrder from "./EditOrder.vue";
import {red} from "vuetify/util/colors";
import DeleteClientModal from "./ModalWindows/DeleteClientModal.vue";
import DeleteOrderModal from "./ModalWindows/DeleteOrderModal.vue";
import VSelect from "vue3-select";
import {th} from "vuetify/locale";
//import TextareaAutosize from "vue-textarea-autosize";
//import VueTextareaAutosizeEsm from "vue-textarea-autosize";

//import Autosize from "autosize/dist/autosize.js";
//Vue.use(VueTextareaAutosize)
export default {
  components: {
      DeleteClientModal,
      DeleteOrderModal,
    EditOrder,
      VSelect,
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
      paidStatus: '',

        statusOptions: [
            {name: 'выполнено', value: 'done', color: 'green'},
            {name: 'в работе', value: 'process', color: 'red'},
            {name: 'в ожидании', value: 'waiting', color: 'orange'},
        ],

      statusTranslations: {
        done: 'Выполнено',
        waiting: 'Ожидание',
        process: 'В процессе'
      },

      selectedOrder: '',
      selectedStatus: '',

      orderDetails: '',

      isOrderOpened: false,
      isOrdersListVisible: true,
      isEditOrderDivVisible: false,
    }
  },

    watch: {
      selectedStatus(newStatus){
        this.updateOrderStatus(newStatus.value)
      },

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

      switchPaidStatus(){
        axios.put(this.$Url + `/api/switch_paid_status/${this.selectedOrder.id}`)
        .then((response) => {
            console.log(response)
        })
            .catch(err => {
                console.log('ошибка изменения статуса',err)
            })
      },

      updatePaidStatus(){
        this.paidStatus = !this.paidStatus
        let paid = this.paidStatus
        axios.put(this.$Url + `/api/update_paid_status/${this.selectedOrder.id}`, {paid})
            .then(response => {
                console.log('статус обновлен', response.data)
            })
            .catch(err => {
                console.error('ошибка обновления статуса')
            })
      },

      updateOrderStatus(status){
        console.log("отправляем статус: ",this.paidStatus, "ордера: ", this.selectedOrder.id)
        axios.put(this.$Url + `/api/update_order_status/${this.selectedOrder.id}`, {status})
            .then(response => {
                console.log('статус обновлен.', response.data)
            })
            .catch(err => {
                console.error('Error updating status: ', err)
            })
      },

      handleOrderDeleted(){
          //this.showAlert('success', 'ордер удален')
          this.closeOrderDetailsDiv()
          location.reload()
      },

      translateStatus(status) {
          return this.statusTranslations[status] || status
      },

      getStatusColors(status){
          const colors = {
              done: 'green',
              waiting: 'orange',
              process: 'red'
          }
          return colors[status] || 'black'
      },

      autoResize(event){
          const textarea = event.target;
          textarea.style.height = 'auto';
          textarea.style.height = textarea.scrollHeight + 'px';
      },

    formatDate(dateTime){
      const options = { year: '2-digit', month: '2-digit', day: '2-digit'}
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
      //this.selectedStatus = this.selectedOrder.status
      if(this.selectedOrder.status) {
          this.selectedStatus = this.translateStatus(this.selectedOrder.status)
      }
      //this.getStatusColors(this.selectedStatus)
      console.log("selectedStatus", this.selectedStatus)

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
      console.log("статус оплаты: ", this.selectedOrder.paid)
      this.paidStatus = this.selectedStatus.paid
    },
    closeOrderDetailsDiv(){
      this.isOrderOpened = false
      this.isOrdersListVisible = true
      location.reload()
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
        console.log("Удаляем ордер с id: ", this.selectedOrder)
        this.$refs.deleteOrderModal.open(this.selectedOrder)
        // axios.delete(this.$Url + `/api/delete_order/${this.selectedOrder.id}`)
      //     .then(response => {
      //       console.log(response.data)
      //     })
      // location.reload()
    },

      openDeleteOrderModal(orderId){
          this.$refs.deleteOrderModal.open(orderId)
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
      red() {
          return red
      },

      orderStatusColor(){
        switch (this.order.status) {
            case 'done': return 'green'
            case 'process': return 'orange'
        }
      },

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
<!--        <div class="col-md-4" :style="{ color: getStatusColors(selectedOrder.status) }">-->
<!--            {{ translateStatus(selectedOrder.status) }}-->
<!--        </div>-->

          <VSelect :options="statusOptions" v-model="selectedStatus"
                   label="name"
                   :searchable="false"
                   :clearable="false"
                   placeholder="без статуса"
          >
              <template #selected-option="option">
                  <div :style="{color: selectedStatus.color}">
                      {{option.name}}
                  </div>
              </template>
          </VSelect>

          <div class="form-check form-switch">
              <input class="form-check-input"
                     type="checkbox"
                     role="switch"
                     id="flexSwitchCheckChecked"
                     v-model="selectedOrder.paid"
                     @change="switchPaidStatus"
              >
              <label class="form-check-label" for="flexSwitchCheckChecked">
                  {{ selectedOrder.paid ? 'Оплачено' : 'Не оплачено' }}
              </label>
          </div>

        <div class="col-md-4">
          номер заказ-наряда:
          {{ this.selectedOrder.user_order_number }}
        </div>
        <div class="col-md-4">
          клиент:
          {{ this.selectedOrder.client_name }} Телефон: {{this.selectedOrder.client_phone}}
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
          <button class="btn btn-danger"
                  v-on:click="openDeleteOrderModal(this.selectedOrder.id)"
                  data-bs-target="#deleteOrderModal"
                  data-bs-toggle="modal"
          >
              удалить
          </button>
          <button class="btn btn-secondary" @click="closeOrderDetailsDiv">закрыть</button>
          <button class="btn btn-primary" @click="openEditOrder">редактировать</button>
        </div>
      </div>
    </div>
  </div>

  <div id="ordersDiv" v-if="isOrdersListVisible">
    <div>
      <div id="orderItem"
           v-for="order in orders"
           :key="order.id"
           @click="showOrder(order.id)"
      >
          <div class="d-flex justify-content-between align-items-center"
               :style="{ width: '100%', color: getStatusColors(order.status)}"
          >
              <div style="width: 30%;">{{ formatDate(order.created_at) }} | №{{order.user_order_number}}</div>
              <div style="width: 30%;">{{ order.specialization_name }}</div>
              <div style="width: 30%;">{{ order.client_name }}</div>
<!--              <div style="width: 10%;">-->
<!--                  <span style="color: green;">✔</span>-->
<!--              </div>-->
              <div style="width: 10%; text-align: right"> <span v-if="order.paid" style="color: green;">✔</span>
                  {{ order.total_amount }}</div>
          </div>
      </div>
    </div>
  </div>

    <DeleteOrderModal ref="deleteOrderModal"
                      @order-deleted="handleOrderDeleted"
    />

  <div id="editOrderDiv" v-if="isEditOrderDivVisible">

    <EditOrder :orderToEdit="selectedOrder" :already-added-services="services"/>


                  <div class="fixed-bottom col-6 mx-0"> <!-- Используем mx-auto для центрирования блока -->
                      <button class="btn btn-danger w-100" @click="closeEditOrderDiv">отмена</button>
                  </div>


  </div>

</div>
</template>

<style>

#orderItem:hover {
  color: white;
  cursor: pointer;
  transition: border 0.5s ease;
  border: 1px solid rgba(0, 0, 0, 1);

}

#orderItem:active {
  background-color: red;
  color: white;
}

textarea {
    width: 100%;
}

#orderItem {
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 1px;
    padding: 1px; /* Optional for spacing between border and content */
}

</style>
