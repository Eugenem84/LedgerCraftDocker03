<script>
import axios from "axios";

export default {
  props: [ 'productId', 'productName', 'baseSalePrice'],
  data(){
    return {
      isVisible: false,
      productName: '',
      productId: '',
      baseSalePrice: '',
      byPrice: '',
      arrivalQuantity: '',
    }
  },
  methods: {
    open(productId, productName, baseSalePrice) {
      this.isVisible = true
      this.productId = productId
      this.productName = productName
      this.baseSalePrice = baseSalePrice
    },

    arrivalProduct(){
      console.log('приходуем товар')
      const requestData = {
        product_id: this.productId,
        base_sale_price: this.baseSalePrice,
        by_price: this.byPrice,
        arrival_quantity: this.arrivalQuantity,
      }
      console.log("данные на отправку" ,requestData)
      // Задача 11.7: ручка под `auth:sanctum`. Web-часть входит по сессии
      // (`Auth::routes()`), поэтому шлём cookie (`withCredentials`) и CSRF-токен из
      // meta-тега — запрос проходит как «first-party» (Sanctum), без bearer-токена.
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      axios.post(this.$Url + '/api/arrival_product', requestData, {
        withCredentials: true,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
      })
          .then(response => {
            console.log(response.data.message)
            this.$emit('product_arrival_added')
            this.arrivalQuantity = ''
            this.byPrice = ''
          })
          .catch(error => {
            // 401 — сессия истекла, 403 — чужой товар: не молчим в консоли.
            const reason = error.response?.data?.message || error.message
            console.error('приход товара не сохранён:', reason)
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
    <div class="modal fade" id="arrivalProductModal" tabindex="-1" aria-labelledby="arrivalProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="arrivalProductModalLabel">Поступление "{{this.productName}}"</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="arrivalProduct">
                                <input class="form-control"
                                       v-model="byPrice"
                                       type="number"
                                       placeholder="цена закупки"
                                       required
                                >
                                <input id="baseSalePriceInput"
                                       class="form-control"
                                       v-model="baseSalePrice"
                                       type="number"
                                       placeholder="цена продажи"
                                       required
                                >
                                <input id="quantityInput"
                                       class="form-control"
                                       type="number"
                                       v-model="arrivalQuantity"
                                       placeholder="количество"
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
