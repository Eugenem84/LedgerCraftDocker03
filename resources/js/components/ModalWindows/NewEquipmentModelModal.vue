<script>
import axios from "axios";

export default {
  props: ['selectedSpecialization'],
  data(){
    return {
      isVisible: false,
      newEquipmentModelNameInput: '',
    }
  },
  methods: {
    open(){
      this.isVisible = true
    },

    createNewEquipmentModel(){
        console.log('создаем новую модель')
      const requestData = {
        name: this.newEquipmentModelNameInput,
        specialization_id: this.selectedSpecialization,
      }
      axios.post(this.$Url + '/api/add_equipment_model', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('equipment_model_added')
            this.newEquipmentModelNameInput = ''
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
    <div class="modal fade" id="newEquipmentModelModal" tabindex="-1" aria-labelledby="newEquipmentModelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newEquipmentModelModalLabel">Создание новой модели</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="createNewEquipmentModel">
                                <input id="newEquipmentModelNameInput"
                                       class="form-control"
                                       v-model="newEquipmentModelNameInput"
                                       placeholder="введите название"
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
