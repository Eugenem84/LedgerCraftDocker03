import { Q as QItemLabel, a as QItem, b as QItemSection } from "./QItem-DuqkKkh7.js";
import { _ as _export_sfc, r as ref, J as useRouter, n as onMounted, D as openBlock, K as createElementBlock, G as createVNode, F as withCtx, L as Fragment, E as createBlock, M as createTextVNode, I as createCommentVNode, N as renderList, O as QBtn, P as withDirectives, H as normalizeClass, R as createBaseVNode, S as toDisplayString, T as QIcon, U as Ripple } from "./index-74sOg8Nl.js";
import { D as DeleteConfirmPage, Q as QList } from "./DeleteConfirmPage-DjUGgkyk.js";
import { Q as QPage } from "./QPage-B-LnxX2q.js";
import { api } from "./axios-D58jYJIV.js";
import { u as useSpecializationsStore } from "./specializations-B0lcZ67D.js";
import { u as useOrderStore } from "./order-CgBhk1_9.js";
const _sfc_main = {
  __name: "OrdersPage",
  setup(__props, { expose: __expose }) {
    __expose();
    const deleteConfirmPage = ref(null);
    const orderStore = useOrderStore();
    const router = useRouter();
    const loading = ref(false);
    const orders = ref([]);
    const statusBorderClass = (status) => {
      return {
        "border-waiting": status === "waiting",
        "border-done": status === "done",
        "border-process": status === "process"
      };
    };
    const goToOrderDetails = (order) => {
      console.log("переходим на ордер", order.id);
      orderStore.setOrder(order);
      router.push(`/orders/${order.id}`);
    };
    const goToNewOrder = () => {
      console.log("не реализовано");
      orderStore.clearCurrentOrder();
      router.push({ name: `new-order` });
    };
    const getOrders = async () => {
      console.log("!!!применены обновления в коде!!!");
      loading.value = true;
      try {
        const specializationsStore = useSpecializationsStore();
        const id = specializationsStore.selectedSpecialization.id;
        console.log("запрашиваем ордеры специализации: ", id);
        const token = localStorage.getItem("authToken");
        console.log("token: ", token);
        const response = await api.get(`/get_orders_by_user`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        orders.value = response.data;
        console.log("ордеры: ", response.data);
      } catch (err) {
        console.error("Ошибка загрузки ордеров: ", err);
      } finally {
        loading.value = false;
      }
    };
    onMounted(() => {
      console.log("url: ", api.defaults.baseURL);
      getOrders();
    });
    const __returned__ = { deleteConfirmPage, orderStore, router, loading, orders, statusBorderClass, goToOrderDetails, goToNewOrder, getOrders, ref, onMounted, get useRouter() {
      return useRouter;
    }, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    }, get useOrderStore() {
      return useOrderStore;
    }, DeleteConfirmPage };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1 = { class: "row no-wrap items-center" };
const _hoisted_2 = { class: "col-3 text-left" };
const _hoisted_3 = { class: "col-6 text-center" };
const _hoisted_4 = { class: "col-1 text-right" };
const _hoisted_5 = {
  key: 0,
  class: "q-ml-sm",
  style: { "display": "flex", "flex-direction": "column" },
  align: "center"
};
const _hoisted_6 = { class: "col-2 text-right" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock(Fragment, null, [
    createVNode(QPage, {
      class: "q-pa-none",
      style: { "padding": "0" }
    }, {
      default: withCtx(() => [
        createVNode(QList, {
          bordered: "",
          separator: ""
        }, {
          default: withCtx(() => [
            $setup.orders.length === 0 ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
              default: withCtx(() => _cache[0] || (_cache[0] = [
                createTextVNode("Нет данных")
              ])),
              _: 1
            })) : createCommentVNode("", true),
            (openBlock(true), createElementBlock(Fragment, null, renderList($setup.orders, (order) => {
              return withDirectives((openBlock(), createBlock(QItem, {
                key: order,
                clickable: "",
                onClick: ($event) => $setup.goToOrderDetails(order),
                class: normalizeClass(["flex-direction: column", $setup.statusBorderClass(order.status)]),
                style: { "height": "20px" }
              }, {
                default: withCtx(() => [
                  createVNode(QItemSection, { class: "col-12" }, {
                    default: withCtx(() => [
                      createBaseVNode("div", _hoisted_1, [
                        createBaseVNode("div", _hoisted_2, [
                          createVNode(QItemLabel, {
                            class: "text-body1 q-pa-none q-ma-none",
                            style: { "font-size": "16px", "align-items": "flex-start", "margin-bottom": "-7px" }
                          }, {
                            default: withCtx(() => [
                              createTextVNode(toDisplayString(new Date(order.created_at).toLocaleDateString("ru-RU")), 1)
                            ]),
                            _: 2
                          }, 1024),
                          createVNode(QItemLabel, {
                            class: "q-ml-sm",
                            style: { "white-space": "nowrap", "color": "gray" }
                          }, {
                            default: withCtx(() => [
                              createTextVNode(" №: " + toDisplayString(order.id), 1)
                            ]),
                            _: 2
                          }, 1024)
                        ]),
                        createBaseVNode("div", _hoisted_3, [
                          createVNode(QItemLabel, null, {
                            default: withCtx(() => [
                              createTextVNode(toDisplayString(order.client_name), 1)
                            ]),
                            _: 2
                          }, 1024)
                        ]),
                        createBaseVNode("div", _hoisted_4, [
                          order.paid ? (openBlock(), createElementBlock("div", _hoisted_5, [
                            createVNode(QItemLabel, null, {
                              default: withCtx(() => [
                                createVNode(QIcon, {
                                  name: "verified",
                                  color: "green",
                                  class: "q-ml-sm"
                                }),
                                _cache[1] || (_cache[1] = createBaseVNode("span", {
                                  class: "text-caption text-green",
                                  style: { "font-size": "9px" }
                                }, "оплачено", -1))
                              ]),
                              _: 1
                            })
                          ])) : createCommentVNode("", true)
                        ]),
                        createBaseVNode("div", _hoisted_6, [
                          createVNode(QItemLabel, { class: "text-body1" }, {
                            default: withCtx(() => [
                              createTextVNode(toDisplayString(order.total_amount), 1)
                            ]),
                            _: 2
                          }, 1024)
                        ])
                      ])
                    ]),
                    _: 2
                  }, 1024)
                ]),
                _: 2
              }, 1032, ["onClick", "class"])), [
                [Ripple]
              ]);
            }), 128))
          ]),
          _: 1
        }),
        createVNode(QBtn, {
          icon: "add",
          round: "",
          class: "fab bg-yellow text-black",
          onClick: $setup.goToNewOrder,
          size: "20px"
        })
      ]),
      _: 1
    }),
    createVNode($setup["DeleteConfirmPage"], { ref: "deleteConfirmPage" }, null, 512)
  ], 64);
}
const OrdersPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render], ["__scopeId", "data-v-372fd12a"], ["__file", "OrdersPage.vue"]]);
export {
  OrdersPage as default
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiT3JkZXJzUGFnZS1EZ21YUmhRWS5qcyIsInNvdXJjZXMiOlsiLi4vLi4vLi4vc3JjL3BhZ2VzL09yZGVyc1BhZ2UudnVlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQgc2V0dXA+XG5pbXBvcnQgeyByZWYsIG9uTW91bnRlZCB9IGZyb20gJ3Z1ZSc7XG5pbXBvcnQgeyB1c2VSb3V0ZXIgfSBmcm9tIFwidnVlLXJvdXRlclwiO1xuaW1wb3J0IHsgYXBpIH0gZnJvbSAnYm9vdC9heGlvcy5qcydcbmltcG9ydCB7dXNlU3BlY2lhbGl6YXRpb25zU3RvcmV9IGZyb20gXCJzdG9yZXMvc3BlY2lhbGl6YXRpb25zLmpzXCI7XG5pbXBvcnQge3VzZU9yZGVyU3RvcmV9IGZyb20gXCJzdG9yZXMvb3JkZXIuanNcIjtcbmltcG9ydCBEZWxldGVDb25maXJtUGFnZSBmcm9tIFwicGFnZXMvRGVsZXRlQ29uZmlybVBhZ2UudnVlXCI7XG5cbmNvbnN0IGRlbGV0ZUNvbmZpcm1QYWdlID0gcmVmKG51bGwpXG5cbmNvbnN0IG9yZGVyU3RvcmUgPSB1c2VPcmRlclN0b3JlKClcbmNvbnN0IHJvdXRlciA9IHVzZVJvdXRlcigpXG5jb25zdCBsb2FkaW5nID0gcmVmKGZhbHNlKVxuY29uc3Qgb3JkZXJzID0gcmVmKFtdKVxuXG5jb25zdCBzdGF0dXNCb3JkZXJDbGFzcyA9IChzdGF0dXMpID0+IHtcbiAgcmV0dXJuIHtcbiAgICAnYm9yZGVyLXdhaXRpbmcnOiBzdGF0dXMgPT09J3dhaXRpbmcnLFxuICAgICdib3JkZXItZG9uZSc6IHN0YXR1cyA9PT0gJ2RvbmUnLFxuICAgICdib3JkZXItcHJvY2Vzcyc6IHN0YXR1cyA9PT0gJ3Byb2Nlc3MnXG4gIH1cbn1cblxuY29uc3QgZ29Ub09yZGVyRGV0YWlscyA9IChvcmRlcikgPT4ge1xuICBjb25zb2xlLmxvZygn0L/QtdGA0LXRhdC+0LTQuNC8INC90LAg0L7RgNC00LXRgCcsIG9yZGVyLmlkKVxuICBvcmRlclN0b3JlLnNldE9yZGVyKG9yZGVyKVxuICByb3V0ZXIucHVzaChgL29yZGVycy8ke29yZGVyLmlkfWApXG59XG5cbmNvbnN0IGdvVG9OZXdPcmRlciA9ICgpID0+IHtcbiAgY29uc29sZS5sb2coJ9C90LUg0YDQtdCw0LvQuNC30L7QstCw0L3QvicpXG4gIG9yZGVyU3RvcmUuY2xlYXJDdXJyZW50T3JkZXIoKVxuICByb3V0ZXIucHVzaCh7IG5hbWU6IGBuZXctb3JkZXJgfSlcbn1cblxuY29uc3QgZ2V0T3JkZXJzID0gYXN5bmMgKCkgPT4ge1xuICBjb25zb2xlLmxvZygnISEh0L/RgNC40LzQtdC90LXQvdGLINC+0LHQvdC+0LLQu9C10L3QuNGPINCyINC60L7QtNC1ISEhJylcbiAgbG9hZGluZy52YWx1ZSA9IHRydWVcbiAgdHJ5IHtcbiAgICBjb25zdCBzcGVjaWFsaXphdGlvbnNTdG9yZSA9IHVzZVNwZWNpYWxpemF0aW9uc1N0b3JlKClcbiAgICBjb25zdCBpZCA9IHNwZWNpYWxpemF0aW9uc1N0b3JlLnNlbGVjdGVkU3BlY2lhbGl6YXRpb24uaWRcbiAgICBjb25zb2xlLmxvZygn0LfQsNC/0YDQsNGI0LjQstCw0LXQvCDQvtGA0LTQtdGA0Ysg0YHQv9C10YbQuNCw0LvQuNC30LDRhtC40Lg6ICcsIGlkKVxuICAgIGNvbnN0IHRva2VuID0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oJ2F1dGhUb2tlbicpXG4gICAgY29uc29sZS5sb2coJ3Rva2VuOiAnLCB0b2tlbilcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5nZXQoYC9nZXRfb3JkZXJzX2J5X3VzZXJgLCB7XG4gICAgICBoZWFkZXJzOiB7XG4gICAgICAgIEF1dGhvcml6YXRpb246IGBCZWFyZXIgJHt0b2tlbn1gXG4gICAgICB9XG4gICAgfSlcbiAgICAvL2F1dGhTdG9yZS5zZXRUb2tlbihyZXNwb25zZS5kYXRhLmFjY2Vzc190b2tlbilcbiAgICBvcmRlcnMudmFsdWUgPSByZXNwb25zZS5kYXRhXG4gICAgY29uc29sZS5sb2coJ9C+0YDQtNC10YDRizogJywgcmVzcG9uc2UuZGF0YSlcbiAgfSBjYXRjaCAoZXJyKSB7XG4gICAgY29uc29sZS5lcnJvcign0J7RiNC40LHQutCwINC30LDQs9GA0YPQt9C60Lgg0L7RgNC00LXRgNC+0LI6ICcsIGVycilcbiAgfSBmaW5hbGx5IHtcbiAgICBsb2FkaW5nLnZhbHVlID0gZmFsc2VcbiAgfVxufVxuXG5vbk1vdW50ZWQoKCkgPT4ge1xuICBjb25zb2xlLmxvZygndXJsOiAnLCBhcGkuZGVmYXVsdHMuYmFzZVVSTClcbiAgZ2V0T3JkZXJzKClcbn0pXG5cbjwvc2NyaXB0PlxuXG48dGVtcGxhdGU+XG4gIDxxLXBhZ2UgY2xhc3M9XCJxLXBhLW5vbmVcIiBzdHlsZT1cInBhZGRpbmc6IDBcIj5cbiAgICA8cS1saXN0IGJvcmRlcmVkIHNlcGFyYXRvcj5cbiAgICAgIDxxLWl0ZW0tbGFiZWwgdi1pZj1cIm9yZGVycy5sZW5ndGggPT09IDBcIj7QndC10YIg0LTQsNC90L3Ri9GFPC9xLWl0ZW0tbGFiZWw+XG4gICAgICA8cS1pdGVtIHYtZm9yPVwib3JkZXIgaW4gb3JkZXJzXCJcbiAgICAgICAgICAgICAgOmtleT1cIm9yZGVyXCJcbiAgICAgICAgICAgICAgY2xpY2thYmxlXG4gICAgICAgICAgICAgIHYtcmlwcGxlXG4gICAgICAgICAgICAgIEBjbGljaz1cImdvVG9PcmRlckRldGFpbHMob3JkZXIpXCJcbiAgICAgICAgICAgICAgY2xhc3M9XCJmbGV4LWRpcmVjdGlvbjogY29sdW1uXCJcbiAgICAgICAgICAgICAgc3R5bGU9XCJoZWlnaHQ6IDIwcHhcIlxuICAgICAgICAgICAgICA6Y2xhc3M9XCJzdGF0dXNCb3JkZXJDbGFzcyhvcmRlci5zdGF0dXMpXCJcbiAgICAgID5cbiAgICAgICAgPCEtLSDQn9C10YDQstGL0Lkg0YDRj9C0ICgzINC60L7Qu9C+0L3QutC4KSAtLT5cbiAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTEyXCI+XG4gICAgICAgICAgPGRpdiBjbGFzcz1cInJvdyBuby13cmFwIGl0ZW1zLWNlbnRlclwiPlxuICAgICAgICAgICAgPGRpdiBjbGFzcz1cImNvbC0zIHRleHQtbGVmdFwiPlxuICAgICAgICAgICAgICA8cS1pdGVtLWxhYmVsIGNsYXNzPVwidGV4dC1ib2R5MSBxLXBhLW5vbmUgcS1tYS1ub25lXCIgc3R5bGU9XCJmb250LXNpemU6IDE2cHg7IGFsaWduLWl0ZW1zOiBmbGV4LXN0YXJ0OyBtYXJnaW4tYm90dG9tOiAtN3B4XCI+XG4gICAgICAgICAgICAgICAge3sgbmV3IERhdGUob3JkZXIuY3JlYXRlZF9hdCkudG9Mb2NhbGVEYXRlU3RyaW5nKCdydS1SVScpIH19XG4gICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgICA8cS1pdGVtLWxhYmVsIGNsYXNzPVwicS1tbC1zbVwiIHN0eWxlPVwid2hpdGUtc3BhY2U6IG5vd3JhcDsgY29sb3I6IGdyYXlcIj5cbiAgICAgICAgICAgICAgICDihJY6IHt7b3JkZXIuaWR9fVxuICAgICAgICAgICAgICA8L3EtaXRlbS1sYWJlbD5cbiAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgPGRpdiBjbGFzcz1cImNvbC02IHRleHQtY2VudGVyXCI+XG4gICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgICAgIHt7IG9yZGVyLmNsaWVudF9uYW1lIH19XG4gICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICA8ZGl2IGNsYXNzPVwiY29sLTEgdGV4dC1yaWdodFwiPlxuICAgICAgICAgICAgICA8ZGl2IHYtaWY9XCJvcmRlci5wYWlkXCJcbiAgICAgICAgICAgICAgICAgICBjbGFzcz1cInEtbWwtc21cIlxuICAgICAgICAgICAgICAgICAgIHN0eWxlPVwiZGlzcGxheTpmbGV4OyBmbGV4LWRpcmVjdGlvbjogY29sdW1uXCIgYWxpZ249XCJjZW50ZXJcIj5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgICAgICAgPHEtaWNvbiBuYW1lPVwidmVyaWZpZWRcIiBjb2xvcj1cImdyZWVuXCIgY2xhc3M9XCJxLW1sLXNtXCIgLz5cbiAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidGV4dC1jYXB0aW9uIHRleHQtZ3JlZW5cIiBzdHlsZT1cImZvbnQtc2l6ZTogOXB4XCI+0L7Qv9C70LDRh9C10L3Qvjwvc3Bhbj5cbiAgICAgICAgICAgICAgICA8L3EtaXRlbS1sYWJlbD5cbiAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJjb2wtMiB0ZXh0LXJpZ2h0XCI+XG4gICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LWJvZHkxXCI+XG4gICAgICAgICAgICAgICAge3sgb3JkZXIudG90YWxfYW1vdW50IH19XG4gICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgPC9kaXY+XG4gICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgIDwvcS1pdGVtPlxuXG4gICAgPC9xLWxpc3Q+XG5cbiAgICA8IS0tINCf0LvQsNCy0LDRjtGJ0LDRjyDQutC90L7Qv9C60LAg0LTQvtCx0LDQstC70LXQvdC40Y8g0L3QvtCy0L7Qs9C+INC+0YDQtNC10YDQsCAtLT5cbiAgICA8cS1idG5cbiAgICAgIGljb249XCJhZGRcIlxuICAgICAgcm91bmRcbiAgICAgIGNsYXNzPVwiZmFiIGJnLXllbGxvdyB0ZXh0LWJsYWNrXCJcbiAgICAgIEBjbGljaz1cImdvVG9OZXdPcmRlclwiXG4gICAgICBzaXplPVwiMjBweFwiXG4gICAgLz5cblxuICA8L3EtcGFnZT5cblxuICA8RGVsZXRlQ29uZmlybVBhZ2UgcmVmPVwiZGVsZXRlQ29uZmlybVBhZ2VcIiAvPlxuXG48L3RlbXBsYXRlPlxuXG48c3R5bGUgc2NvcGVkPlxuXG4uZmFiIHtcbiAgcG9zaXRpb246IGZpeGVkO1xuICBib3R0b206IDcwcHg7XG4gIHJpZ2h0OiAxNnB4O1xuICB6LWluZGV4OiAxMDAwOyAvKiDRh9GC0L7QsdGLINC60L3QvtC/0LrQsCDQsdGL0LvQsCDQv9C+0LLQtdGA0YUg0L7RgdGC0LDQu9GM0L3Ri9GFINGN0LvQtdC80LXQvdGC0L7QsiAqL1xufVxuXG4uYm9yZGVyLXdhaXRpbmcge1xuICBib3JkZXItbGVmdDogM3B4IHNvbGlkIHllbGxvdztcbn1cblxuLmJvcmRlci1kb25lIHtcbiAgYm9yZGVyLWxlZnQ6IDNweCBzb2xpZCBncmVlbjtcbn1cblxuLmJvcmRlci1wcm9jZXNzIHtcbiAgYm9yZGVyLWxlZnQ6IDNweCBzb2xpZCByZWQ7XG59XG5cbjwvc3R5bGU+XG4iXSwibmFtZXMiOlsiX29wZW5CbG9jayIsIl9jcmVhdGVFbGVtZW50QmxvY2siLCJfRnJhZ21lbnQiLCJfY3JlYXRlVk5vZGUiLCJfd2l0aEN0eCIsIl9jcmVhdGVCbG9jayIsIl9jcmVhdGVUZXh0Vk5vZGUiLCJfY3JlYXRlQ29tbWVudFZOb2RlIiwiX3JlbmRlckxpc3QiLCJfbm9ybWFsaXplQ2xhc3MiLCJfY3JlYXRlRWxlbWVudFZOb2RlIiwiX3RvRGlzcGxheVN0cmluZyJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7QUFRQSxVQUFNLG9CQUFvQixJQUFJLElBQUk7QUFFbEMsVUFBTSxhQUFhLGNBQWE7QUFDaEMsVUFBTSxTQUFTLFVBQVM7QUFDeEIsVUFBTSxVQUFVLElBQUksS0FBSztBQUN6QixVQUFNLFNBQVMsSUFBSSxDQUFFLENBQUE7QUFFckIsVUFBTSxvQkFBb0IsQ0FBQyxXQUFXO0FBQ3BDLGFBQU87QUFBQSxRQUNMLGtCQUFrQixXQUFVO0FBQUEsUUFDNUIsZUFBZSxXQUFXO0FBQUEsUUFDMUIsa0JBQWtCLFdBQVc7QUFBQSxNQUNqQztBQUFBLElBQ0E7QUFFQSxVQUFNLG1CQUFtQixDQUFDLFVBQVU7QUFDbEMsY0FBUSxJQUFJLHNCQUFzQixNQUFNLEVBQUU7QUFDMUMs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
