<script>
import axios from "axios";
//import {error} from "@babel/eslint-parser/lib/convert";

export default {
  data(){
    return {
      isVisible: false,
      productId: '',
    }
  },
  methods: {
    open(productId) {
      this.productId = productId
      this.isVisible = true
    },

    deleteStoreProduct() {
      const requestData = {
        productId: this.productId
      }
      console.log(requestData)
      axios.post( this.$Url +'/api/delete_store_product', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('product_deleted')
          })
      this.isVisible = false
    },

    closeModal() {
      this.isVisible = false
    }
  }
}
</script>

<template>
<div>
    <div class="modal fade" id="deleteStoreProductModal" tabindex="-1" aria-labelledby="deleteStoreProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteStoreProductModalLabel"> Удаление Товара </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="закрыть"></button>
                </div>
                <div class="modal-body">
                    <div> Удалить товар {{this.productId}}? </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" v-on:click="deleteStoreProduct">Удалить</button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>

</style>
