import { _ as _export_sfc, r as ref, J as useRouter, a3 as useAuthStore, D as openBlock, E as createBlock, F as withCtx, G as createVNode, $ as QCardSection, R as createBaseVNode, a0 as QInput, a1 as QCardActions, O as QBtn, Z as QCard } from "./index-74sOg8Nl.js";
import { Q as QPage } from "./QPage-B-LnxX2q.js";
import { api } from "./axios-D58jYJIV.js";
import { u as useQuasar } from "./use-quasar-DEOs6wIv.js";
const _sfc_main = {
  __name: "LoginPage",
  setup(__props, { expose: __expose }) {
    __expose();
    const email = ref("");
    const password = ref("");
    const loading = ref(false);
    const router = useRouter();
    const $q = useQuasar();
    const authStore = useAuthStore();
    const login = async () => {
      loading.value = true;
      try {
        $q.notify({
          type: "positive",
          message: `trying send URL: ${api.defaults.baseURL} `,
          position: "top"
        });
        const response = await api.post("/login", {
          email: email.value,
          password: password.value
        });
        authStore.setToken(response.data.access_token);
        router.push("/orders");
        console.log("проверка хранилища pinia: ", authStore.token);
      } catch (err) {
        $q.notify({
          type: "negative",
          message: "ошибка входа: " + err.message,
          position: "top",
          timeout: "1000"
        });
        console.error("ошибка входа: ", err);
      } finally {
        loading.value = false;
      }
    };
    const __returned__ = { email, password, loading, router, $q, authStore, login, ref, get useAuthStore() {
      return useAuthStore;
    }, get api() {
      return api;
    }, get useRouter() {
      return useRouter;
    }, get useQuasar() {
      return useQuasar;
    } };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createBlock(QPage, { class: "flex flex-center" }, {
    default: withCtx(() => [
      createVNode(QCard, {
        class: "q-pa-md",
        style: { "width": "300px" }
      }, {
        default: withCtx(() => [
          createVNode(QCardSection, null, {
            default: withCtx(() => _cache[2] || (_cache[2] = [
              createBaseVNode("div", { class: "text-h6" }, "Вход", -1)
            ])),
            _: 1
          }),
          createVNode(QCardSection, null, {
            default: withCtx(() => [
              createVNode(QInput, {
                modelValue: $setup.email,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.email = $event),
                label: "Email",
                type: "email",
                outlined: "",
                class: "q-md-md"
              }, null, 8, ["modelValue"]),
              createVNode(QInput, {
                modelValue: $setup.password,
                "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.password = $event),
                label: "Пароль",
                type: "password",
                outlined: ""
              }, null, 8, ["modelValue"])
            ]),
            _: 1
          }),
          createVNode(QCardActions, { align: "right" }, {
            default: withCtx(() => [
              createVNode(QBtn, {
                label: "Войти",
                color: "primary",
                onClick: $setup.login,
                loading: $setup.loading
              }, null, 8, ["loading"])
            ]),
            _: 1
          })
        ]),
        _: 1
      })
    ]),
    _: 1
  });
}
const LoginPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render], ["__file", "LoginPage.vue"]]);
export {
  LoginPage as default
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiTG9naW5QYWdlLURSV1V6RlVVLmpzIiwic291cmNlcyI6WyIuLi8uLi8uLi9zcmMvcGFnZXMvYXV0aC9Mb2dpblBhZ2UudnVlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQgc2V0dXA+XHJcblxyXG5pbXBvcnQgeyByZWYgfSBmcm9tICd2dWUnXHJcbmltcG9ydCB7IHVzZUF1dGhTdG9yZX0gZnJvbSBcInN0b3Jlcy9hdXRoLXN0b3JlLmpzXCI7XHJcbmltcG9ydCB7IGFwaSB9IGZyb20gXCJib290L2F4aW9zLmpzXCI7XHJcbmltcG9ydCB7dXNlUm91dGVyfSBmcm9tIFwidnVlLXJvdXRlclwiO1xyXG5pbXBvcnQge3VzZVF1YXNhcn0gZnJvbSBcInF1YXNhclwiO1xyXG5cclxuY29uc3QgZW1haWwgPSByZWYoJycpXHJcbmNvbnN0IHBhc3N3b3JkID0gcmVmKCcnKVxyXG5jb25zdCBsb2FkaW5nID0gcmVmKGZhbHNlKVxyXG5cclxuY29uc3Qgcm91dGVyID0gdXNlUm91dGVyKClcclxuY29uc3QgJHEgPSB1c2VRdWFzYXIoKVxyXG5cclxuY29uc3QgYXV0aFN0b3JlID0gdXNlQXV0aFN0b3JlKClcclxuXHJcbmNvbnN0IGxvZ2luID0gYXN5bmMgKCkgPT4ge1xyXG4gIGxvYWRpbmcudmFsdWUgPSB0cnVlXHJcblxyXG4gIC8vc2hvd0RpYWxvZygn0LjQvdGE0L7RgNC80LDRhtC40Y8nLCBg0YHRgtCw0YDRgiDQvtGC0L/RgNCw0LLQutC4YClcclxuXHJcbiAgdHJ5IHtcclxuICAgICRxLm5vdGlmeSh7XHJcbiAgICAgIHR5cGU6ICdwb3NpdGl2ZScsXHJcbiAgICAgIG1lc3NhZ2U6IGB0cnlpbmcgc2VuZCBVUkw6ICR7YXBpLmRlZmF1bHRzLmJhc2VVUkx9IGAsXHJcbiAgICAgIHBvc2l0aW9uOiAndG9wJyxcclxuICAgIH0pXHJcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5wb3N0KCcvbG9naW4nLCB7XHJcbiAgICAgIGVtYWlsOiBlbWFpbC52YWx1ZSxcclxuICAgICAgcGFzc3dvcmQ6IHBhc3N3b3JkLnZhbHVlXHJcbiAgICB9KVxyXG4gICAgYXV0aFN0b3JlLnNldFRva2VuKHJlc3BvbnNlLmRhdGEuYWNjZXNzX3Rva2VuKVxyXG4gICAgcm91dGVyLnB1c2goJy9vcmRlcnMnKVxyXG4gICAgY29uc29sZS5sb2coJ9C/0YDQvtCy0LXRgNC60LAg0YXRgNCw0L3QuNC70LjRidCwIHBpbmlhOiAnLCBhdXRoU3RvcmUudG9rZW4pXHJcbiAgfSBjYXRjaCAoZXJyKXtcclxuICAgICRxLm5vdGlmeSh7XHJcbiAgICAgIHR5cGU6ICduZWdhdGl2ZScsXHJcbiAgICAgIG1lc3NhZ2U6ICfQvtGI0LjQsdC60LAg0LLRhdC+0LTQsDogJyArIGVyci5tZXNzYWdlLFxyXG4gICAgICBwb3NpdGlvbjogXCJ0b3BcIixcclxuICAgICAgdGltZW91dDogXCIxMDAwXCJcclxuICAgIH0pXHJcbiAgICBjb25zb2xlLmVycm9yKCfQvtGI0LjQsdC60LAg0LLRhdC+0LTQsDogJywgZXJyKVxyXG4gIH0gZmluYWxseSB7XHJcbiAgICBsb2FkaW5nLnZhbHVlID0gZmFsc2VcclxuICB9XHJcbn1cclxuXHJcbjwvc2NyaXB0PlxyXG5cclxuPHRlbXBsYXRlPlxyXG4gPHEtcGFnZSBjbGFzcz1cImZsZXggZmxleC1jZW50ZXJcIj5cclxuICAgPHEtY2FyZCBjbGFzcz1cInEtcGEtbWRcIiBzdHlsZT1cIndpZHRoOiAzMDBweFwiPlxyXG4gICAgIDxxLWNhcmQtc2VjdGlvbj5cclxuICAgICAgIDxkaXYgY2xhc3M9XCJ0ZXh0LWg2XCI+0JLRhdC+0LQ8L2Rpdj5cclxuICAgICA8L3EtY2FyZC1zZWN0aW9uPlxyXG5cclxuICAgICA8cS1jYXJkLXNlY3Rpb24+XHJcbiAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwiZW1haWxcIlxyXG4gICAgICAgICAgICAgICAgbGFiZWw9XCJFbWFpbFwiXHJcbiAgICAgICAgICAgICAgICB0eXBlPVwiZW1haWxcIlxyXG4gICAgICAgICAgICAgICAgb3V0bGluZWRcclxuICAgICAgICAgICAgICAgIGNsYXNzPVwicS1tZC1tZFwiXHJcbiAgICAgICAvPlxyXG4gICAgICAgPHEtaW5wdXQgdi1tb2RlbD1cInBhc3N3b3JkXCJcclxuICAgICAgICAgICAgICAgIGxhYmVsPVwi0J/QsNGA0L7Qu9GMXCJcclxuICAgICAgICAgICAgICAgIHR5cGU9XCJwYXNzd29yZFwiXHJcbiAgICAgICAgICAgICAgICBvdXRsaW5lZFxyXG4gICAgICAgLz5cclxuICAgICA8L3EtY2FyZC1zZWN0aW9uPlxyXG5cclxuICAgICA8cS1jYXJkLWFjdGlvbnMgYWxpZ249XCJyaWdodFwiPlxyXG4gICAgICAgPHEtYnRuIGxhYmVsPVwi0JLQvtC50YLQuFwiXHJcbiAgICAgICAgICAgICAgY29sb3I9XCJwcmltYXJ5XCJcclxuICAgICAgICAgICAgICBAY2xpY2s9XCJsb2dpblwiXHJcbiAgICAgICAgICAgICAgOmxvYWRpbmc9XCJsb2FkaW5nXCJcclxuICAgICAgIC8+XHJcbiAgICAgPC9xLWNhcmQtYWN0aW9ucz5cclxuICAgPC9xLWNhcmQ+XHJcbiA8L3EtcGFnZT5cclxuPC90ZW1wbGF0ZT5cclxuXHJcbjxzdHlsZSBzY29wZWQ+XHJcblxyXG48L3N0eWxlPlxyXG4iXSwibmFtZXMiOlsiX2NyZWF0ZUJsb2NrIiwiX3dpdGhDdHgiLCJfY3JlYXRlVk5vZGUiLCJfY3JlYXRlRWxlbWVudFZOb2RlIl0sIm1hcHBpbmdzIjoiOzs7Ozs7OztBQVFBLFVBQU0sUUFBUSxJQUFJLEVBQUU7QUFDcEIsVUFBTSxXQUFXLElBQUksRUFBRTtBQUN2QixVQUFNLFVBQVUsSUFBSSxLQUFLO0FBRXpCLFVBQU0sU0FBUyxVQUFXO0FBQzFCLFVBQU0sS0FBSyxVQUFXO0FBRXRCLFVBQU0sWUFBWSxhQUFjO0FBRWhDLFVBQU0sUUFBUSxZQUFZO0FBQ3hCLGNBQVEsUUFBUTtBQUloQixVQUFJO0FBQ0YsV0FBRyxPQUFPO0FBQUEsVUFDUixNQUFNO0FBQUEsVUFDTixTQUFTLG9CQUFvQixJQUFJLFNBQVMsT0FBTztBQUFBLFVBQ2pELFVBQVU7QUFBQSxRQUNoQixDQUFLO0FBQ0QsY0FBTSxXQUFXLE1BQU0sSUFBSSxLQUFLLFVBQVU7QUFBQSxVQUN4QyxPQUFPLE1BQU07QUFBQSxVQUNiLFVBQVUsU0FBUztBQUFBLFFBQ3pCLENBQUs7QUFDRCxrQkFBVSxTQUFTLFNBQVMsS0FBSyxZQUFZO0FBQzdDLGVBQU8sS0FBSyxTQUFTO0FBQ3JCLGdCQUFRLElBQUksOEJBQThCLFVBQVUsS0FBSztBQUFBLE1BQzFELFNBQVEsS0FBSTtBQUNYLFdBQUcsT0FBTztBQUFBLFVBQ1IsTUFBTTtBQUFBLFVBQ04sU0FBUyxtQkFBbUIsSUFBSTtBQUFBLFVBQ2hDLFVBQVU7QUFBQSxVQUNWLFNBQVM7QUFBQSxRQUNmLENBQUs7QUFDRCxnQkFBUSxNQUFNLGtCQUFrQixHQUFHO0FBQUEsTUFDdkMsVUFBWTtBQUNSLGdCQUFRLFFBQVE7QUFBQSxNQUNqQjtBQUFBLElBQ0g7Ozs7Ozs7Ozs7Ozs7OztzQkFLQ0EsWUE0QlMsT0FBQSxFQUFBLE9BQUEsc0JBNUJ1QjtBQUFBLElBbkRqQyxTQUFBQyxRQW9ERyxNQTBCUztBQUFBLE1BMUJUQyxZQTBCUyxPQUFBO0FBQUEsUUExQkQsT0FBTTtBQUFBLFFBQVUsT0FBQSxFQUFvQixTQUFBLFFBQUE7QUFBQTtRQXBEL0MsU0FBQUQsUUFxREssTUFFaUI7QUFBQSxVQUZqQkMsWUFFaUIsY0FBQSxNQUFBO0FBQUEsWUF2RHRCLFNBQUFELFFBc0RPLE1BQStCLE9BQUEsQ0FBQSxNQUFBLE9BQUEsQ0FBQSxJQUFBO0FBQUEsY0FBL0JFLGdCQUErQixPQUExQixFQUFBLE9BQU0sYUFBVSxRQUFJLEVBQUE7QUFBQTtZQXREaEMsR0FBQTtBQUFBO1VBeURLRCxZQVlpQixjQUFBLE1BQUE7QUFBQSxZQXJFdEIsU0FBQUQsUUEwRE8sTUFLRTtBQUFBLGNBTEZDLFlBS0UsUUFBQTtBQUFBLGdCQS9EVCxZQTBEeUIsT0FBSztBQUFBLGdCQTFEOUIsdUJBQUEsT0FBQSxDQUFBLE1BQUEsT0FBQSxDQUFBLElBQUEsWUEwRHlCLE9BQUssUUFBQTtBQUFBLGdCQUNkLE9BQU07QUFBQSxnQkFDTixNQUFLO0FBQUEsZ0JBQ0wsVUFBQTtBQUFBLGdCQUNBLE9BQU07QUFBQTtjQUVmQSxZQUlFLFFBQUE7QUFBQSxnQkFwRVQsWUFnRXlCLE9BQVE7QUFBQSxnQkFoRWpDLHVCQUFBLE9BQUEsQ0FBQSxNQUFBLE9BQUEsQ0FBQSxJQUFBLFlBZ0V5QixPQUFRLFdBQUE7QUFBQSxnQkFDakIsT0FBTTtBQUFBLGdCQUNOLE1BQUs7QUFBQSxnQkFDTCxVQUFBO0FBQUE7O1lBbkVoQixHQUFBO0FBQUE7VUF1RUtBLFlBTWlCLGNBQUEsRUFBQSxPQUFBLFFBTkksR0FBQTtBQUFBLFlBdkUxQixTQUFBRCxRQXdFTyxNQUlFO0FBQUEsY0FKRkMsWUFJRSxNQUFBO0FBQUEsZ0JBSkssT0FBTTtBQUFBLGdCQUNOLE9BQU07QUFBQSxnQkFDTCxTQUFPLE9BQUs7QUFBQSxnQkFDWixTQUFTLE9BQU87QUFBQTs7WUEzRS9CLEdBQUE7QUFBQTs7UUFBQSxHQUFBO0FBQUE7O0lBQUEsR0FBQTtBQUFBOzs7In0=
