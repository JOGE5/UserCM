export default {
  mounted(el, binding) {
    const loader = document.createElement('span');
    loader.className = 'v-loading-spinner loader';
    loader.style.display = 'none';
    loader.style.marginLeft = '8px';
    el._v_loading_loader = loader;
    // ensure button has inline layout for loader placement
    if (getComputedStyle(el).display === 'inline') {
      el.style.display = 'inline-flex';
      el.style.alignItems = 'center';
    }
    el.appendChild(loader);
    updateState(el, binding.value);
  },
  updated(el, binding) {
    updateState(el, binding.value);
  },
  unmounted(el) {
    if (el._v_loading_loader && el.contains(el._v_loading_loader)) {
      el.removeChild(el._v_loading_loader);
    }
    delete el._v_loading_loader;
  }
};

function updateState(el, value) {
  const loader = el._v_loading_loader;
  if (!loader) return;
  if (value) {
    el.setAttribute('disabled', 'disabled');
    loader.style.display = 'inline-block';
  } else {
    el.removeAttribute('disabled');
    loader.style.display = 'none';
  }
}
