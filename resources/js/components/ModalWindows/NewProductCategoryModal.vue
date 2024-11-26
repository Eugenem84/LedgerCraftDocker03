<script>
import axios from "axios";

export default {
  props: ['selectedSpecialization'],
  data(){
    return {
      isVisible: false,
      newProductCategoryNameInput: '',
      specializationId: ''
    }
  },
  methods: {
    open(selectedSpecialization){
      this.isVisible = true
      this.specializationId = selectedSpecialization
    },

    createNewProductCategory(){
      console.log('specializationID: ', this.specializationId)
      const requestData = {
        name: this.newProductCategoryNameInput,
        specialization_id: this.selectedSpecialization,
      }
      console.log(requestData)
      axios.post(this.$Url + '/api/add_product_category', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('product-category-added')
            this.newProductCategoryNameInput= ''
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
    <div class="modal fade" id="newProductCategoryModal" tabindex="-1" aria-labelledby="newProductCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newProductCategoryModalLabel">Создание новой продуктовой категории</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="createNewProductCategory">
                        <input id="newProductCategoryNameInput"
                               class="form-control"
                               v-model="newProductCategoryNameInput"
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
