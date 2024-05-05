<script>
import axios from "axios";

export default {
  data(){
    return {
      isVisible: false,
      clientId: '',
      currentClientName: '',
      currentClientPhone: '',
    }
  },
  methods: {
    open(clientId, currentClientName, currentClientPhone){
      this.clientId = clientId
      this.currentClientName = currentClientName
      this.currentClientPhone = currentClientPhone
      this.isVisible = true
    },

    editClient() {
      const requestData = {
        id: this.clientId,
        name: this.currentClientName,
        phone: this.currentClientPhone,
      }
      console.log(requestData)
      axios.post('http://localhost:8000/api/edit_client', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('client-edited')
          })
      this.isVisible = false
    },

    closeModal(){
      this.isVisible = false
    }
  }
}
</script>

<template>

    <div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"> Редактирование клиента </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="editClient">
                        <input id="newServiceNameInput" class="form-control" v-model="currentClientName">
                        <input id="newServicePriceInput" class="form-control" v-model="currentClientPhone">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" v-on:click="editClient" data-bs-dismiss="modal">Изменить</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="model">Отмена</button>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>
