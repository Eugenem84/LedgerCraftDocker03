<script>
import TestModal from "./ModalWindows/TestModal.vue";
export default {

    components: {
        TestModal,
    },

    data(){
        return {
            someId: 5,
        }
    },

    methods: {
      openModal(){
          console.log('открываем модальное окно')
          this.$refs.TestModal.displayId(this.someId)
      },

      openModal2(){
          this.$refs.TestModal.isModal2Open();
      }

    },
}
</script>

<template>
<div>
    Тут будет статистика

<!--    <button class="btn btn-primary" v-on:click="openModal" >open modal</button>-->

    <TestModal ref="TestModal" ></TestModal>

    <button type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#exampleModal"
    >
        Launch demo modal
    </button>

    <button v-on:click="openModal2" type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#exampleModal2"
    >
        Launch demo modal 2
    </button>

</div>
</template>

<style scoped>

</style>
