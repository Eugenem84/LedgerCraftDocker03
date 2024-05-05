<script>
import axios from "axios";

export default {
  props: ['selectedCategory'],
  data(){
    return {
      isVisible: false,
      newServiceNameInput: '',
      newServicePriceInput: '',
      selectedCategory: null,
    }
  },
  methods:{
    open(){
      this.isVisible = true
      this.clearInputs()
    },

    clearInputs(){
      this.newServicePriceInput = ''
      this.newServiceNameInput = ''
    },

    createNewService(){
      const requestData = {
        service: this.newServiceNameInput,
        price: this.newServicePriceInput,
        category_id: this.selectedCategory,
      }
      axios.post('http://localhost:8000/api/add_service', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('service-added')
          })
      this.isVisible = false
    },
    closeModal(){
        this.isVisible = false
    }
  },
}

</script>

<template>
  <div>

      <!-- Модальное окно -->
      <div class="modal fade" id="newServiceModal" tabindex="-1" aria-labelledby="newServiceModalLabel" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <h1 class="modal-title fs-5" id="newServiceModalLabel">Новая услуга</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                  </div>
                  <div class="modal-body">
                      <form @submit.stop.prevent="createNewService">
                          <input id="newServiceNameInput"
                                 class="form-control"
                                 v-model="newServiceNameInput"
                                 placeholder="введите название услуги"
                                 required
                          >
                          <br>
                          <input id="newServicePriceInput"
                                 class="form-control"
                                 v-model="newServicePriceInput"
                                 placeholder="введите цену"
                                 type="number"
                                 required
                          >
                          <br>

                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                              <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Сохранить</button>
                          </div>

                      </form>
                  </div>
              </div>
          </div>
      </div>
  </div>
</template>

<style scoped>

</style>
