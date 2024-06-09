<script>
import 'bootstrap'
import axios from "axios"
//import {BIconTrash} from 'bootstrap-vue'
//import {BAlert} from "bootstrap-vue";
//import alert from "bootstrap/js/src/alert";
import NewServiceModal from "./ModalWindows/NewServiceModal.vue";
import NewSpecializationModal from "./ModalWindows/NewSpecializationModal.vue";
import NewClientModal from "./ModalWindows/NewClientModal.vue";
import NewCategoryModal from "./ModalWindows/NewCategoryModal.vue";
import VSelect from "vue3-select";
export default {
   components: {
     NewClientModal,
     NewSpecializationModal,
     NewServiceModal,
     NewCategoryModal,
     VSelect,
   //  BIconTrash,
   //  BAlert,
   },

  data(){
    return {
      //searchQuery: '',
      specializations: [],
      clients: '',
      categories: [],
      services: [],
      addedServices: [],
      addedMaterials: [],

      selectedSpecialization: null,
      selectedClient: null,
      selectedCategory: null,
      userOrderNumber: null,
      selectedStatus: '',


        statusOptions: [
          {name: 'выполнено', value: 'done', color: 'green'},
          {name: 'в работе', value: 'process', color: 'red'},
          {name: 'в ожидании', value: 'waiting', color: 'orange'},
      ],

      newMaterialId: 0,
      newMaterialName: null,
      newMaterialPrice: null,
      newMaterialCounter: 1,
      newMaterialSumPrice: null,

      materials: null,
      comments: null,

      alertVisible: false,
      alertVariant: 'success',
      alertMessage: '',

      isNewServiceModalOpen: false,

      materialsPrice: 0,
      totalAmount: 0

      }
  },

  computed: {

    totalAddedServicesPrice(){
      return this.addedServices.reduce((total, service) => total + Number(service.price), 0)
    },

    totalPrice(){
        let sum = this.materialsPrice + this.totalAddedServicesPrice
        this.totalAmount = sum
        return sum
    },

    newMaterialSumPriceCalc(){
      return this.newMaterialPrice * this.newMaterialCounter
    },
    //
      totalMaterialPrice(){
          console.log("материалы: ", this.addedMaterials)
          let sum = 0;
          for (let material of this.addedMaterials){
              sum += material.price * material.counter
          }

          this.materialsPrice = sum;
          return sum
      }
  },

  methods: {

      // onSearch(searchQuery) {
      //     this.searchQuery = searchQuery;
      //     this.filteredClients = this.clients.filter(client =>
      //         client.name.toLowerCase().includes(searchQuery.toLowerCase())
      //     );
      //
      //     // Добавление опции "Добавить нового клиента" в конец списка
      //     this.filteredClients.push({ name: 'Добавить нового клиента', isCreate: true });
      // },

      onlyNumbers(event) {
          if (!/[0-9]/.test(event.key)) {
              event.preventDefault();
          }
      },

     autoResize(event){
         const textarea = event.target;
         textarea.style.height = 'auto';
         textarea.style.height = textarea.scrollHeight + 'px';
     },

    handleSpecializationChange(){
      if (this.selectedSpecialization === 'create_new_specialization'){
        this.openNewSpecializationModal()
      } else {
        this.loadClients()
        this.loadCategories()
      }
      console.log('выбрана специализация: ', this.selectedSpecialization)
    },

    handleClientChange(){
      console.log('выбор клиента')
      if (this.selectedClient === 'create-new-client'){
        console.log('открываем модальное окно нового клиента')
        this.openNewClientModal()
      } else {
        //
      }
    },

    lastClientAddedOn(){
        this.selectedClient = this.clients[0]
    },

    async onClientAdded(){
      await this.loadClients()
      setTimeout(()  => { this.lastClientAddedOn()}, 1000)
    },

    handleClientAdded(newClient){
      this.selectedClient = newClient
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

    handleSelectClientChange(value){
      console.log('Выбрана опция: ', value)
      if (value && value.isCreate){
          console.log("открываем модальное окно создание клиента: ")
          let modal = new bootstrap.Modal(document.getElementById('newClientModal'))
          modal.show()
      } else {
          this.selectedClient = value
          console.log("выбранный клитент", this.selectedClient)
      }
    },

    loadCategories(){
      axios.get(this.$Url + `/api/get_categories/${this.selectedSpecialization}`)
          .then(response => {
            this.categories = response.data
            console.log('список категорий: ', this.categories)
            if (this.categories.length > 0) {
                this.selectedCategory = this.categories[0].id
                this.loadServicesByCategory()
            }
          })
          .catch(error => {
            console.error('Ошибка загрузки категорий: ', error.message)
          })
    },

    loadClients(){
      axios.get(this.$Url + `/api/get_clients/${this.selectedSpecialization}`)
          .then(response => {
            this.clients = response.data
            this.clients.reverse()
            this.clients.push({ id: null, name: 'Добавить клиента', isCreate: true})
            //this.selectedClient = this.clients[0]
            console.log('список клиентов: ', this.clients)
            console.log('выбранный клиент: ', this.selectedClient)
          })
          .catch(error => {
            console.error('Ошибка загрузки клиентов: ', error.message)
          })
    },

    loadServicesByCategory(){
      axios.get(this.$Url + `/api/get_service/${this.selectedCategory}`)
          .then(response => {
            this.services = response.data
            console.log('Список услуг: ', this.services)
          })
          .catch(error => {
            console.error('Ошибка загрузки услуг: ', error.message)
          })
    },

    loadSpecializations (){
      axios.get(this.$Url + '/api/getSpecialization')
          .then(response => {
            this.specializations = response.data
            console.log('список специализаций: ', this.specializations )
          })
          .catch(eError => {
            console.error(eError.message)
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

    //добавление материала в ордер
    addMaterial(){
      const newMaterial = {
        id: this.newMaterialId + 1,
        name: this.newMaterialName,
        price: this.newMaterialPrice,
        counter: this.newMaterialCounter,
        total: this.newMaterialSumPriceCalc,
      }
      this.addedMaterials.push(newMaterial)
      this.newMaterialName = ''
      this.newMaterialPrice= ''
      this.newMaterialCounter = ''
      console.log("добавленные материалы: ",this.addedMaterials)
    },

    deleteMaterial(materialId){
        console.log("удаляем материал с id: ", materialId)
        const materialIndex = this.addedMaterials.findIndex(material => material.id === materialId)
        if (materialIndex !== -1) {
            this.addedMaterials.splice(materialIndex, 1)
        }
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
        const newSpecializationModal = new bootstrap.Modal(document.getElementById('newSpecializationModal'))
        newSpecializationModal.show()
    },

    openNewClientModal(){
      this.$refs.newClientModal.selectedClient = this.selectedClient
      this.$refs.newClientModal.open()
    },

    openNewCategoryModal() {
        console.log('открытие модального окна новой категории')
        console.log('выбрана специализацияя: ', this.selectedSpecialization)
        this.$refs.newCategoryModal.open(this.selectedSpecialization)
    },

    // открытие модального окна для добавления новой услуги
    openNewServiceModal(){
      console.log('создаем новую услугу')
      console.log('selectedCategory: ', this.selectedCategory)
      this.$refs.newServiceModal.selectedCategory = this.selectedCategory
      console.log('открываем модальное окно')
      this.$refs.newServiceModal.open()
    },

    //сохранение ордера
    saveOrder() {
      console.log('создаем ордер')
      // const totalAmount = this.totalAddedServicesPrice
      const orderData = {
        clientId: this.selectedClient.id,
        userOrderNumber: this.userOrderNumber,
        specializationId: this.selectedSpecialization,
        status: this.selectedStatus.value,
        totalAmount: this.totalAmount,
        addedMaterials: this.addedMaterials,
        materials: this.materials,
        comments: this.comments,
        servicesId: this.addedServices.map(service => service.id)
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
        axios.post(this.$Url +'/save_order', orderData, {
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        })
            .then(response => {
              console.log("ответ с сервера: ", response.data.message)
              this.addedServices = []
              this.addedMaterials = []
              this.materials = ''
              this.comments = ''
              this.showAlert('success', 'Ордер успешно сохранен')
              console.log("адрес перенаправления: ", this.$Url + '/history')
              window.location.href = this.$Url + '/history'
            })
            .catch(error => {
              console.error('Ошибка сохранения ордера: ', error)
              this.showAlert('danger', 'Ошибка сохроанения ордера')
            })
      }
    }
  },

  mounted() {
       this.selectedStatus = this.statusOptions[2]
       console.log(this.$Url)
      //console.log('cookie: ', document.cookie)
      //console.log('laravel-session cookie: ',document.cookie.includes('laravel_session'));
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      axios.defaults.withCredentials = true
      //const laravelSessionToken = document.cookie.match(/laravel_session=([^;]+)/)[1];
      console.log('csrf: ', csrfToken)
      //console.log('auth-token: ', laravelSessionToken)
      axios.get(this.$Url +'/get_all_specializations', {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            //'Authorization': `Bearer ${laravelSessionToken}`
            }
        })
        .then(response => {
          this.specializations = response.data
          console.log('список специализаций: ', this.specializations )

          if (this.specializations.length > 0) {
              this.selectedSpecialization = this.specializations[0].id
              console.log('выбрана специализация: ' , this.selectedSpecialization)
              this.loadClients()
              this.loadCategories()
              this.loadServicesByCategory()
          }

        })
        .catch(eError => {
          console.error(eError.message)
        })

      //события для ресайза текстогого поля
      document.querySelectorAll('textarea').forEach((element) => {
          element.addEventListener('input', this.autoResize);
      });
  }
}

</script>

<template>
  <div>

      <div>
          <div class="d-flex">
              <select v-model="selectedSpecialization" @change="handleSpecializationChange" class="form-select w-auto" >
<!--                  <option value="" class="select-option&#45;&#45;placeholder" selected>Выберите специализацию</option>-->
                  <option v-for="specialization in specializations"
                          :key="specialization.id" :value="specialization.id" >
                      {{ specialization.specializationName }}
                  </option>
                  <option disabled v-if="specializations.length === 0">нет специализаций</option>
              </select>

              <button type="button"
                      class="btn btn-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#newSpecializationModal" >
                  +
              </button>

              <input v-model="userOrderNumber" id="orderIdUser" type="number" class="input-group-sm" placeholder="номер заказа">

          </div>


          <VSelect :value="selectedClient"
                   v-model="selectedClient"
                   :options="clients"
                   label="name"
                   placeholder="выберите клиента..."
                   @update:modelValue="handleSelectClientChange"
          >
<!--              <template #singlLable="{option}">-->
<!--                <div>-->
<!--                    <span v-if="option.isCreate">{{option.name}}</span>-->
<!--                    <span v-else>{{option.name}}</span>-->
<!--                </div>-->
<!--              </template>-->
              <template #no-options="{ search, noResults }">
                  <div class="no-options">
                      <span>нет результатов...</span>
                      <span>
                          <button type="button"
                                    class="btn btn-primary"
                                    data-bs-target="#newClientModal"
                                    data-bs-toggle="modal" >
                              Добавить клиента
                          </button>
                      </span>
                  </div>
              </template>
              <template #append-item-custom>
                  <div class="v-select__append-item">
                      <span>Этот шаблон всегда виден</span>
                  </div>
              </template>
<!--              <template #append-item>-->
<!--                  <div>-->
<!--                      <button>Добавит  <button type="button" class="btn btn-primary" @click="handleAddNewClient">-->
<!--                          Добавить нового клиента-->
<!--                      </button>ь нового клиента</button>-->
<!--                  </div>-->
<!--              </template>-->

          </VSelect>
          <VSelect :options="statusOptions"
                   v-model="selectedStatus"
                   label="name"
                   :searchable="false"
                   :clearable="false"
          >
              <template #selected-option="option">
                  <div :style="{color: option.color,}">
                      {{option.name}}
                  </div>
              </template>
          </VSelect>

          <div>
              <select v-model="selectedCategory" @change="handleCategoriesChange" class="form-select w-auto" >
                  <option v-for="category in categories"
                          :key="category.id" :value="category.id">
                      {{category.category_name}}
                  </option>
              </select>

              <button type="button"
                      class="btn btn-primary"
                      data-bs-target="#newCategoryModal"
                      data-bs-toggle="modal"
                      v-on:click="openNewCategoryModal"
              >
                  +
              </button>

          </div>

          <div id="tabsTest">
          <div class="container">
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item"><a class="nav-link active" href="#serviceChoice" role="tab" data-bs-toggle="tab"> ВЫБОР УСЛУГ </a></li>
              <li class="nav-item">
                  <a class="nav-link" href="#addedServices" role="tab" data-bs-toggle="tab"> ДОБАВЛЕННЫХ УСЛУГ:
                        {{this.addedServices.length}}
                  </a>
              </li>
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
                       data-bs-toggle="modal"
                       data-bs-target="#newServiceModal"
                       v-if="selectedCategory"
                       v-on:click="openNewServiceModal"
                       style="background-color: #2C6EFC; color: white; text-align: center"
                  >
                      Добавить новую услугу
                  </div>

                  <div>Всего к оплате: {{totalAmount}}</div>

              </div>
              <div class="tab-pane" id="addedServices">
                  <div id="addedServices">

                      <div id="serviceLabel" class="d-flex justify-content-center">Работа: </div>


                      <div v-for="service in addedServices" :key="service.id" >
                          <div class="d-flex justify-content-between align-items-center">
                              <div>{{service.service}}</div>
                              <div class="d-flex align-items-center">
                                  <div id="price">{{service.price}}</div>
                                  <button class="btn btn-danger" @click="deleteFromAdded(service.id)">
                                      -
                                      <!--                                  <BIconTrash icon="trash"></BIconTrash>-->
                                  </button>
                              </div>
                          </div>
                      </div>

                      <div id="materialsLabel" class="d-flex justify-content-center">Материалы: </div>


                      <div v-for="material in addedMaterials" >
                          <div class="d-flex justify-content-between align-items-center">
                              <div> {{material.name}} </div>
                              <div class="d-flex align-items-center">
                                  <div id="price">{{material.price}}р</div>
                                  <div id="counter">X{{material.counter}} =</div>
                                  <div id="total">  {{material.total}}р</div>
                                  <button class="btn btn-danger" @click="deleteMaterial(material.id)"> - </button>
                              </div>
                          </div>
                      </div>

                      <div id="materialTotalSum">итого по материалам: {{totalMaterialPrice}}</div>
                      <div id="totalSum"> Всего к оплате: {{totalPrice}}</div>

                      <br>
                      <div class="container">
                          <div class="row" style="margin-right: -15px; margin-left: -15px">
                              <div class="col-md-6" style="flex-basis: 52%; padding-right: 0; padding-left: 0;">
                                  <input id="newMaterialName"
                                         v-model="newMaterialName"
                                         placeholder="название материала"
                                         class="form-control"
                                  >
                              </div>
                              <div class="col-md-3" style="flex-basis: 18%; padding-right: 0; padding-left: 0;">
                                  <input id="newMaterialPrice"
                                         v-model="newMaterialPrice"
                                         placeholder="цена"
                                         class="form-control"
                                         v-on:keypress="onlyNumbers"
                                  >
                              </div>
                              <div class="col-md-1" style="flex-basis: 12%; padding-right: 0; padding-left: 0;" >
                                  <input id="newMaterialCounter"
                                         v-model="newMaterialCounter"
                                         placeholder="шт"
                                         class="form-control"
                                         v-on:keypress="onlyNumbers"
                                  >
                              </div>
                              <div class="col-md-3" style="flex-basis: 18%; padding-right: 0; padding-left: 0;">
                                  <input id="newMaterialSumPrice"
                                         v-model="newMaterialSumPriceCalc"
                                         placeholder="всего"
                                         class="form-control"
                                         disabled
                                  >
                              </div>
                          </div>
                          <div class="row justify-content-end mt-3">
                              <div class="col-auto"
                                   v-if="newMaterialName && newMaterialName.trim() !== ''">
                                  <button class="btn btn-primary"
                                          @click="addMaterial"
                                  >
                                      добавить материал
                                  </button>
                              </div>
                          </div>
                      </div>

                      <br>

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

<!--      <div>-->
<!--        <div>-->
<!--          <div>-->
<!--            <div>-->
<!--              <div>-->
<!--                Сумма: {{ totalAddedServicesPrice }}-->
<!--              </div>-->
<!--            </div>-->
<!--          </div>-->
<!--        </div>-->
<!--      </div>-->

    </div>

<!--    <BAlert v-model="alertVisible" :variant="alertVariant" dismissible fade class="fixed-top"  >-->
<!--      {{ alertMessage }}-->
<!--    </BAlert>-->

    <div class="fixed-bottom">
      <div class="justify-content-between">
          <button @click="saveOrder()" class="btn btn-primary m-3" >
            сохранить
          </button>
      </div>

    </div>

      <NewServiceModal :selectedCategory="selectedCategory"
                       ref="newServiceModal"
                       @service-added="loadServicesByCategory"
      />

      <NewSpecializationModal ref="newSpecializationModal"
                              @specialization-added="loadSpecializations"
      />

      <NewClientModal :selected-specialization="selectedSpecialization"
                      ref="newClientModal"
                      @client_added="onClientAdded"
      />

      <NewCategoryModal :selected-specialization="selectedSpecialization"
                        ref="newCategoryModal"
                        @category-added="loadCategories"
      />

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

#price {
    margin-right: 5px;
}

.nav-link {
    color: black;
}

.nav-link.active {
    background: #2c6efc;
    color: white;
}


</style>
