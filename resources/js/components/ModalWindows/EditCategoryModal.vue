<script>
import axios from "axios";

export default {
  data(){
    return {
      isVisible: false,
      categoryId: '',
      currentCategoryName: '',
    }
  },

  methods: {
    open(categoryId, currentCategoryName){
      console.log(categoryId)
      this.categoryId = categoryId
      this.currentCategoryName = currentCategoryName
      this.isVisible = true
    },

    editCategory(){
      const requestData = {
        id: this.categoryId,
        category_name: this.currentCategoryName,
      }
      //console.log(requestData)
      axios.post(this.$Url + '/api/edit_category', requestData)
          .then(response => {
            console.log(response.data.message)
            this.$emit('category-edited')
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

    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"> Редактирование Категории </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form @submit.stop.prevent="editCategory">
                        <input id="newSpecializationNameInput" class="form-control" v-model="currentCategoryName">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" v-on:click="editCategory" data-bs-dismiss="modal">Изменить</button>

                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>

    </div>

</template>

<style scoped>

</style>
