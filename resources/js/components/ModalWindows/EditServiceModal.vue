<script>
import axios from "axios";

export default {
  data() {
    return {
      isVisible: false,
      serviceId: '',
      currentServiceName: '',
      currentServicePrice: '',
    }
  },

  methods: {
    open(serviceId, currentServiceName, currentServicePrice) {
      this.serviceId = serviceId
      this.currentServiceName = currentServiceName
      this.currentServicePrice = currentServicePrice
      this.isVisible = true
    },

    editService() {
      const requestData = {
        id: this.serviceId,
        service: this.currentServiceName,
        price: this.currentServicePrice,
      }
      axios.post('http://localhost:8000/api/edit_service', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('service-edited')
          })
      this.isVisible = false
    },

    closeModal() {
      this.isVisible = false
    }
  }
}
</script>

<template>
<div>
    <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"> Редактирование услуги </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="editService">
                        <input id="newServiceNameInput" class="form-control" v-model="currentServiceName">
                        <input id="newServicePriceInput" class="form-control" v-model="currentServicePrice">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" v-on:click="editService" data-bs-dismiss="modal">Изменить</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="model">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>

</style>
