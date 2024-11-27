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
                  this.productCategories = response.data
                  if (this.productCategories.length > 0){
                      this.selectedProductCategory = this.productCategories[0].id
                      console.log('productCategories: ', this.productCategories)
                  }
              })
              .catch(error => {
                  console.error('Ошибка загрузки категорий: ', error.message)
              })
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
                        <VSelect :value="11"
                                 v-model="sfd"
                                 :options="equipmentModels"
                                 label="name"
                                 placeholder="выберите категорию..."
                                 @update:modelValue="handleSelectEquipmentModelChange"
                                 @open="adjustDropdownHeight"
                                 ref="vSelect"
                                 class="limited-height"
                        >
                            <template #no-options="{ search, noResults }">
                                <div class="no-options">
                                    <span>нет результатов...</span>
                                    <span>
                          <button type="button"
                                  class="btn btn-primary"
                                  data-bs-target="#newEquipmentModelModal"
                                  data-bs-toggle="modal" >
                              Добавить модель
                          </button>
                      </span>
                                </div>
                            </template>
                            <template #append-item-custom>
                                <div class="v-select__append-item">
                                    <span>Этот шаблон всегда виден</span>
                                </div>
                            </template>
                        </VSelect>

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
