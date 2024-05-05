<script>
import axios from "axios";

export default {
  data(){
    return {
      isVisible: false,
      clientId: ''
    }
  },
  methods: {
    open(clientId){
      this.clientId = clientId
      this.isVisible = true
    },

    deleteClient() {
      const requestData = {
        clientId: this.clientId,
      }
      console.log('удаление клиента с Id: ', requestData)
      axios.post('http://localhost:8000/api/delete_client', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('client-deleted')
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


    <div>
        <div class="modal fade" id="deleteClientModal" tabindex="-1" aria-labelledby="deleteClientModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteClientModalLabel"> Удвление Услуги </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div> Удалить клтиента? </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" v-on:click="deleteClient">Удалить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!--  <b-modal title="удаление клиента" :visible="isVisible" @hidden="closeModal" hide-footer>-->
<!--    <b-form @submit.stop.prevent="deleteClient">-->
<!--      Удалить клиента?-->
<!--      <b-button @click="closeModal">отмена</b-button>-->
<!--      <b-button type="submit" variant="primary">удалить</b-button>-->
<!--    </b-form>-->

<!--  </b-modal>-->

</template>

<style scoped>

</style>
