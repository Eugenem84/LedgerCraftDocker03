<script>
import axios from "axios";
import VSelect from "vue3-select";

export default {
    components: {
        VSelect
    },
  props: ['specializationId'],
  data(){
    return {
        isModalOpen: false,
        categories: [],
        selectedProductCategory: null,
        products: [],
        selectedProduct: null,
        isLoadingProductCategories: false,
        isLoadingProducts: false,
        selectedSpecialization: null
    }
  },

    // mounted() {
    //     // Подписка на событие через API Bootstrap 5
    //     const modalElement = document.getElementById('addProductFromStoreModal');
    //     modalElement.addEventListener('shown.bs.modal', this.loadProductCategories);
    // },

    methods: {

        open(specializationId){
            console.log('открылось модальное окно для специализации: ', specializationId)
            this.selectedSpecialization = specializationId
            this.loadProductCategories()
        },

      loadProductCategories(){
          console.log('specializationId: ', this.selectedSpecialization);
          return axios.get(this.$Url + `/api/get_product_categories/${this.selectedSpecialization}`)
              .then(response => {
                  this.categories = response.data
                  if (this.categories.length > 0){
                      this.selectedProductCategory = this.categories[0].id
                      console.log('productCategories: ', this.categories)
                  }
                  this.loadProductsByCategory()
              })
              .catch(error => {
                  console.error('Ошибка загрузки категорий: ', error.message)
              })
      },

      loadProductsByCategory(){
            console.log('load products by category')
            axios.get(this.$Url + `/api/get_product_stocks/${this.selectedProductCategory}`)
                .then(response => {
                    console.log('товары: ', response.data)
                    this.products = response.data
                })
                .catch(error => {
                    console.log('ошибка загрузки товаров: ', error)
                })
      },

      addProductToOrder(){
        console.log('addProductToOrder...')
        this.$emit('material-from-store-added', this.selectedProduct)
      },

  }
}
</script>

<template>
    <!-- Модальное окно -->
    <div class="modal fade" id="addProductFromStoreModal" tabindex="-1" aria-labelledby="addProductFromStoreModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="arrivalProductModalLabel">Добавление товара со склада</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="addProductFromStore">
                        <VSelect
                                 v-model="selectedProductCategory"
                                 :options="categories"
                                 :reduce="category => category.id"
                                 label="name"
                                 placeholder="выберите категорию..."
                                 ref="vSelect"
                                 class="limited-height"
                        >
                            <template #no-options="{ search, noResults }">
                                <div class="no-options">
                                    <span>нет результатов...</span>
                                </div>
                            </template>

                        </VSelect>
                        <br>
                        <VSelect v-model="selectedProduct"
                                 :options="products"
                                 :reduce="product => product"
                                 label="name"
                                 placeholder="выберите товар"
                                 ref="VSelect"
                                 class="limited-height"
                        >
                            <template #no-options="{ search, noResults }">
                                <div class="no-options">
                                    <span>нет результатов...</span>
                                </div>
                            </template>
                        </VSelect>

                                <br>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit"
                                    class="btn btn-primary"
                                    data-bs-dismiss="modal"
                                    @click="addProductToOrder()"
                            >Добавить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>
