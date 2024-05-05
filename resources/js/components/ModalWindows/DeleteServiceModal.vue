<script>
import axios from "axios";
//import {error} from "@babel/eslint-parser/lib/convert";

export default {
  data(){
    return {
      isVisible: false,
      serviceId: '',
    }
  },
  methods: {
    open(serviceId) {
      this.serviceId = serviceId
      this.isVisible = true
    },

    deleteService() {
      const requestData = {
        serviceId: this.serviceId
      }
      console.log(requestData)
      axios.post('http://localhost:8000/api/delete_service', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('service-deleted')
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
    <div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteServiceModalLabel"> Удвление Услуги </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="закрыть"></button>
                </div>
                <div class="modal-body">
                    <div> Удалить Услугу? </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" v-on:click="deleteService">Удалить</button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>

</style>
