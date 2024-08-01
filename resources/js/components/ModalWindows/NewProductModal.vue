<script>
import axios from "axios";

export default {
  props: ['selectedProductCategory'],
  data(){
    return {
      isVisible: false,
      newProductNameInput: '',
      baseSalePrice: '',
    }
  },
  methods: {
    open(){
      this.isVisible = true
    },

    createNewProduct(){
      console.log('создаем новый товар')
      const requestData = {
        name: this.newProductNameInput,
        base_sale_price: this.baseSalePrice,
        product_category_id: this.selectedProductCategory,
      }
      console.log("данные на отправку" ,requestData)
      axios.post(this.$Url + '/api/add_product', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('product_added')
            this.newProductNameInput = ''
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
    <div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newProductModalLabel">Создание нового товара</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="createNewProduct">
                                <input id="newProductModelNameInput"
                                       class="form-control"
                                       v-model="newProductNameInput"
                                       placeholder="введите название"
                                       required
                                >
                                <input id="baseSalePrice"
                                       class="form-control"
                                       v-model="baseSalePrice"
                                       placeholder="цена продажи"
                                       required
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

</template>

<style scoped>

</style>
