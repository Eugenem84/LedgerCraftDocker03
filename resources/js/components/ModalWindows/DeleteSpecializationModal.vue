<script>
import axios from "axios";

export default {
  data(){
    return {
      isVisible: false,
      specializationId: '',
    }
  },
  methods: {
    open(specializationId){
      this.specializationId = specializationId
      this.isVisible = true
    },

    deleteSpecialization(){
      console.log('Удаляем специализацию: ', this.specializationId)
      const requestData = {
        specializationId: this.specializationId,
      }
      axios.post('http://localhost:8000/api/delete_specialization', requestData)
          .then(response => {
            this.$emit('specialization-deleted')
            console.log(response.data.message)
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
        <div class="modal fade" id="deleteSpecializationModal" tabindex="-1" aria-labelledby="deleteSpecializationModalModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteSpecializationModalLabel"> Удвление специализации </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div> Удалить специализацию? </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" v-on:click="deleteSpecialization">Удалить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
