import { a8 as defineStore } from "./index-74sOg8Nl.js";
import { api } from "./axios-D58jYJIV.js";
const useSpecializationsStore = defineStore("specializations", {
  state: () => ({
    specializations: [],
    selectedSpecialization: null
  }),
  getters: {
    getSelectedSpecialization: (state) => state.selectedSpecialization
  },
  actions: {
    async getSpecializations() {
      try {
        const token = localStorage.getItem("authToken");
        const response = await api.get("/get_specializations_by_user", {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        this.specializations = response.data;
        console.log("specializations: ", this.specializations);
        this.selectedSpecialization = this.specializations[0];
      } catch (err) {
        console.error("Ошибка получения специализаций: ", err);
      }
    },
    setSelectedSpecialization(specialization) {
      this.selectedSpecialization = specialization;
      console.log("применяемм выбранную спеиализацию: ", specialization);
      console.log("Изменение специализации на: ", this.selectedSpecialization);
    }
  }
});
export {
  useSpecializationsStore as u
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3BlY2lhbGl6YXRpb25zLUIwbGNaNjdELmpzIiwic291cmNlcyI6WyIuLi8uLi8uLi9zcmMvc3RvcmVzL3NwZWNpYWxpemF0aW9ucy5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgeyBkZWZpbmVTdG9yZSB9IGZyb20gXCJwaW5pYVwiO1xuaW1wb3J0IHsgYXBpIH0gZnJvbSBcImJvb3QvYXhpb3MuanNcIjtcblxuZXhwb3J0IGNvbnN0IHVzZVNwZWNpYWxpemF0aW9uc1N0b3JlID0gZGVmaW5lU3RvcmUoJ3NwZWNpYWxpemF0aW9ucycsIHtcbiAgc3RhdGU6ICgpID0+ICh7XG4gICAgc3BlY2lhbGl6YXRpb25zOiBbXSxcbiAgICBzZWxlY3RlZFNwZWNpYWxpemF0aW9uOiBudWxsXG4gIH0pLFxuICBnZXR0ZXJzOiB7XG4gICAgZ2V0U2VsZWN0ZWRTcGVjaWFsaXphdGlvbjogKHN0YXRlKSA9PiBzdGF0ZS5zZWxlY3RlZFNwZWNpYWxpemF0aW9uXG4gIH0sXG4gIGFjdGlvbnM6IHtcbiAgICBhc3luYyBnZXRTcGVjaWFsaXphdGlvbnMoKSB7XG4gICAgICB0cnkge1xuICAgICAgICBjb25zdCB0b2tlbiA9IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCdhdXRoVG9rZW4nKTtcbiAgICAgICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkuZ2V0KCcvZ2V0X3NwZWNpYWxpemF0aW9uc19ieV91c2VyJywge1xuICAgICAgICAgIGhlYWRlcnM6IHtcbiAgICAgICAgICAgIEF1dGhvcml6YXRpb246IGBCZWFyZXIgJHt0b2tlbn1gXG4gICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICAgICAgdGhpcy5zcGVjaWFsaXphdGlvbnMgPSByZXNwb25zZS5kYXRhO1xuICAgICAgICBjb25zb2xlLmxvZygnc3BlY2lhbGl6YXRpb25zOiAnLCB0aGlzLnNwZWNpYWxpemF0aW9ucyk7XG4gICAgICAgIHRoaXMuc2VsZWN0ZWRTcGVjaWFsaXphdGlvbiA9IHRoaXMuc3BlY2lhbGl6YXRpb25zWzBdXG4gICAgICB9IGNhdGNoIChlcnIpIHtcbiAgICAgICAgY29uc29sZS5lcnJvcign0J7RiNC40LHQutCwINC/0L7Qu9GD0YfQtdC90LjRjyDRgdC/0LXRhtC40LDQu9C40LfQsNGG0LjQuTogJywgZXJyKTtcbiAgICAgIH1cbiAgICB9LFxuICAgIHNldFNlbGVjdGVkU3BlY2lhbGl6YXRpb24oc3BlY2lhbGl6YXRpb24pIHtcbiAgICAgIHRoaXMuc2VsZWN0ZWRTcGVjaWFsaXphdGlvbiA9IHNwZWNpYWxpemF0aW9uO1xuICAgICAgY29uc29sZS5sb2coJ9C/0YDQuNC80LXQvdGP0LXQvNC8INCy0YvQsdGA0LDQvdC90YPRjiDRgdC/0LXQuNCw0LvQuNC30LDRhtC40Y46ICcsIHNwZWNpYWxpemF0aW9uKVxuICAgICAgY29uc29sZS5sb2coJ9CY0LfQvNC10L3QtdC90LjQtSDRgdC/0LXRhtC40LDQu9C40LfQsNGG0LjQuCDQvdCwOiAnLCB0aGlzLnNlbGVjdGVkU3BlY2lhbGl6YXRpb24pO1xuICAgIH1cbiAgfVxufSk7XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7QUFHWSxNQUFDLDBCQUEwQixZQUFZLG1CQUFtQjtBQUFBLEVBQ3BFLE9BQU8sT0FBTztBQUFBLElBQ1osaUJBQWlCLENBQUU7QUFBQSxJQUNuQix3QkFBd0I7QUFBQSxFQUM1QjtBQUFBLEVBQ0UsU0FBUztBQUFBLElBQ1AsMkJBQTJCLENBQUMsVUFBVSxNQUFNO0FBQUEsRUFDN0M7QUFBQSxFQUNELFNBQVM7QUFBQSxJQUNQLE1BQU0scUJBQXFCO0FBQ3pCLFVBQUk7QUFDRixjQUFNLFFBQVEsYUFBYSxRQUFRLFdBQVc7QUFDOUMsY0FBTSxXQUFXLE1BQU0sSUFBSSxJQUFJLGdDQUFnQztBQUFBLFVBQzdELFNBQVM7QUFBQSxZQUNQLGVBQWUsVUFBVSxLQUFLO0FBQUEsVUFDMUM7QUFBQSxRQUNBLENBQVM7QUFDRCxhQUFLLGtCQUFrQixTQUFTO0FBQ2hDLGdCQUFRLElBQUkscUJBQXFCLEtBQUssZUFBZTtBQUNyRCxhQUFLLHlCQUF5QixLQUFLLGdCQUFnQixDQUFDO0FBQUEsTUFDckQsU0FBUSxLQUFLO0FBQ1osZ0JBQVEsTUFBTSxvQ0FBb0MsR0FBRztBQUFBLE1BQzdEO0FBQUEsSUFDSztBQUFBLElBQ0QsMEJBQTBCLGdCQUFnQjtBQUN4QyxXQUFLLHlCQUF5QjtBQUM5QixjQUFRLElBQUksdUNBQXVDLGNBQWM7QUFDakUsY0FBUSxJQUFJLGdDQUFnQyxLQUFLLHNCQUFzQjtBQUFBLElBQzdFO0FBQUEsRUFDQTtBQUNBLENBQUM7In0=
