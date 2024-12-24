<!--<script xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">-->

<script>
import axios from "axios"
import VSelect from "vue3-select";
//import {BIconTrash} from 'bootstrap-vue'
//import {BAlert} from "bootstrap-vue";
//import alert from "bootstrap/js/src/alert";
import NewServiceModal from "./ModalWindows/NewServiceModal.vue";
import NewSpecializationModal from "./ModalWindows/NewSpecializationModal.vue";
import NewClientModal from "./ModalWindows/NewClientModal.vue";
import NewCategoryModal from "./ModalWindows/NewCategoryModal.vue";
import NewEquipmentModelModal from "./ModalWindows/NewEquipmentModelModal.vue";

import {th} from "vuetify/locale";
export default {
  props: ['orderToEdit' , 'alreadyAddedServices'],
  components: {
    NewEquipmentModelModal,
    VSelect,
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
      materials: [],
      addedMaterials: [],
      addedProducts: [],
      equipmentModels: [],

      paid: '',
      userOrderNumber: '',
      totalServicePrice: 0,
      totalMaterialPrice: 0,
      totalAmount: 0,

      newMaterialId: 0,
      newMaterialName: null,
      newMaterialPrice: null,
      newMaterialCounter: 1,
      newMaterialSumPrice: null,

      selectedSpecialization: null,
      selectedClient: null,
      selectedCategory: null,
      comments: null,
      selectedEquipmentModel: null,


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

    newMaterialSumPriceCalc(){
        return this.newMaterialPrice * this.newMaterialCounter
    },

    totalAddedServicesPrice(){
      let sum = this.addedServices.reduce((total, service) => total + Number(service.price), 0)
      this.totalServicePrice = sum
      return sum
    },

    totalMaterialPrice(){
        console.log("материалы: ", this.materials)
        let sum = 0;
        console.log()
        for (let material of this.materials){
            sum += material.price * material.amount
        }
        this.totalMaterialPrice = sum
        return sum
    },

      totalPrice(){
          let sum = this.totalMaterialPrice + this.totalServicePrice
          this.totalAmount = sum
          return sum
      },
  },

  watch: {
        'orderToEdit.materials': function(newVal) {
            this.$nextTick(() => {
                const materialsTextArea = document.getElementById('materialsTextArea');
                if (materialsTextArea) this.autoResize({ target: materialsTextArea });
            });
        },
        'orderToEdit.comments': function(newVal) {
            this.$nextTick(() => {
                const commentsTextArea = document.getElementById('commentsTextArea');
                if (commentsTextArea) this.autoResize({ target: commentsTextArea });
            });
        }
    },

  methods: {

      adjustDropdownHeight() {
          this.$nextTick(() => {
              const selectElement = this.$refs.vSelect.$el;
              const dropdownMenu = selectElement.querySelector('.vs__dropdown-menu');
              if (dropdownMenu) {
                  const optionHeight = 32; // Примерная высота одной опции в пикселях
                  const visibleOptions = 6; // Количество видимых опций
                  const maxHeight = optionHeight * visibleOptions;
                  dropdownMenu.style.maxHeight = `${maxHeight}px`;
                  dropdownMenu.style.overflowY = 'auto';
              }
          })
      },

      updateMaterialTotal(material){
          console.log("материалы для расчета общей суммы", material)
          material.total = material.price * material.amount
      },

      updateMaterialCounter(materialId, newCounter) {
          console.log("добавленные материалы: ", this.addedMaterials)
          const material = this.addedMaterials.find(material => material.id === materialId)
          if (material){
              material.amount = newCounter
          }
          this.updateMaterialTotal(material)
      },

      updateProductCounter(productId, newCounter){
        console.log("добавленые продукты: ", this.addedProducts)
        const product = this.addedProducts.find(product => product.product_id === productId)
          if (product){
              product.amount = newCounter
          }
          this.updateMaterialTotal(product)
      },

      updateMaterialPrice(materialId, newPrice){
          const material = this.addedMaterials.find(material => material.id === materialId)
          if (material){
              material.price = newPrice
          }
          this.updateMaterialTotal(material)
      },

      updateMaterialName(materialId, newName){
          const material = this.addedMaterials.find(material => material.id === materialId)
          if (material){
              material.name = newName
          }
          console.log("material is update")
      },

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
      console.log('handleClientChange')
      if (this.selectedClient === 'create-new-client'){
        console.log('открываем модальное окно нового клиента')
        this.openNewClientModal()
      } else {
        console.log('selectedClient: ', this.selectedClient)
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
      console.log('выбрана категория: ', this.selectedCategory.id)
    },

    loadCategories(){
      axios.get(this.$Url + `/api/get_categories/${this.selectedSpecialization}`)
          .then(response => {
            this.categories = response.data
            console.log('список категорий: ',this.categories)
          })
          .catch(error => {
            console.error('Ошибка загрузки категорий: ', error.message)
          })
    },

    loadClients(){
      console.log('loading clients...')
      axios.get(this.$Url + `/api/get_clients/${this.selectedSpecialization}`)
          .then(response => {
            this.clients = response.data
            console.log('список клиентов: ', this.clients)
            this.selectedClient = this.clients.find(client => client.id === this.orderToEdit.client_id)
          })
          .catch(error => {
            console.error('Ошибка загрузки клиентов: ', error.message)
          })
    },

    loadEquipmentModels(){
      console.log('loading equipment models...')
      axios.get(this.$Url + `/api/get_equipment_models/${this.selectedSpecialization}`)
          .then(response => {
              this.equipmentModels = response.data
              console.log('список моделей: ', this.equipmentModels)
              this.selectedEquipmentModel = this.equipmentModels.find(equipmentModel => equipmentModel.id === this.orderToEdit.model_id)
          })
          .catch(error => {
              console.log('Ошибка загрузки моделей: ', error.message)
          })
    },

    loadServicesByCategory(){
      axios.get(this.$Url + `/api/get_service/${this.selectedCategory.id}`)
          .then(response => {
            this.services = response.data
            console.log('Список услуг: ', this.services)
          })
          .catch(error => {
            console.error('Ошибка загрузки услуг: ', error.message)
          })
    },

    loadMaterialsByOrder(){
      axios.get(this.$Url + `/api/get_materials_by_order/${this.orderToEdit.id}`)
          .then(response => {
              this.materials = response.data.map(material => {
                  material.total = material.amount * material.price
                  if (material.product_id){
                      this.addedProducts.push(material)
                  } else {
                      this.addedMaterials.push(material)
                  }
                  return material
              })
              console.log("materials: ", this.materials)
              console.log("addedMaterials: ", this.addedMaterials)
              console.log("addedProducts: ", this.addedProducts)
          })
          .catch(error => {
            console.error(error.message)
          })
    },

    loadSpecializations (){
      axios.get( this.$Url + '/api/getSpecialization')
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

    // deleteMaterial(materialId){
    //     console.log("удаляем материал с id: ", materialId)
    //     const materialIndex = this.materials.findIndex(material => material.id === materialId)
    //     if (materialIndex !== -1) {
    //         this.materials.splice(materialIndex, 1)
    //     }
    // },

      deleteMaterial(materialName){
          console.log("удаляем материал с именем: ", materialName);
          const materialIndex = this.materials.findIndex(material => material.name === materialName);
          if (materialIndex !== -1) {
              this.materials.splice(materialIndex, 1);
              console.log("Материал успешно удален", this.materials);
          } else {
              console.log("Материал не найден");
          }
      },

      //добавление материала в ордер
      addMaterial(){
          const newMaterial = {
              id: this.newMaterialId + 1,
              name: this.newMaterialName,
              price: this.newMaterialPrice,
              amount: this.newMaterialCounter,
              total: this.newMaterialSumPriceCalc,
          }
          this.materials.push(newMaterial)
          this.newMaterialName = ''
          this.newMaterialPrice= ''
          this.newMaterialCounter = ''
          console.log("добавленные материалы: ",this.materials)
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
      this.$refs.newServiceModal.selectedCategory = this.selectedCategory.id
      this.$refs.newServiceModal.open()
    },

    //сохранение ордера
    saveOrder() {
      console.log('создаем ордер')
      console.log('materials for send: ', this.materials)
      //const totalAmount = this.totalAddedServicesPrice
      const orderData = {
        id: this.orderToEdit.id,
        client_id: this.selectedClient.id,
        model_id: this.selectedEquipmentModel.id,
        specialization_id: this.selectedSpecialization,
        user_order_number: this.userOrderNumber,
        total_amount: this.totalAmount,
        materials: this.addedMaterials,
        products: this.addedProducts,
        comments: this.comments,
        services: this.addedServices.map(service => service.id),
        paid: this.paid
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
        axios.post(this.$Url + '/api/update_order', orderData, {
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        })
            .then(response => {
              console.log(response.data.message)
              //console.log('datasend: ',orderData )
              this.addedServices = []
              //this.materials = ''
              //this.comments = ''
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

    async getNameClient(){
        await this.wait(3000)
        this.selectedClient = this.clients.find(client => client.id === this.orderToEdit.client_id)
        console.log("selectedClient: ", this.selectedClient)
    },

    async mounted() {
        this.paid = this.orderToEdit.paid
        //события для ресайза текстогого поля
        document.querySelectorAll('textarea').forEach((element) => {
            element.addEventListener('input', this.autoResize);
        });

        this.addedServices = this.alreadyAddedServices
        this.selectedSpecialization = this.orderToEdit.specialization_id
        //this.selectedClient = this.orderToEdit
        this.materials = this.orderToEdit.materials
        this.comments = this.orderToEdit.comments
        //this.selectedClient = this.orderToEdit.client_id
        await axios.get(this.$Url + '/api/getSpecialization')
            .then(response => {
            this.specializations = response.data
            console.log('список специализаций: ', this.specializations )
            })
            .catch(eError => {
                console.error(eError.message)
            })
        this.loadClients()
        this.loadEquipmentModels()
        console.log('clients: ', this.clients)
        console.log('equipmentModels:', this.equipmentModels)
        //console.log("client_id: ", this.orderToEdit)
        //await this.selectedClient = this.clients.find(client => client.id === this.orderToEdit.client_id)
        this.loadCategories()
        this.loadMaterialsByOrder()
        this.userOrderNumber = this.orderToEdit.user_order_number
        console.log("выбранный клиент: ", this.selectedClient)
    }
}

</script>

<template>
  <h5> редактор ордера</h5>

    <div class="form-check form-switch">
        <input class="form-check-input"
               type="checkbox"
               role="switch"
               id="flexSwitchCheckChecked"
               v-model="paid"
        >
        <label class="form-check-label" for="flexSwitchCheckChecked">
            {{ paid ? 'Оплачено' : 'Не оплачено' }}
        </label>
    </div>

  <div>
    <div>

      <input id="orderNumber" type="number" v-model="this.userOrderNumber">
      <br>

        <div class="d-flex">
            <div class="col-11">
                <VSelect :value="selectedClient"
                         v-model="selectedClient"
                         :options="clients"
                         label="name"
                         @update:modelValue="handleClientChange"
                         @open="adjustDropdownHeight"
                         ref="vSelect"
                         class="limited-height"
                >
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
                </VSelect>
            </div>

            <button class="btn btn-primary"
                    type="button"
                    data-bs-target="#newClientModal"
                    data-bs-toggle="modal"
            >
                +
            </button>
        </div>
    </div>

        <div class="d-flex">
            <div class="col-11">
                <VSelect :value="selectedEquipmentModel"
                         v-model="selectedEquipmentModel"
                         :options="equipmentModels"
                         label="name"
                         placeholder="выберите модель..."
                         @update:modelValue="handleSelectEquipmentModelChange"
                         @open="adjustDropdownHeight"
                         ref="vSelect"
                         class="limited-height"
                >
                    <template #no-options="{ search, noResults }">
                        <div class="no-options">
                            <span>нет результатов...</span>
                            <span>
                          <button type="button"
                                  class="btn btn-primary"
                                  data-bs-target="#newEquipmentModelModal"
                                  data-bs-toggle="modal" >
                              Добавить модель
                          </button>
                      </span>
                        </div>
                    </template>
                    <template #append-item-custom>
                        <div class="v-select__append-item">
                            <span>Этот шаблон всегда виден</span>
                        </div>
                    </template>
                </VSelect>
            </div>

            <button class="btn btn-primary"
                    type="button"
                    data-bs-target="#newEquipmentModelModal"
                    data-bs-toggle="modal"
            >
                +
            </button>

    </div>

    </div>

<!--    <BAlert v-model="alertVisible" :variant="alertVariant" dismissible fade class="fixed-top"  >-->
<!--      {{ alertMessage }}-->
<!--    </BAlert>-->

    <div id="tabs">
<!--        <div class="container">-->
<!--            <ul class="nav nav-tabs" role="tablist">-->
<!--                <li class="nav-item"><a class="nav-link active" href="#serviceChoice" role="tab" data-bs-toggle="tab"> ВЫБОР УСЛУГ </a></li>-->
<!--                <li class="nav-item"><a class="nav-link" href="#addedServices" role="tab" data-bs-toggle="tab"> ДОБАВЛЕННЫХ УСЛУГ: {{this.addedServices.length}} </a></li>-->
<!--            </ul>-->
<!--        </div>-->

        <div class="container">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" href="#serviceChoice" role="tab" data-bs-toggle="tab"> РАБОТА</a></li>
                <li class="nav-item"><a class="nav-link" href="#materialChoice" role="tab" data-bs-toggle="tab"> МАТЕРИАЛЫ</a></li>

                <li class="nav-item">
                    <a class="nav-link" href="#addedServices" role="tab" data-bs-toggle="tab"> работ: {{this.addedServices.length}}  материалов: {{this.materials.length}}</a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane" id="materialChoice">

                <div style="text-align: center">Выбор материалов: </div>

                материалы
                <div v-for="material in addedMaterials" >
                    <div class="d-flex justify-content-between align-items-center">
                        <input type="text"
                               v-model="material.name"
                               @input="updateMaterialName(material.id, material.name)"
                               class="form-control form-control-sm custom-width-150"
                        >
                        <div class="d-flex align-items-center">

                            <input v-on:keypress="onlyNumbers"
                                   class="form-control form-control-sm custom-width-40"
                                   v-model="material.price"
                                   @input="updateMaterialPrice(material.id, material.price)"
                            >
                            x
                            <input v-on:keypress="onlyNumbers"
                                   class="form-control form-control-sm custom-width-25"
                                   v-model="material.amount"
                                   @input="updateMaterialCounter(material.id, material.amount)"
                            >
                            =
                            <input
                                class="form-control form-control-sm custom-width-40"
                                :readonly="true"
                                disabled
                                v-model="material.total"
                            >
                            <button class="btn btn-danger" @click="deleteMaterial(material.name)"> - </button>
                        </div>
                    </div>
                </div>

                продукты
                <div v-for="material in addedProducts" >
                    <div class="d-flex justify-content-between align-items-center">
                        <input type="text"
                               v-model="material.name"
                               @input="updateMaterialName(material.id, material.name)"
                               class="form-control form-control-sm custom-width-150"
                               disabled
                        >
                        <div class="d-flex align-items-center">

                            <input v-on:keypress="onlyNumbers"
                                   class="form-control form-control-sm custom-width-40"
                                   v-model="material.price"
                                   @input="updateProductSalePrice(material.id, material.price)"
                                   disabled
                            >
                            x
                            <input v-on:keypress="onlyNumbers"
                                   class="form-control form-control-sm custom-width-25"
                                   v-model="material.amount"
                                   @input="updateProductCounter(material.product_id, material.amount)"
                            >
                            =
                            <input
                                class="form-control form-control-sm custom-width-40"
                                :readonly="true"
                                disabled
                                v-model="material.total"
                            >
                            <button class="btn btn-danger" @click="deleteMaterial(material.name)"> - </button>
                        </div>
                    </div>
                </div>

                <a id="materialTotalSum">итого по материаламм: </a>
                <a class="mb-0">{{totalMaterialPrice}}</a>
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
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane show active" id="serviceChoice">


                <VSelect v-model="selectedCategory"
                         :options="categories"
                         label="category_name"
                         placeholder="выберите категорию..."
                         @update:modelValue="handleCategoriesChange"
                         @open="adjustDropdownHeight"
                         ref="vSelect"
                         class="limited-height"
                >
                    <template #no-options="{ search, noResults }">
                        <div class="no-options">
                            <span>нет результатов...</span>
                            <span>
                                  <button type="button"
                                          class="btn btn-primary"
                                          data-bs-target="#newCategoryModal"
                                          data-bs-toggle="modal" >
                                      Добавить категорию
                                  </button>
                              </span>
                        </div>
                    </template>
                </VSelect>

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

                <div>Итого к оплате: {{totalAmount}}</div>
                <br>

            </div>
            <div class="tab-pane" id="addedServices">
                <div id="addedServices">

                    <div id="serviceLabel" class="d-flex justify-content-center">Работа: </div>

                    <div v-for="service in addedServices" :key="service.id" >
                        <div class="d-flex justify-content-between align-items-center">
                            <div>{{service.service}}</div>
                            <div>{{service.price}}</div>
                            <button class="btn btn-danger" @click="deleteFromAdded(service.id)">
                                -
<!--                                <BIconTrash icon="trash"></BIconTrash>-->
                            </button>
                        </div>
                    </div>

                    <div id="serviceTotalSum"> итого по работе: {{totalAddedServicesPrice}}</div>


                    <div id="materialsLabel" class="d-flex justify-content-center">Материалы: </div>

                    <div v-for="material in materials" >
                        <div class="d-flex justify-content-between align-items-center">
                            <div> {{material.name}} </div>
                            <div class="d-flex align-items-center">
                                <div id="price">{{material.price}}р</div>
                                <div id="counter">X{{material.amount}} =</div>
                                <div id="total">  {{material.price * material.amount}}р</div>
                                <button class="btn btn-danger" @click="deleteMaterial(material.name)"> - </button>
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

                    <textarea id="commentsTextArea"
                              v-model="this.comments"
                              placeholder="нет комментарии"
                              rows="1"
                              class="form-control form-control-sm"
                    ></textarea>

                </div>

            </div>
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
                        @client-added="loadClients"
        />

        <NewEquipmentModelModal :selected-specialization="selectedSpecialization"
                                ref="newEquipmentModelModal"
                                @equipmentModel-added="loadEquipmentModels"
        />

        <NewCategoryModal :selected-specialization="selectedSpecialization"
                          ref="newCategoryModal"
                          @category-added="loadCategories"
        />

    <div class="fixed-bottom">
      <div class="container-fluid">
        <div class="row justify-content-end">
          <button @click="saveOrder()" class="btn btn-primary col-6" style="position: relative" >
            сохранить
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

.nav-link {
    color: black;
    font-size: 95%;
    padding: max(3px);
}

.nav-link.active {
    background: #2c6efc;
    color: white;
    font-size: 95%;
    font-weight: bold;
    transition: background-color 1s;
}
.custom-width-150 {
    width: 150%;
}
.custom-width-40 {
    width: 40%;
}
.custom-width-25 {
    width: 25%;
}
.custom-width-10 {
    width: 10%;
}

.limited-height .vs__dropdown-menu {
    max-height: 280px; /* Примерное значение для 7 опций по 40px каждая */
    overflow-y: auto;
}
</style>
