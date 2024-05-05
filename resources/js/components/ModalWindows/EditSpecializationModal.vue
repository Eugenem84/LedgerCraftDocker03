<script>
import axios from "axios";

export default {
  data(){
    return {
      isVisible: false,
      specializationId: '',
      currentSpecializationName: '',
    }
  },

  methods: {
    open(specializationId, currentSpecializationName){
      this.specializationId = specializationId
      this.currentSpecializationName = currentSpecializationName
      this.isVisible = true
    },

    editSpecialization(){
      const requestData = {
        id: this.specializationId,
        specializationName: this.currentSpecializationName
      }
      axios.post('http://localhost:8000/api/edit_specialization', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('specialization-deleted')
          })
      this.isVisible = false
    },

    closeModal(){
      this.isVisible = false
    },
  }
}
</script>

<template>
<div class="modal fade" id="editSpecializationModal" tabindex="-1" aria-labelledby="editSpecializationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5"> Редактирование специализации </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form @submit.stop.prevent="editSpecialization">
                    <input id="newSpecializationNameInput" class="form-control" v-model="currentSpecializationName">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" v-on:click="editSpecialization" data-bs-dismiss="modal">Изменить</button>

                <button type="button" class="btn btn-danger" data-bs-dismiss="model">Отмена</button>
            </div>
        </div>
    </div>

</div>

</template>

<style scoped>

</style>
