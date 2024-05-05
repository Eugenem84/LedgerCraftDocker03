<script>
import axios from "axios";

export default {
  props: ['selectedSpecialization'],
  data(){
    return {
      isVisible: false,
      newCategoryNameInput: '',
      specializationId: ''
    }
  },
  methods: {
    open(selectedSpecialization){
      this.isVisible = true
      this.specializationId = selectedSpecialization
    },

    createNewCategory(){
      console.log('specializationID: ', this.specializationId)
      const requestData = {
        category_name: this.newCategoryNameInput,
        specialization_id: this.specializationId,
      }
      console.log(requestData)
      axios.post('http://localhost:8000/api/add_category', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('category-added')
            this.newCategoryNameInput= ''
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
    <div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newCategoryModalLabel">Создание новой категории</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="createNewCategory">
                        <input id="newCategoryNameInput"
                               class="form-control"
                               v-model="newCategoryNameInput"
                               placeholder="введите название категории"
                               required
                        >
                        <br>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" >Сохранить изменения</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>
