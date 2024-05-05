<!--<script xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">-->

<script>
import axios from "axios"
//import {BIconTrash} from 'bootstrap-vue'
//import {BAlert} from "bootstrap-vue";
import alert from "bootstrap/js/src/alert";
import NewServiceModal from "./ModalWindows/NewServiceModal.vue";
import NewSpecializationModal from "./ModalWindows/NewSpecializationModal.vue";
import NewClientModal from "./ModalWindows/NewClientModal.vue";
import NewCategoryModal from "./ModalWindows/NewCategoryModal.vue";
export default {
  props: ['orderToEdit' , 'alreadyAddedServices'],
  components: {
    NewClientModal,
    NewSpecializationModal,
    NewServiceModal,
    NewCategoryModal,
    //BIconTrash,
    //BAlert,
  },

  data(){
    return {
      specializations: [],
      clients: [],
      categories: [],
      services: [],
      addedServices: [],

      selectedSpecialization: null,
      selectedClient: null,
      selectedCategory: null,
      materials: null,
      comments: null,

      alertVisible: false,
      alertVariant: 'success',
      alertMessage: '',

      isNewServiceModalOpen: false,
    }
  },

  computed: {

    tabTitleCounter() {
      return `добавлено услуг: ${this.addedServices.length}`
    },

    totalAddedServicesPrice(){
      return this.addedServices.reduce((total, service) => total + service.price, 0)
    },

  },

  methods: {

    handleSpecializationChange(){
      console.log('handleSpecializationChange')
      if (this.selectedSpecialization === 'create_new_specialization'){
        this.openNewSpecializationModal()
      } else {
        this.loadClients()
        this.loadCategories()
      }
      console.log('выбрана специализация: ', this.selectedSpecialization)
    },

    handleClientChange(){
      //this.selectedClient = this.orderToEdit.
      console.log('selectedClient!!!', this.selectedClient)
      console.log('handleClientChange')
      if (this.selectedClient === 'create-new-client'){
        console.log('открываем модальное окно нового клиента')
        this.openNewClientModal()
      } else {
        console.log('selectedClient!!!!', this.selectedClient)
      }
    },

    handleCategoriesChange(){
      console.log('выбираем категорию')
      if (this.selectedCategory === 'create_new_category'){
        console.log('создаем новую категорию')
        this.openNewCategoryModal()
      } else {
        this.loadServicesByCategory()
      }
      console.log('выбрана категория: ', this.selectedCategory)
    },

    loadCategories(){
      axios.get(`http://localhost:8000/api/get_categories/${this.selectedSpecialization}`)
          .then(response => {
            this.categories = response.data
            console.log('список категорий: ',this.categories)
          })
          .catch(error => {
            console.error('Ошибка загрузки категорий: ', error.message)
          })
    },

    loadClients(){
      console.log('loadClients')
      axios.get(`http://localhost:8000/api/get_clients/${this.selectedSpecialization}`)
          .then(response => {
            this.clients = response.data
            console.log('список клиентов: ', this.clients)
          })
          .catch(error => {
            console.error('Ошибка загрузки клиентов: ', error.message)
          })
    },

    loadServicesByCategory(){
      axios.get(`http://localhost:8000/api/get_service/${this.selectedCategory}`)
          .then(response => {
            this.services = response.data
            console.log('Список услуг: ', this.services)
          })
          .catch(error => {
            console.error('Ошибка загрузки услуг: ', error.message)
          })
    },

    loadSpecializations (){
      axios.get('http://localhost:8000/api/getSpecialization')
          .then(response => {
            this.specializations = response.data
            console.log('список специализаций: ', this.specializations )
          })
          .catch(eError => {
            console.error(eError.message)
          })
    },

    loadServicesByOrder(){
      axios.get(`http://localhost:8000/api/get_services/${this.orderToEdit.id}`)
          .then(response => {
            this.addedServices = response.data
          }).catch(err => {
        console.error(err.message)
      })
    },

    //удаление сервиса из ордера
    deleteFromAdded(serviceId) {
      console.log('удаляем сервис')
      const index = this.addedServices.findIndex((service) => service.id === serviceId)
      if (index !== -1){
        this.addedServices.splice(index, 1);
      }
      console.log(this.addedServices)
    },

    //добавление сервиса в ордер
    addServiceToOrder(service) {
      console.log('Добавление сервиса в ордер: ', service)
      const isServiceAlreadyAdded = this.addedServices.some(addedService => addedService.id === service.id)
      if (!isServiceAlreadyAdded) {
        this.addedServices.push(service)
        console.log('Добавленные сервисы: ', this.addedServices)
      } else {alert('Такой сервис уже есть в ордере!')}
    },

    //Алерт
    showAlert(variant, message) {
      this.alertVariant = variant
      this.alertMessage = message
      this.alertVisible = true
      setTimeout(() => {
        this.alertVisible = false
      }, 2000)
    },

    openNewSpecializationModal(){
      console.log('создаем новую специализацию')
      this.$refs.newSpecializationModal.open()
    },

    openNewClientModal(){
      this.$refs.newClientModal.selectedClient = this.selectedClient
      this.$refs.newClientModal.open()
    },

    openNewCategoryModal(){
      console.log('открываем модальное окно создания категории')
      this.$refs.newCategoryModal.selectesSpecialization = this.selectedSpecialization
      this.$refs.newCategoryModal.open()
    },

    // открытие модального окна для добавления новой услуги
    openNewServiceModal(){
      this.$refs.newServiceModal.selectedCategory = this.selectedCategory
      this.$refs.newServiceModal.open()
    },

    //сохранение ордера
    saveOrder() {
      console.log('создаем ордер')
      const totalAmount = this.totalAddedServicesPrice
      const orderData = {
        id: this.orderToEdit.id,
        client_id: this.selectedClient,
        specialization_id: this.selectedSpecialization,
        total_amount: totalAmount,
        materials: this.materials,
        comments: this.comments,
        services: this.addedServices.map(service => service.id)
      }
      console.log('данные для сохранения: ', orderData)
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      console.log('csrfToken',csrfToken)
      // проверка на пустое поле сервисов
      if (this.addedServices.length === 0) {
        this.showAlert('danger', 'Сначала добавьте сервисы')
      } else if (!this.selectedClient) {
        // проверка на пустое поле выбора клиента
        this.showAlert('danger', 'Сначала выберите клиента')
      } else {
        axios.post('http://localhost:8000/api/update_order', orderData, {
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        })
            .then(response => {
              console.log(response.data.message)

              this.addedServices = []
              this.materials = ''
              this.comments = ''
              this.showAlert('success', 'Ордер успешно сохранен')
            })
            .catch(error => {
              console.error('Ошибка сохранения ордера: ', error)
              this.showAlert('danger', 'Ошибка сохроанения ордера')
            })
      }
      location.reload()
    }
  },

   async mounted() {
    this.addedServices = this.alreadyAddedServices
    this.selectedSpecialization = this.orderToEdit.specialization_id
    this.selectedClient = this.orderToEdit.client_id
    //this.selectedClient = this.orderToEdit.client_id
     await axios.get('http://localhost:8000/api/getSpecialization')
        .then(response => {
          this.specializations = response.data
          console.log('список специализаций: ', this.specializations )
        })
        .catch(eError => {
          console.error(eError.message)
        })
    this.selectedClient = this.orderToEdit.client_id
    console.log('orderToEdit: ', this.orderToEdit)
    await this.loadClients()
    this.loadCategories()
  }
}

</script>

<template>
  <div>
    <div>
      <select v-model="selectedSpecialization" @change="handleSpecializationChange" class="w-auto">
        <option v-for="specialization in specializations"
                              :key="specialization.id" :value="specialization.id" >
          {{ specialization.specializationName }}
        </option>

        <option value="create_new_specialization">
          создать новую специализацию
        </option>
      </select>

      <select v-model="selectedClient" class="w-auto" @change="handleClientChange"  >
        <option v-for="client in clients"
                              :key="client.id" :value="client.id">
          {{client.name}} - {{client.phone}}
        </option>

        <option value="create-new-client" v-if="selectedSpecialization">
          создать нового клиента
        </option>
      </select>
    </div>

      <div>
            <div>
              <select v-model="selectedCategory" @change="handleCategoriesChange" class="w-auto" >
                <option v-for="category in categories"
                                      :key="category.id" :value="category.id">
                  {{category.category_name}}
                </option>

                <option v-if="selectedSpecialization" value="create_new_category">
                  создать новую категорию
                </option>

              </select>
            </div>
            <div>
              <div>
                Сумма: {{ totalAddedServicesPrice }}
              </div>
            </div>
      </div>


    </div>

    <BAlert v-model="alertVisible" :variant="alertVariant" dismissible fade class="fixed-top"  >
      {{ alertMessage }}
    </BAlert>

    <div id="tabsTest">
        <div class="container">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" href="#serviceChoice" role="tab" data-bs-toggle="tab"> ВЫБОР УСЛУГ </a></li>
                <li class="nav-item"><a class="nav-link" href="#addedServices" role="tab" data-bs-toggle="tab"> ДОБАВЛЕННЫХ УСЛУГ: {{this.addedServices.length}} </a></li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane show active" id="serviceChoice">

                <div id="serviceItem" v-for="service in services" :key="service.id" @click="addServiceToOrder(service)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>{{service.service }}</div>
                        <div>{{service.price}}</div>
                    </div>
                </div>
                <div id="serviceItem"
                     class=""
                     data-bs-toggle="modal"
                     data-bs-target="#newServiceModal"
                     v-if="selectedCategory"
                     v-on:click="openNewServiceModal"
                >
                    Добавить новую услугу
                </div>

            </div>
            <div class="tab-pane" id="addedServices">
                <div id="addedServices">
                    <div v-for="service in addedServices" :key="service.id" >
                        <div class="d-flex justify-content-between align-items-center">
                            <div>{{service.service}}</div>
                            <div>{{service.price}}</div>
                            <button class="btn btn-danger" @click="deleteFromAdded(service.id)">
                                -
                                <BIconTrash icon="trash"></BIconTrash>
                            </button>
                        </div>
                    </div>

                    <br>

                    <textarea id="materialsTextArea"
                              v-model="materials"
                              placeholder="нет материалов"
                              rows="1"
                              class="form-control form-control-sm"
                    ></textarea>

                    <br>

                    <textarea id="commentsTextArea"
                              v-model="comments"
                              placeholder="нет комментарии"
                              rows="1"
                              class="form-control form-control-sm"
                    ></textarea>

                </div>

            </div>
        </div>
    </div>


<!--    <div id="orderDiv" class="d-flex flex-column" style="min-height: 75vh">-->
<!--      <b-tabs order switch>-->
<!--        <b-tab title="Выбор услуг" href="#serviceChoice">-->
<!--          <br>-->
<!--          <div id="serviceChoice">-->
<!--            <b-list-group>-->
<!--              <b-list-group-item id="serviceItem" v-for="service in services" :key="service.id" @click="addServiceToOrder(service)">-->
<!--                <div class="d-flex justify-content-between align-items-center">-->
<!--                  <div>{{service.service }}</div>-->
<!--                  <div>{{service.price}}</div>-->
<!--                </div>-->
<!--              </b-list-group-item>-->

<!--              <b-list-group-item v-if="selectedCategory" id="serviceItem" @click="openNewServiceModal">-->
<!--                <div class="d-flex justify-content-center align-items-center">-->
<!--                  добавить новую услугу-->
<!--                </div>-->
<!--              </b-list-group-item>-->

<!--            </b-list-group>-->
<!--          </div>-->
<!--        </b-tab>-->


        <NewServiceModal :selectedCategory="selectedCategory"
                         ref="newServiceModal"
                         @service-added="loadServicesByCategory"
        />

        <NewSpecializationModal ref="newSpecializationModal"
                                @specialization-added="loadSpecializations"
        />

        <NewClientModal :selected-specialization="selectedSpecialization"
                        ref="newClientModal"
                        @client-added="loadClients"
        />

        <NewCategoryModal :selected-specialization="selectedSpecialization"
                          ref="newCategoryModal"
                          @category-added="loadCategories"
        />


<!--        <b-tab :title="tabTitleCounter" href="#addedServices">-->
<!--          <br>-->
<!--          <div id="addedServices">-->
<!--            <b-list-group-item v-for="service in addedServices" :key="service.id" >-->
<!--              <div class="d-flex justify-content-between align-items-center">-->
<!--                <div>{{service.service}}</div>-->
<!--                <div>{{service.price}}</div>-->
<!--                <b-button @click="deleteFromAdded(service.id)" variant="danger">-->
<!--                  <BIconTrash icon="trash"></BIconTrash>-->
<!--                </b-button>-->
<!--              </div>-->
<!--            </b-list-group-item>-->

<!--            <br>-->

<!--            <b-form-textarea id="materialsTextArea"-->
<!--                             v-model="materials"-->
<!--                             placeholder="нет материалов"-->
<!--                             rows="1"-->
<!--                             max-rows="6"-->
<!--            ></b-form-textarea>-->

<!--            <br>-->

<!--            <b-form-textarea id="commentsTextArea"-->
<!--                             v-model="comments"-->
<!--                             placeholder="комментарии"-->
<!--                             rows="1"-->
<!--                             max-rows="6"-->
<!--            ></b-form-textarea>-->

<!--          </div>-->
<!--        </b-tab>-->
<!--      </b-tabs>-->

<!--    </div>-->

    <div class="fixed-bottom">
      <div class="container-fluid">
        <div class="row justify-content-end">
          <button @click="saveOrder()" class="btn btn-primary col-6" >
            сохранить ордер
          </button>
        </div>
      </div>
    </div>


</template>

<style scoped>
#serviceItem:hover {
  background-color: #6c757d;
  color: white;
  cursor: pointer;
}
#serviceItem:active {
  background-color: red;
  color: white;
}
</style>
