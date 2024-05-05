<script>
import axios from "axios";

export default {
  data(){
    return {
      isVisible: false,
      newSpecializationNameInput: '',
    }
  },
  methods: {
    open(){
      this.isVisible = true
    },
    createNewSpecialization(){
      const requestsData = {
        specializationName: this.newSpecializationNameInput
      }
      axios.post('http://localhost:8000/add_specialization', requestsData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('specialization-added')
          })
      this.isVisible = false
    },
    closeModal() {
      this.isVisible = false
    },
  },
}
</script>

<template>
<div>

    <!-- Модальное окно -->
    <div class="modal fade" id="newSpecializationModal" tabindex="-1" aria-labelledby="newSpecializationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newSpecializationModalLabel">Создание новой специализации</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="createNewSpecialization">
                        <input id="newSpecializationNameInput"
                               v-model="newSpecializationNameInput"
                               placeholder="введите название специализации"
                               required
                               class="form-control"
                        >
                        <br>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
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
