<script xmlns="http://www.w3.org/1999/html">
import axios from "axios"
import NewServiceModal from "./ModalWindows/NewServiceModal.vue";
import DeleteServiceModal from "./ModalWindows/DeleteServiceModal.vue";
import EditServiceModal from "./ModalWindows/EditServiceModal.vue";
import NewClientModal from "./ModalWindows/NewClientModal.vue";
import DeleteClientModal from "./ModalWindows/DeleteClientModal.vue";
import EditClientModal from "./ModalWindows/EditClientModal.vue";
import NewCategoryModal from "./ModalWindows/NewCategoryModal.vue";
import EditCategoryModal from "./ModalWindows/EditCategoryModal.vue";
import DeleteCategoryModal from "./ModalWindows/DeleteCategoryModal.vue";
import NewSpecializationModal from "./ModalWindows/NewSpecializationModal.vue";
import EditSpecializationModal from "./ModalWindows/EditSpecializationModal.vue";
import DeleteSpecializationModal from "./ModalWindows/DeleteSpecializationModal.vue";

import NewProductCategoryModal from "./ModalWindows/NewProductCategoryModal.vue";
import DeleteProductCategoryModal from "./ModalWindows/DeleteProductCategoryModal.vue";
import EditProductCategoryModal from "./ModalWindows/EditProductCategoryModal.vue";

import NewProductModal from "./ModalWindows/NewProductModal.vue";
import DeleteStoreProductModal from "./ModalWindows/DeleteStoreProductModal.vue";


import ArrivalProductModal from "./ModalWindows/ArrivalProductModal.vue";


//import {BIconTrash} from "bootstrap-vue"
//import {BIconPencilSquare} from "bootstrap-vue";
//import {BAlert} from "bootstrap-vue";

export default {
  components: {
  //  BAlert,
   // BIconTrash,
   // BIconPencilSquare,

    NewServiceModal,
    DeleteServiceModal,
    EditServiceModal,

    NewClientModal,
    DeleteClientModal,
    EditClientModal,

    NewCategoryModal,
    EditCategoryModal,
    DeleteCategoryModal,

    NewProductCategoryModal,
    EditProductCategoryModal,
    DeleteProductCategoryModal,

    NewProductModal,
    DeleteStoreProductModal,


    ArrivalProductModal,


    NewSpecializationModal,
    EditSpecializationModal,
    DeleteSpecializationModal,
  },

  data() {
    return {
      specializations: [],
      clients: [],
      categories: [],
      productCategories: [],
      services: [],
      productStocks: [],

      selectedSpecializations: '',
      selectedSpecializationName: '',
      selectedClients: '',
      selectedCategory: '',
      selectedProductCategory: '',

      isDeleteClientModalOpen: true,
      currentClientId: null,

      alertVisible: false,
      alertVariant: 'success',
      alertMessage: '',
    }
  },

  methods: {
    handleSpecializationChange() {
      if (this.selectedSpecializations === 'create_new_specialization'){
        this.openNewSpecializationModal()
      } else {
        this.loadClients()
        this.loadCategories()
        //this.loadProductsByCategory()
      }
    },

    handleCategoryChange(){
      if (this.selectedCategory === 'create_new_category') {
        this.openNewCategoryModal()
      } else {
        console.log('Выбрана категория: ', this.selectedCategory)
        this.loadServicesByCategory()
      }
    },

    handleClientAdded(){
      console.log('клиент добавлен')
      this.loadClients()
      this.showAlert('success', 'клиент добавлен')
    },

    handleClientDeleted(){
      this.loadClients()
      this.showAlert('success', 'клиент удален')
    },

    handleClientEdited(){
      this.loadClients()
      this.showAlert('success', 'клиент изменен')
    },

    handleServiceAdded(){
      this.loadServicesByCategory()
      this.showAlert('success', 'сервис добавлен')
    },

    handleServiceDeleted(){
      this.loadServicesByCategory()
      this.showAlert('success', 'сервис удален')
    },

    handleServiceEdited(){
      this.loadServicesByCategory()
      this.showAlert('success', 'сервис изменен')
    },

    handleCategoryAdded(){
      this.loadCategories()
      this.showAlert('success', 'категория добавлена')
    },

    handleCategoryEdited(){
      this.loadCategories()
      this.showAlert('success', 'категория изменена')
    },

    handleCategoryDeleted(){
      this.loadCategories()
      this.showAlert('success', 'категория удалена')
    },

    handleProductCategoryAdded(){
        this.loadProductCategories()
        this.showAlert('success', 'категория добавлена')
    },

    handleProductCategoryDeleted(){
      this.loadProductCategories()
      this.showAlert('success', 'категория удалена')
    },

    handleProductAdded(){
      this.loadProductsByCategory()
    },

    handleSpecializationAdded(){
      //тут нужна перезагрузка страницы
    },

    handleSpecializationEdited(){
      //тут перезагрузка страницы
    },

    handleSpecializationDeleted(){
      //перезагрузка страницы
    },

    openNewServiceModal() {
      console.log(this.selectedCategory)
      if (this.$refs.newServiceModal) {
        this.$refs.newServiceModal.selectedCategory = this.selectedCategory
        this.$refs.newServiceModal.open()
      } else {
        console.log('модальное окно еще не доступно')
      }
    },

    openNewStoreProductModal(){
        console.log('открываем модальное окно создания продукта, категории: ', this.selectedProductCategory)
        if (this.$refs.newStoreProductModal) {
            this.$refs.newStoreProductModal.selectedProductCategory = this.selectedProductCategory
            this.$refs.newStoreProductModal.open(this.selectedProductCategory)
        } else {
            console.log('модальное окно еще не доступно')
        }
    },

    openEditStoreProductModal(productId, currentProductName, currentServicePrice){
      //
    },

    openDeleteServiceModal(serviceId){
      console.log(serviceId)
      this.$refs.deleteServiceModal.open(serviceId)
    },

    openEditServiceModal(serviceId, currentServiceName, currentServicePrice){
      console.log('редактирование сервиса')
      this.$refs.editServiceModal.open(serviceId, currentServiceName, currentServicePrice)
    },



    loadClients() {
      axios.get(this.$Url + `/api/get_clients/${this.selectedSpecializations}`)
          .then(response => {
            this.clients = response.data
            this.clients.reverse()
          })
          .catch(error => {
            console.error('ошибка загрузки клиентов: ', error.message.response.data.message)
          })
    },

    loadCategories() {
      axios.get(this.$Url + `/api/get_categories/${this.selectedSpecializations}`)
          .then(response => {
            this.categories = response.data
            if (this.categories.length > 0){
                this.selectedCategory = this.categories[0].id
                this.loadServicesByCategory()
            }
          })
          .catch(error => {
            console.error('Ошибка загрузки категорий: ', error.message)
          })
    },

    loadProductCategories(){
        axios.get(this.$Url + `/api/get_product_categories/${this.selectedSpecializations}`)
            .then(response => {
                this.productCategories = response.data
                if (this.productCategories.length > 0){
                    this.selectedProductCategory = this.productCategories[0].id
                    //this.loadProductsByCategory()
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки категорий: ', error.message)
            })
    },

    loadProductsByCategory(){
        console.log('load products by category')
        axios.get(this.$Url + `/api/get_product_stocks/${this.selectedProductCategory}`)
            .then(response => {
                console.log('товары: ', response.data)
                this.productStocks = response.data
            })
            .catch(error => {
                console.log('ошибка загрузки товаров: ', error)
            })
    },

    loadServicesByCategory() {
      axios.get(this.$Url + `/api/get_service/${this.selectedCategory}`)
          .then(response => {
            this.services = response.data
          })
          .catch(error => {
            console.log('ошибка загрузки сервисов', error.message)
          })
    },

    openNewClientModal(specializationId){
      this.$refs.newClientModal.open(specializationId)
    },

    openDeleteClientModal(clientId){
      this.$refs.deleteClientModal.open(clientId)
    },

    openDeleteStoreProductModal(productId){
      this.$refs.deleteStoreProductModal.open(productId)
    },

    openEditClientModal(clientId, currentClientName, currentClientPhone){
      this.$refs.editClientModal.open(clientId, currentClientName, currentClientPhone)
    },

    openNewSpecializationModal(){
      this.$refs.newSpecializationModal.open()
    },

    openEditSpecializationModal(){
      const currentSpecialization = this.specializations.find(spec => spec.id === this.selectedSpecializations)
      this.$refs.editSpecializationModal.open(this.selectedSpecializations, currentSpecialization.specializationName)
    },

    openDeleteSpecializationModal(){
      console.log(this.selectedSpecializations)
      this.$refs.deleteSpecializationModal.open(this.selectedSpecializations)
    },

    openNewCategoryModal() {
      console.log('выбрана специализация: ', this.selectedSpecializations)
      this.$refs.newCategoryModal.open(this.selectedSpecializations)
    },


    openEditCategoryModal(){
      const currentCategory = this.categories.find(cat => cat.id === this.selectedCategory)
      this.$refs.editCategoryModal.open(this.selectedCategory, currentCategory.category_name)
    },

    openDeleteCategoryModal(){
      console.log(this.selectedCategory)
      this.$refs.deleteCategoryModal.open(this.selectedCategory)
    },

    openNewProductCategoryModal() {
        console.log('выбрана специализация: ', this.selectedSpecializations)
        this.$refs.newProductCategoryModal.open(this.selectedSpecializations)
    },

    openArrivalProductModal(productId, productName, baseSalePrice, quantity){
        console.log('поступление: ', productId, productName, baseSalePrice, quantity)
        this.$refs.arrivalProductModal.open(productId, productName, baseSalePrice, quantity)
    },

    openEditProductCategoryModal(){
        const currentProductCategory = this.productCategories.find(cat => cat.id === this.selectedProductCategory)
        this.$refs.editProductCategoryModal.open(this.selectedProductCategory, currentProductCategory.name)
    },

    openDeleteProductCategoryModal(){
        console.log('удаляем категорию: ',this.selectedProductCategory)
        this.$refs.deleteProductCategoryModal.open(this.selectedProductCategory)
    },

    toggleClientsButtons(client){
      this.clients.forEach((elem) => {
          elem.isClicked = false
      })
      client.isClicked = !client.isClicked
    },

    toggleServiceButtons(service){
      this.services.forEach((elem) => {
          elem.isClicked = false
      })
      service.isClicked = !service.isClicked
    },

    toggleProductButtons(product){
      this.productStocks.forEach((elem) => {
          elem.isClicked = false
      })
      product.isClicked = !product.isClicked
    },

    showAlert(variant, message){
      this.alertVariant = variant
      this.alertMessage = message
      this.alertVisible = true
      setTimeout(() => {
        this.alertVisible = false
      }, 2000)
    },

  },

  mounted() {
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      axios.get(this.$Url + '/get_all_specializations', {
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              //  'Authorization': `Bearer ${laravelSessionToken}`
          }
      })
        .then(response => {
          this.specializations = response.data
          console.log(response.data)
          if (this.specializations.length > 0) {
              this.selectedSpecializations = this.specializations[0].id
              this.loadCategories()
              this.loadProductCategories()
              this.loadClients()
          }
        })
        .catch(eError => {
          console.log(eError.message)
        })
  }
}

</script>

<template>
  <div>
    <div class="d-flex">
      <select v-model="selectedSpecializations" @change="handleSpecializationChange" class="form-select w-auto">

        <option v-for="specialization in specializations"
                              :key="specialization.id" :value="specialization.id">
          {{ specialization.specializationName }}
        </option>

        <option value="create_new_specialization">
          создать новую специализацию
        </option>

      </select>
      <button class="btn btn-primary"
              @click="openNewSpecializationModal"
              data-bs-target="#newSpecializationModal"
              data-bs-toggle="modal"
      >
          +
      </button>

      <button class="btn btn-secondary"
              @click="openEditSpecializationModal()"
              data-bs-target="#editSpecializationModal"
              data-bs-toggle="modal"
      >
          редактировать
      </button>
      <button class="btn btn-danger"
              data-bs-toggle="modal"
              data-bs-target="#deleteSpecializationModal"
              @click="openDeleteSpecializationModal()">
          -
      </button>
    </div>

    <div class="container">
        <ul class="nav nav-tabs active" role="tablist">
            <li class="nav-item">
                <a class="nav-link" href="#serviceChoice" role="tab" data-bs-toggle="tab">УСЛУГИ</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#store" role="tab" data-bs-toggle="tab">СКЛАД</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#clientChoice" role="tab" data-bs-toggle="tab">КЛИЕНТЫ</a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane" id="serviceChoice">

            <div class="d-flex" style="margin: 5px">
                <select v-model="selectedCategory" class="form-select w-auto" @change="handleCategoryChange">
                    <option v-for="category in categories"
                            :key="category.id" :value="category.id">
                        {{ category.category_name }}
                    </option>
                </select>
                <button class="btn btn-primary"
                        @click="openNewCategoryModal()"
                        data-bs-toggle="modal"
                        data-bs-target="#newCategoryModal"
                >
                    +
                </button>
                <button class="btn btn-secondary"
                        @click="openEditCategoryModal(selectedCategory.id, selectedCategory.category_name)"
                        data-bs-toggle="modal"
                        data-bs-target="#editCategoryModal"
                >
                    редактировать
                </button>
                <button class="btn btn-danger"
                        @click="openDeleteCategoryModal()"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteCategoryModal"
                >
                    -
                </button>
            </div>

            <div id="serviceItem"
                 v-for="service in services"
                 :key="service.id"
                 @click="toggleServiceButtons(service)"
                 :style="{border: service.isClicked ? '2px solid black' : 'white'}"
            >
                <div class="d-flex justify-content-between align-items-center">
                    <div>{{service.service }}</div>
                    <div>{{service.price}}</div>
                </div>

                <div id="servicesEditButtons" v-if="service.isClicked">
                    <button @click="openDeleteServiceModal(service.id)"
                            class="btn btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteServiceModal"
                    >
                        удалить
                        <BIconTrash icon="trash"></BIconTrash>
                    </button>
                    <button @click="openEditServiceModal(service.id, service.service, service.price)"
                            class="btn btn-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#editServiceModal"
                    >
                        редактировать
                        <BIconPencilSquare icon="pencil-square"></BIconPencilSquare>
                    </button>
                </div>

            </div>

            <div id="serviceItem"
                 data-bs-toggle="modal"
                 data-bs-target="#newServiceModal"
                 v-if="selectedCategory"
                 v-on:click="openNewServiceModal"
                 class="d-flex justify-content-center align-items-center"
                 style="background-color: #2C6EFC; color: white; text-align: center"
            >
                добавить новую услугу
            </div>
        </div>
    </div>

    <div class="tab-content">
        <div class="tab-pane show active" id="store">

            <div class="d-flex" style="margin: 5px">
                <select v-model="selectedProductCategory" class="form-select w-auto" @change="loadProductsByCategory">
                    <option v-for="productCategory in productCategories"
                            :key="productCategory.id" :value="productCategory.id">
                        {{ productCategory.name }}
                    </option>
                </select>
                <button class="btn btn-primary"
                        @click="openNewProductCategoryModal()"
                        data-bs-toggle="modal"
                        data-bs-target="#newProductCategoryModal"
                >
                    +
                </button>
                <button class="btn btn-secondary"
                        @click="openEditProductCategoryModal(selectedProductCategory.id, selectedProductCategory.name)"
                        data-bs-toggle="modal"
                        data-bs-target="#editProductCategoryModal"
                >
                    редактировать
                </button>
                <button class="btn btn-danger"
                        @click="openDeleteProductCategoryModal()"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteProductCategoryModal"
                >
                    -
                </button>
            </div>

            <div id="productStocksItem"
                 v-for="product in productStocks"
                 :key="product.id"
                 @click="toggleProductButtons(product)"
                 :style="{border: product.isClicked ? '2px solid black' : 'white'}"
            >
                <div class="d-flex justify-content-between align-items-center">
                    <div>{{product.name }}</div>
                    <div>x{{product.quantity}}</div>
                    <div>{{product.base_sale_price}}р</div>
                </div>

                <div id="productStocksDeleteButtons" v-if="product.isClicked">
                    <button @click="openDeleteStoreProductModal(product.id)"
                            class="btn btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteStoreProductModal"
                    >
                        удалить
                        <BIconTrash icon="trash"></BIconTrash>
                    </button>
                    <button @click="openEditStoreProductModal(product.id, product.name, product.price)"
                            class="btn btn-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#editStoreProductModal"
                    >
                        редактировать
                        <BIconPencilSquare icon="pencil-square"></BIconPencilSquare>
                    </button>
                    <button @click="openArrivalProductModal(product.id, product.name, product.base_sale_price, product.quantity)"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#arrivalProductModal"
                    >
                        поступление
                    </button>
                </div>

            </div>

            <div id="productItem"
                 data-bs-toggle="modal"
                 data-bs-target="#newProductModal"
                 v-if="selectedProductCategory"
                 v-on:click=""
                 class="d-flex justify-content-center align-items-center"
                 style="background-color: #2C6EFC; color: white; text-align: center"
            >
                добавить товар
            </div>

        </div>
    </div>

    <div class="tab-content">
        <div class="tab-pane" id="clientChoice">
            <div id="clientItem" v-for="client in clients"
                 :key="client.id"
                 v-on:click="toggleClientsButtons(client)"
                 :style="{border: client.isClicked ? '2px solid black' : 'white'}"

            >
                <div class="d-flex justify-content-between align-items-center">
                    <div>{{client.name}}</div>
                    <div>{{client.phone}}</div>
                </div>

                <div id="clientsEditButtons" v-if="client.isClicked">
                    <button v-on:click="openDeleteClientModal(client.id)"
                            class="btn btn-danger"
                            data-bs-target="#deleteClientModal"
                            data-bs-toggle="modal"
                    >
                        удалить
                    </button>
                    <button v-on:click="openEditClientModal(client.id, client.name, client.phone)"
                            class="btn btn-primary"
                            data-bs-target="#editClientModal"
                            data-bs-toggle="modal"
                    >
                        редактировать
                    </button>
                </div>

            </div>

            <div id="clientItem"
                 data-bs-toggle="modal"
                 data-bs-target="#newClientModal"
                 v-if="selectedSpecializations"
                 v-on:click="openNewClientModal"
                 class="d-flex justify-content-center align-items-center"
                 style="background-color: #2C6EFC; color: white; text-align: center"
            >
                добавить нового клиента
            </div>

        </div>
    </div>

    <NewClientModal :selected-specialization="selectedSpecializations"
                    ref="newClientModal"
                    @client_added="handleClientAdded"
    />

    <DeleteClientModal ref="deleteClientModal"
                       @client-deleted="handleClientDeleted"
    />

    <EditClientModal ref="editClientModal"
                     @client-edited="handleClientEdited"
    />

    <NewServiceModal :selectedCategory="selectedCategory"
                     ref="newServiceModal"
                     @service-added="handleServiceAdded"
    ></NewServiceModal>

    <DeleteServiceModal ref="deleteServiceModal"
                        @service-deleted="handleServiceDeleted"
    />

    <EditServiceModal ref="editServiceModal"
                      @service-edited="handleServiceEdited"
    />

    <NewSpecializationModal ref="newSpecializationModal"
                            @specialization-added="handleSpecializationAdded"
    />

    <EditSpecializationModal ref="editSpecializationModal"
                             @specialization-edited="handleSpecializationEdited"
    />

    <DeleteSpecializationModal ref="deleteSpecializationModal"
                               @specialization-deleted="handleSpecializationDeleted"
    />

    <NewCategoryModal ref="newCategoryModal"
                      :selectedSpecialization="selectedSpecializations"
                      @category-added="handleCategoryAdded"
    />

    <EditCategoryModal ref="editCategoryModal"
                       @category-edited="handleCategoryEdited"
    />

    <DeleteCategoryModal ref="deleteCategoryModal"
                         @category-deleted="handleCategoryDeleted"
    />

    <NewProductCategoryModal ref="newProductCategoryModal"
                             :selectedSpecialization="selectedSpecializations"
                             @product-category-added="handleProductCategoryAdded"
    />

    <EditProductCategoryModal ref="editProductCategoryModal"
                              @prodact-category-edited="handleProductCategoryEdited"
    />

    <DeleteProductCategoryModal ref="deleteProductCategoryModal"
                                @product-category-deleted="handleProductCategoryDeleted"
    />

    <NewProductModal ref="newProductModal"
                          :selectedProductCategory="selectedProductCategory"
                          @product_added="handleProductAdded"
    />

    <DeleteStoreProductModal ref="deleteStoreProductModal"
                             @product_deleted="handleProductDeleted"
    />

    <ArrivalProductModal ref="arrivalProductModal"
                         @product_arrival_added="handleProductArrivalAdded"
    />

    <BAlert v-model="alertVisible" :variant="alertVariant" dismissible fade class="fixed-top">
      {{alertMessage}}
    </BAlert>

  </div>

</template>

<style scoped>

#serviceItem:hover {
  background-color: gray;
  color: white;
  cursor: pointer;
}

#serviceItem:active {
  background-color: red;
  color: white;
}

#clientItem:hover {
  background-color: #6c757d;
  color: white;
  cursor: pointer;
}

#clientItem:active {
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
</style>
