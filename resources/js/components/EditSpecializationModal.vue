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
  <b-modal title="редактирование специализации" :visible="isVisible" @hidden="closeModal" hide-footer>
    <b-form @submit.stop.prevent="editSpecialization">
      редактируйте:
      <b-input id="newSpecializationNameInput"
               v-model="currentSpecializationName"
      ></b-input>
      <b-button @click="closeModal">отмена</b-button>
      <b-button type="submit" variant="primary">сохранить</b-button>
    </b-form>
  </b-modal>
</template>

<style scoped>

</style>