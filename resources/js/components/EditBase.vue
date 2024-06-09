<script>
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

    NewSpecializationModal,
    EditSpecializationModal,
    DeleteSpecializationModal,
  },

  data() {
    return {
      specializations: [],
      clients: [],
      categories: [],
      services: [],

      selectedSpecializations: '',
      selectedSpecializationName: '',
      selectedClients: '',
      selectedCategory: '',

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
            console.error('ошибка загрузки клиентов: ', error.message)
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
    <br>
    <div class="d-flex">
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

    <div class="container">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#serviceChoice" role="tab" data-bs-toggle="tab">УСЛУГИ</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#clientChoice" role="tab" data-bs-toggle="tab">КЛИЕНТЫ</a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane show active" id="serviceChoice">
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
            >
                добавить новую услугу
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
                 class="d-flex justify-content-center align-items-center">
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
                      @category-added="handleCategoryAdded"
    />

    <EditCategoryModal ref="editCategoryModal"
                       @category-edited="handleCategoryEdited"
    />

    <DeleteCategoryModal ref="deleteCategoryModal"
                         @category-deleted="handleCategoryDeleted"
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
}

.nav-link.active {
    background: #2c6efc;
    color: white;
}
</style>
