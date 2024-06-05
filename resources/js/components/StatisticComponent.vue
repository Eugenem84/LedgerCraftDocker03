<script>
import TestModal from "./ModalWindows/TestModal.vue";
import VSelect from 'vue3-select'
export default {

    components: {
        TestModal,
        VSelect,
    },

    data(){
        return {
            someId: 5,
            selectedOptions: null,
            options: ['var1', 'var2']
        }
    },

    methods: {

        async fetchData(query) {
            const response = await axios.get('/api/data', { params: { query } });
            const options = response.data.map((item) => ({
                value: item.id,
                label: item.name,
            }));
            this.options = options;
        },

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
    <VSelect v-model="selectedOptions" :options="options">

    </VSelect>

</div>
</template>

<style scoped>

</style>
