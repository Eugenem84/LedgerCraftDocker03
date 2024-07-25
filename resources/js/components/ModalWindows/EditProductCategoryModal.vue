<script>
import axios from "axios";

export default {
  data(){
    return {
      isVisible: false,
      productCategoryId: '',
      currentProductCategoryName: '',
    }
  },

  methods: {
    open(categoryId, currentCategoryName){
      console.log(categoryId)
      this.productCategoryId = categoryId
      this.currentProductCategoryName = currentCategoryName
      this.isVisible = true
    },

    editProductCategory(){
      const requestData = {
        id: this.productCategoryId,
        name: this.currentProductCategoryName,
      }
      console.log(requestData)
      axios.post(this.$Url + '/api/edit_product_category', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('product-category-edited')
          })
      this.isVisible = false
    },

    closModal(){
      this.isVisible = false
    }
  }
}
</script>

<template>

    <div class="modal fade" id="editProductCategoryModal" tabindex="-1" aria-labelledby="editProductCategoryModalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"> Редактирование Категории Продукта </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="editProductCategory">
                        <input id="newSpecializationNameInput" class="form-control" v-model="currentProductCategoryName">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" v-on:click="editProductCategory" data-bs-dismiss="modal">Изменить</button>

                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>

    </div>

</template>

<style scoped>

</style>
