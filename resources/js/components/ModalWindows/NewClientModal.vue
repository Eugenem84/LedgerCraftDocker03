<script>
import axios from "axios";

export default {
  props: ['selectedSpecialization'],
  data(){
    return {
      isVisible: false,
      newClientNameInput: '',
      newClientPhoneInput: '',
    }
  },
  methods: {
    open(){
      this.isVisible = true
    },

    createNewClient(){
        console.log('создаем нового клиента')
      const requestData = {
        name: this.newClientNameInput,
        phone: this.newClientPhoneInput,
        specialization_id: this.selectedSpecialization,
      }
      axios.post('http://localhost:8000/api/add_client', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('client_added')
            this.newClientNameInput = ''
            this.newClientPhoneInput = ''
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
    <!-- Модальное окно -->
    <div class="modal fade" id="newClientModal" tabindex="-1" aria-labelledby="newClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newClientModalLabel">Создание нового клиента</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="createNewClient">
                                <input id="newClientNameInput"
                                       class="form-control"
                                       v-model="newClientNameInput"
                                       placeholder="введите имя клиентя"
                                       required
                                >
                                <br>
                                <input id="newClientPhoneInput"
                                       class="form-control"
                                       v-model="newClientPhoneInput"
                                       placeholder="введите телефон"
                                       required
                                >
                                <br>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Сохранить изменения</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>
