import { a8 as defineStore, r as ref } from "./index-74sOg8Nl.js";
const useOrderStore = defineStore("order", () => {
  const currentOrder = ref(null);
  const setOrder = (order) => {
    currentOrder.value = order;
  };
  const clearCurrentOrder = () => {
    currentOrder.value = null;
  };
  return {
    currentOrder,
    setOrder,
    clearCurrentOrder
  };
});
export {
  useOrderStore as u
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoib3JkZXItQ2dCaGsxXzkuanMiLCJzb3VyY2VzIjpbIi4uLy4uLy4uL3NyYy9zdG9yZXMvb3JkZXIuanMiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IHsgZGVmaW5lU3RvcmUgfSBmcm9tIFwicGluaWFcIjtcbmltcG9ydCB7IHJlZiB9IGZyb20gJ3Z1ZSdcblxuZXhwb3J0IGNvbnN0IHVzZU9yZGVyU3RvcmUgPSBkZWZpbmVTdG9yZSgnb3JkZXInLCAoKSA9PiB7XG4gIGNvbnN0IGN1cnJlbnRPcmRlciA9IHJlZihudWxsKVxuXG4gIGNvbnN0IHNldE9yZGVyID0gKG9yZGVyKSA9PiB7XG4gICAgY3VycmVudE9yZGVyLnZhbHVlID0gb3JkZXJcbiAgfVxuXG4gIGNvbnN0IGNsZWFyQ3VycmVudE9yZGVyID0gKCkgPT4ge1xuICAgIGN1cnJlbnRPcmRlci52YWx1ZSA9IG51bGxcbiAgfVxuXG4gIHJldHVybiB7XG4gICAgY3VycmVudE9yZGVyLFxuICAgIHNldE9yZGVyLFxuICAgIGNsZWFyQ3VycmVudE9yZGVyXG4gIH1cbn0pXG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IjtBQUdZLE1BQUMsZ0JBQWdCLFlBQVksU0FBUyxNQUFNO0FBQ3RELFFBQU0sZUFBZSxJQUFJLElBQUk7QUFFN0IsUUFBTSxXQUFXLENBQUMsVUFBVTtBQUMxQixpQkFBYSxRQUFRO0FBQUEsRUFDekI7QUFFRSxRQUFNLG9CQUFvQixNQUFNO0FBQzlCLGlCQUFhLFFBQVE7QUFBQSxFQUN6QjtBQUVFLFNBQU87QUFBQSxJQUNMO0FBQUEsSUFDQTtBQUFBLElBQ0E7QUFBQSxFQUNKO0FBQ0EsQ0FBQzsifQ==
