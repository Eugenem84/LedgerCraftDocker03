import { a as createComponent, ak as useDarkProps, al as useDark, b as computed, h, d as hSlot, g as getCurrentInstance, _ as _export_sfc, r as ref, D as openBlock, E as createBlock, F as withCtx, G as createVNode, Z as QCard, $ as QCardSection, R as createBaseVNode, S as toDisplayString, a1 as QCardActions, O as QBtn, a2 as QDialog } from "./index-74sOg8Nl.js";
const roleAttrExceptions = ["ul", "ol"];
const QList = createComponent({
  name: "QList",
  props: {
    ...useDarkProps,
    bordered: Boolean,
    dense: Boolean,
    separator: Boolean,
    padding: Boolean,
    tag: {
      type: String,
      default: "div"
    }
  },
  setup(props, { slots }) {
    const vm = getCurrentInstance();
    const isDark = useDark(props, vm.proxy.$q);
    const role = computed(
      () => roleAttrExceptions.includes(props.tag) ? null : "list"
    );
    const classes = computed(
      () => "q-list" + (props.bordered === true ? " q-list--bordered" : "") + (props.dense === true ? " q-list--dense" : "") + (props.separator === true ? " q-list--separator" : "") + (isDark.value === true ? " q-list--dark" : "") + (props.padding === true ? " q-list--padding" : "")
    );
    return () => h(props.tag, { class: classes.value, role: role.value }, hSlot(slots.default));
  }
});
const _sfc_main = {
  __name: "DeleteConfirmPage",
  props: {
    defaultTitle: { type: String, default: "Подтверждение" },
    defaultMessage: { type: String, default: "Действительно удалить?" }
  },
  setup(__props, { expose: __expose }) {
    const props = __props;
    const isOpen = ref(false);
    const title = ref(props.defaultTitle);
    const message = ref(props.defaultMessage);
    let confirmCallback = () => {
    };
    function open(newTitle, newMessage, onConfirm) {
      isOpen.value = true;
      title.value = newTitle || props.defaultTitle;
      message.value = newMessage || props.defaultMessage;
      confirmCallback = onConfirm;
    }
    function close() {
      isOpen.value = false;
    }
    function confirm() {
      confirmCallback();
      close();
    }
    __expose({ open });
    const __returned__ = { props, isOpen, title, message, get confirmCallback() {
      return confirmCallback;
    }, set confirmCallback(v) {
      confirmCallback = v;
    }, open, close, confirm, ref };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1 = { class: "text-h6" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createBlock(QDialog, {
    modelValue: $setup.isOpen,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.isOpen = $event)
  }, {
    default: withCtx(() => [
      createVNode(QCard, null, {
        default: withCtx(() => [
          createVNode(QCardSection, null, {
            default: withCtx(() => [
              createBaseVNode("div", _hoisted_1, toDisplayString($setup.title), 1),
              createBaseVNode("div", null, toDisplayString($setup.message), 1)
            ]),
            _: 1
          }),
          createVNode(QCardActions, { align: "right" }, {
            default: withCtx(() => [
              createVNode(QBtn, {
                flat: "",
                label: "отмена",
                color: "yellow",
                onClick: $setup.close
              }),
              createVNode(QBtn, {
                flat: "",
                label: "удалить",
                color: "red",
                onClick: $setup.confirm
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      })
    ]),
    _: 1
  }, 8, ["modelValue"]);
}
const DeleteConfirmPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render], ["__file", "DeleteConfirmPage.vue"]]);
export {
  DeleteConfirmPage as D,
  QList as Q
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiRGVsZXRlQ29uZmlybVBhZ2UtRGpVR2dreWsuanMiLCJzb3VyY2VzIjpbIi4uLy4uLy4uL25vZGVfbW9kdWxlcy9xdWFzYXIvc3JjL2NvbXBvbmVudHMvaXRlbS9RTGlzdC5qcyIsIi4uLy4uLy4uL3NyYy9wYWdlcy9EZWxldGVDb25maXJtUGFnZS52dWUiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IHsgaCwgY29tcHV0ZWQsIGdldEN1cnJlbnRJbnN0YW5jZSB9IGZyb20gJ3Z1ZSdcblxuaW1wb3J0IHsgY3JlYXRlQ29tcG9uZW50IH0gZnJvbSAnLi4vLi4vdXRpbHMvcHJpdmF0ZS5jcmVhdGUvY3JlYXRlLmpzJ1xuaW1wb3J0IHVzZURhcmssIHsgdXNlRGFya1Byb3BzIH0gZnJvbSAnLi4vLi4vY29tcG9zYWJsZXMvcHJpdmF0ZS51c2UtZGFyay91c2UtZGFyay5qcydcbmltcG9ydCB7IGhTbG90IH0gZnJvbSAnLi4vLi4vdXRpbHMvcHJpdmF0ZS5yZW5kZXIvcmVuZGVyLmpzJ1xuXG5jb25zdCByb2xlQXR0ckV4Y2VwdGlvbnMgPSBbICd1bCcsICdvbCcgXVxuXG5leHBvcnQgZGVmYXVsdCBjcmVhdGVDb21wb25lbnQoe1xuICBuYW1lOiAnUUxpc3QnLFxuXG4gIHByb3BzOiB7XG4gICAgLi4udXNlRGFya1Byb3BzLFxuXG4gICAgYm9yZGVyZWQ6IEJvb2xlYW4sXG4gICAgZGVuc2U6IEJvb2xlYW4sXG4gICAgc2VwYXJhdG9yOiBCb29sZWFuLFxuICAgIHBhZGRpbmc6IEJvb2xlYW4sXG5cbiAgICB0YWc6IHtcbiAgICAgIHR5cGU6IFN0cmluZyxcbiAgICAgIGRlZmF1bHQ6ICdkaXYnXG4gICAgfVxuICB9LFxuXG4gIHNldHVwIChwcm9wcywgeyBzbG90cyB9KSB7XG4gICAgY29uc3Qgdm0gPSBnZXRDdXJyZW50SW5zdGFuY2UoKVxuICAgIGNvbnN0IGlzRGFyayA9IHVzZURhcmsocHJvcHMsIHZtLnByb3h5LiRxKVxuXG4gICAgY29uc3Qgcm9sZSA9IGNvbXB1dGVkKCgpID0+IChcbiAgICAgIHJvbGVBdHRyRXhjZXB0aW9ucy5pbmNsdWRlcyhwcm9wcy50YWcpID8gbnVsbCA6ICdsaXN0JylcbiAgICApXG5cbiAgICBjb25zdCBjbGFzc2VzID0gY29tcHV0ZWQoKCkgPT5cbiAgICAgICdxLWxpc3QnXG4gICAgICArIChwcm9wcy5ib3JkZXJlZCA9PT0gdHJ1ZSA/ICcgcS1saXN0LS1ib3JkZXJlZCcgOiAnJylcbiAgICAgICsgKHByb3BzLmRlbnNlID09PSB0cnVlID8gJyBxLWxpc3QtLWRlbnNlJyA6ICcnKVxuICAgICAgKyAocHJvcHMuc2VwYXJhdG9yID09PSB0cnVlID8gJyBxLWxpc3QtLXNlcGFyYXRvcicgOiAnJylcbiAgICAgICsgKGlzRGFyay52YWx1ZSA9PT0gdHJ1ZSA/ICcgcS1saXN0LS1kYXJrJyA6ICcnKVxuICAgICAgKyAocHJvcHMucGFkZGluZyA9PT0gdHJ1ZSA/ICcgcS1saXN0LS1wYWRkaW5nJyA6ICcnKVxuICAgIClcblxuICAgIHJldHVybiAoKSA9PiBoKHByb3BzLnRhZywgeyBjbGFzczogY2xhc3Nlcy52YWx1ZSwgcm9sZTogcm9sZS52YWx1ZSB9LCBoU2xvdChzbG90cy5kZWZhdWx0KSlcbiAgfVxufSlcbiIsIjxzY3JpcHQgc2V0dXA+XG5pbXBvcnQgeyByZWYgfSBmcm9tIFwidnVlXCI7XG5cbmNvbnN0IHByb3BzID0gZGVmaW5lUHJvcHMoe1xuICBkZWZhdWx0VGl0bGU6IHsgdHlwZTogU3RyaW5nLCBkZWZhdWx0OiAn0J/QvtC00YLQstC10YDQttC00LXQvdC40LUnIH0sXG4gIGRlZmF1bHRNZXNzYWdlOiB7IHR5cGU6IFN0cmluZywgZGVmYXVsdDogJ9CU0LXQudGB0YLQstC40YLQtdC70YzQvdC+INGD0LTQsNC70LjRgtGMPycgfVxufSk7XG5cbmNvbnN0IGlzT3BlbiA9IHJlZihmYWxzZSk7XG5jb25zdCB0aXRsZSA9IHJlZihwcm9wcy5kZWZhdWx0VGl0bGUpO1xuY29uc3QgbWVzc2FnZSA9IHJlZihwcm9wcy5kZWZhdWx0TWVzc2FnZSk7XG5sZXQgY29uZmlybUNhbGxiYWNrID0gKCkgPT4ge307XG5cbmZ1bmN0aW9uIG9wZW4obmV3VGl0bGUsIG5ld01lc3NhZ2UsIG9uQ29uZmlybSkge1xuICBpc09wZW4udmFsdWUgPSB0cnVlO1xuICB0aXRsZS52YWx1ZSA9IG5ld1RpdGxlIHx8IHByb3BzLmRlZmF1bHRUaXRsZTtcbiAgbWVzc2FnZS52YWx1ZSA9IG5ld01lc3NhZ2UgfHwgcHJvcHMuZGVmYXVsdE1lc3NhZ2U7XG4gIGNvbmZpcm1DYWxsYmFjayA9IG9uQ29uZmlybTtcbn1cblxuZnVuY3Rpb24gY2xvc2UoKSB7XG4gIGlzT3Blbi52YWx1ZSA9IGZhbHNlO1xufVxuXG5mdW5jdGlvbiBjb25maXJtKCkge1xuICBjb25maXJtQ2FsbGJhY2soKTtcbiAgY2xvc2UoKTtcbn1cblxuZGVmaW5lRXhwb3NlKHtvcGVufSk7XG48L3NjcmlwdD5cblxuPHRlbXBsYXRlPlxuICA8cS1kaWFsb2cgdi1tb2RlbD1cImlzT3BlblwiPlxuICAgIDxxLWNhcmQ+XG4gICAgICA8cS1jYXJkLXNlY3Rpb24+XG4gICAgICAgIDxkaXYgY2xhc3M9XCJ0ZXh0LWg2XCI+e3sgdGl0bGUgfX08L2Rpdj5cbiAgICAgICAgPGRpdj57eyBtZXNzYWdlIH19PC9kaXY+XG4gICAgICA8L3EtY2FyZC1zZWN0aW9uPlxuICAgICAgPHEtY2FyZC1hY3Rpb25zIGFsaWduPVwicmlnaHRcIj5cbiAgICAgICAgPHEtYnRuXG4gICAgICAgICAgZmxhdFxuICAgICAgICAgIGxhYmVsPVwi0L7RgtC80LXQvdCwXCJcbiAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgQGNsaWNrPVwiY2xvc2VcIlxuICAgICAgICAvPlxuICAgICAgICA8cS1idG5cbiAgICAgICAgICBmbGF0XG4gICAgICAgICAgbGFiZWw9XCLRg9C00LDQu9C40YLRjFwiXG4gICAgICAgICAgY29sb3I9XCJyZWRcIlxuICAgICAgICAgIEBjbGljaz1cImNvbmZpcm1cIlxuICAgICAgICAvPlxuICAgICAgPC9xLWNhcmQtYWN0aW9ucz5cbiAgICA8L3EtY2FyZD5cbiAgPC9xLWRpYWxvZz5cbjwvdGVtcGxhdGU+XG4iXSwibmFtZXMiOlsiX2NyZWF0ZUJsb2NrIiwiX3dpdGhDdHgiLCJfY3JlYXRlVk5vZGUiLCJfY3JlYXRlRWxlbWVudFZOb2RlIiwiX3RvRGlzcGxheVN0cmluZyJdLCJtYXBwaW5ncyI6IjtBQU1BLE1BQU0scUJBQXFCLENBQUUsTUFBTSxJQUFJO0FBRXZDLE1BQUEsUUFBZSxnQkFBZ0I7QUFBQSxFQUM3QixNQUFNO0FBQUEsRUFFTixPQUFPO0FBQUEsSUFDTCxHQUFHO0FBQUEsSUFFSCxVQUFVO0FBQUEsSUFDVixPQUFPO0FBQUEsSUFDUCxXQUFXO0FBQUEsSUFDWCxTQUFTO0FBQUEsSUFFVCxLQUFLO0FBQUEsTUFDSCxNQUFNO0FBQUEsTUFDTixTQUFTO0FBQUEsSUFDZjtBQUFBLEVBQ0c7QUFBQSxFQUVELE1BQU8sT0FBTyxFQUFFLFNBQVM7QUFDdkIsVUFBTSxLQUFLLG1CQUFrQjtBQUM3QixVQUFNLFNBQVMsUUFBUSxPQUFPLEdBQUcsTUFBTSxFQUFFO0FBRXpDLFVBQU0sT0FBTztBQUFBLE1BQVMsTUFDcEIsbUJBQW1CLFNBQVMsTUFBTSxHQUFHLElBQUksT0FBTztBQUFBLElBQ3REO0FBRUksVUFBTSxVQUFVO0FBQUEsTUFBUyxNQUN2QixZQUNHLE1BQU0sYUFBYSxPQUFPLHNCQUFzQixPQUNoRCxNQUFNLFVBQVUsT0FBTyxtQkFBbUIsT0FDMUMsTUFBTSxjQUFjLE9BQU8sdUJBQXVCLE9BQ2xELE9BQU8sVUFBVSxPQUFPLGtCQUFrQixPQUMxQyxNQUFNLFlBQVksT0FBTyxxQkFBcUI7QUFBQSxJQUN2RDtBQUVJLFdBQU8sTUFBTSxFQUFFLE1BQU0sS0FBSyxFQUFFLE9BQU8sUUFBUSxPQUFPLE1BQU0sS0FBSyxNQUFLLEdBQUksTUFBTSxNQUFNLE9BQU8sQ0FBQztBQUFBLEVBQzlGO0FBQ0EsQ0FBQzs7Ozs7Ozs7QUN6Q0QsVUFBTSxRQUFRO0FBS2QsVUFBTSxTQUFTLElBQUksS0FBSztBQUN4QixVQUFNLFFBQVEsSUFBSSxNQUFNLFlBQVk7QUFDcEMsVUFBTSxVQUFVLElBQUksTUFBTSxjQUFjO0FBQ3hDLFFBQUksa0JBQWtCLE1BQU07QUFBQSxJQUFFO0FBRTlCLGFBQVMsS0FBSyxVQUFVLFlBQVksV0FBVztBQUM3QyxhQUFPLFFBQVE7QUFDZixZQUFNLFFBQVEsWUFBWSxNQUFNO0FBQ2hDLGNBQVEsUUFBUSxjQUFjLE1BQU07QUFDcEMsd0JBQWtCO0FBQUEsSUFDcEI7QUFFQSxhQUFTLFFBQVE7QUFDZixhQUFPLFFBQVE7QUFBQSxJQUNqQjtBQUVBLGFBQVMsVUFBVTtBQUNqQixzQkFBaUI7QUFDakIsWUFBTztBQUFBLElBQ1Q7QUFFQSxhQUFhLEVBQUMsS0FBSSxDQUFDOzs7Ozs7Ozs7O0FBT04sTUFBQSxhQUFBLEVBQUEsT0FBTSxVQUFTOztzQkFIMUJBLFlBcUJXLFNBQUE7QUFBQSxJQXREYixZQWlDcUIsT0FBTTtBQUFBLElBakMzQix1QkFBQSxPQUFBLENBQUEsTUFBQSxPQUFBLENBQUEsSUFBQSxZQWlDcUIsT0FBTSxTQUFBO0FBQUE7SUFqQzNCLFNBQUFDLFFBa0NJLE1BbUJTO0FBQUEsTUFuQlRDLFlBbUJTLE9BQUEsTUFBQTtBQUFBLFFBckRiLFNBQUFELFFBbUNNLE1BR2lCO0FBQUEsVUFIakJDLFlBR2lCLGNBQUEsTUFBQTtBQUFBLFlBdEN2QixTQUFBRCxRQW9DUSxNQUFzQztBQUFBLGNBQXRDRSxnQkFBc0MsT0FBdEMsWUFBc0NDLGdCQUFkLE9BQUssS0FBQSxHQUFBLENBQUE7QUFBQSxjQUM3QkQsZ0JBQXdCLDZCQUFoQixPQUFPLE9BQUEsR0FBQSxDQUFBO0FBQUE7WUFyQ3ZCLEdBQUE7QUFBQTtVQXVDTUQsWUFhaUIsY0FBQSxFQUFBLE9BQUEsUUFiSSxHQUFBO0FBQUEsWUF2QzNCLFNBQUFELFFBd0NRLE1BS0U7QUFBQSxjQUxGQyxZQUtFLE1BQUE7QUFBQSxnQkFKQSxNQUFBO0FBQUEsZ0JBQ0EsT0FBTTtBQUFBLGdCQUNOLE9BQU07QUFBQSxnQkFDTCxTQUFPLE9BQUs7QUFBQTtjQUVmQSxZQUtFLE1BQUE7QUFBQSxnQkFKQSxNQUFBO0FBQUEsZ0JBQ0EsT0FBTTtBQUFBLGdCQUNOLE9BQU07QUFBQSxnQkFDTCxTQUFPLE9BQU87QUFBQTs7WUFsRHpCLEdBQUE7QUFBQTs7UUFBQSxHQUFBO0FBQUE7O0lBQUEsR0FBQTtBQUFBOzs7IiwieF9nb29nbGVfaWdub3JlTGlzdCI6WzBdfQ==
