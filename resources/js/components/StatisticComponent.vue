<script>
import TestModal from "./ModalWindows/TestModal.vue";
import VSelect from 'vue3-select'
import axios from "axios";
import {th} from "vuetify/locale";
export default {

    components: {
        TestModal,
        VSelect,
    },

    data(){
        return {
            someId: 5,
            selectedOptions: null,
            options: ['var1', 'var2'],
            totalDWMY: '',
            topServices: '',
            topProfitClients: '',
            specializations: [],
            selectedSpecialization: ''
        }
    },

    async mounted() {
       await this.loadSpecializations()
       console.log('грузка специализаций завершена')
       await new Promise(resolve => setTimeout(resolve, 1000))
       await this.getTotalDWMY()
       console.log('загрузка статистики завершена')
       this.getTopServicesBySpecialization()
       this.getTopProfitClients()
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
      },

        async loadSpecializations (){
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            axios.get(this.$Url +'/get_all_specializations', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    //'Authorization': `Bearer ${laravelSessionToken}`
                }
            })
                .then(response => {
                    this.specializations = response.data
                    console.log('список специализаций: ', this.specializations )

                    if (this.specializations.length > 0) {
                        this.selectedSpecialization = this.specializations[0]
                        console.log('выбрана специализация: ' , this.selectedSpecialization)
                        // this.loadClients()
                        // this.loadCategories()
                        //this.loadServicesByCategory()
                    }

                })
                .catch(eError => {
                    console.error(eError.message)
                })
        },

        async getTotalDWMY(){
          axios.get( this.$Url + `/api/get_total_DWYM/${this.selectedSpecialization.id}`)
              .then(response => {
                  //console.log(response.data)
                  this.totalDWMY = response.data
                  console.log("totalDWMY: ", this.totalDWMY)
                  console.log("Заработано за год: ", this.totalDWMY[0].total_year)
              })
              .catch(err => {
                  console.error('ошибка получения статистики: ', err)
              })
      },
        getTopServicesBySpecialization(){
          axios.get(this.$Url + `/api/get_top_services/${this.selectedSpecialization.id}`)
              .then(response => {
                  this.topServices = response.data
                  console.log('топ 10 сервисов: ', response.data)
              })
              .catch(err => {
                  console.error('ошибка получения топ сервисов: ', err)
              })
        },

        getTopProfitClients(){
          axios.get(this.$Url + `/api/get_top_profit_clients/${this.selectedSpecialization.id}`)
              .then(response => {
                  this.topProfitClients = response.data
                  console.log('топ 10 доходных клиентов: ', response.data)
              })
              .catch(err => {
                  console.error('ошибка получения топ доходных клиентов: ', err)
              })
        },

        handleSelectedSpecializationChange(){
            if (this.selectedSpecialization === 'create_new_specialization'){
                this.openNewSpecializationModal()
            } else {
                console.log('выбрана специализация: ', this.selectedSpecialization )
                this.getTotalDWMY()
                this.getTopServicesBySpecialization()
                //this.loadClients()
                //this.loadCategories()
            }
            console.log('выбрана специализация: ', this.selectedSpecialization)
        },

    },
}
</script>

<template>
<div>
    <VSelect v-model="selectedSpecialization"
             :options="specializations"
             label="specializationName"
             @update:modelValue="handleSelectedSpecializationChange"
    >

    </VSelect>
    <br>
    <div v-if="totalDWMY">
        <div>Доход за день: <input v-model="totalDWMY[0].total_day" disabled></div>
        <div>Доход за неделю: <input v-model="totalDWMY[0].total_week" disabled> </div>
        <div>Доход за месяц: <input v-model="totalDWMY[0].total_month" disabled> </div>
        <div>Доход за год: <input v-model="totalDWMY[0].total_year" disabled> </div>
    </div>
    <div v-else>
        Загрузка данных...
    </div>
    <br>
    <div style="text-align: center;">Самые популярные работы: </div>
    <div class="d-flex justify-content-between align-items-center">
        <div class="underline">название</div>
        <div class="underline">количество</div>
        <div class="underline">цена</div>
        <div class="underline">общая сумма</div>
    </div>
    <div id="serviceItem" v-for="service in topServices" >
        <div class="d-flex justify-content-between align-items-center">
            <div>{{service.service }}</div>
            <div>{{service.service_count}}</div>
            <div>{{service.price}}</div>
            <div>{{service.total}}</div>
        </div>
    </div>
    <br>
    <div style="text-align: center;">Самые доходные клиенты: </div>
    <div class="d-flex justify-content-between align-items-center">
        <div class="underline">клиент</div>
        <div class="underline">количество заказов</div>
        <div class="underline">общая сумма</div>
    </div>
    <div id="clientItem" v-for="client in topProfitClients" >
        <div class="d-flex justify-content-between align-items-center">
            <div>{{client.name }}</div>
            <div>{{client.num_ord}}</div>
            <div>{{client.total_amount}}</div>
        </div>
    </div>

</div>
</template>

<style scoped>
.underline {
    text-decoration: underline;
}
</style>
