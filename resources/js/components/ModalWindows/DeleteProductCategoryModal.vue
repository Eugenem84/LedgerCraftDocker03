<script>
import axios from "axios";

export default {
  data(){
    return {
      isVisible: false,
      productCategoryId: ''
    }
  },

  methods: {
    open(productCategoryId){
      this.productCategoryId = productCategoryId
      this.isVisible = true
    },

    deleteCategory(){
      const requestData = {
        productCategoryId: this.productCategoryId

      }
      console.log(requestData)
      axios.post(this.$Url + '/api/delete_product_category', requestData)
          .then(response => {
            this.$emit('product-category-deleted')
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
<div class="modal fade" id="deleteProductCategoryModal" tabindex="-1" aria-labelledby="deleteProductCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteProductCategoryModalLabel"> Удвление категории </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="закрыть"></button>
            </div>
            <div class="modal-body">

                    <div> Удалить категорию? </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" v-on:click="deleteCategory">Удалить</button>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>

</style>
