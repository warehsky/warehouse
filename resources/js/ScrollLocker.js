var keys = {37: 1, 38: 1, 39: 1, 40: 1};
function preventDefaultForScrollKeys(e) {
    if (keys[e.keyCode]) {
        preventDefault(e);
        return false;
    }
}
function preventDefault(e) {
    e.preventDefault();
}
var supportsPassive = false;
try {
  window.addEventListener("test", null, Object.defineProperty({}, 'passive', {
    get: function () { supportsPassive = true; } 
  }));
} catch(e) {}
var wheelOpt = supportsPassive ? { passive: false } : false;
var wheelEvent = 'onwheel' in document.createElement('div') ? 'wheel' : 'mousewheel';
export let ScrollLocker = {
    /**
     * @param ScrollModes scrollMode
     */
    disableScroll(scrollMode) {
        window.addEventListener(scrollMode.type, scrollMode.prevent, scrollMode.option);
    },
    /**
     * @param ScrollModes scrollMode
     */
    enableScroll(scrollMode) {
        window.removeEventListener(scrollMode.type, scrollMode.prevent, scrollMode.option);
    }
}
export const ScrollModes = {
    DOMMouseScroll:{
        type:'DOMMouseScroll',
        prevent:preventDefault,
        option:false
    },
    WheelScroll:{
        type:wheelEvent,
        prevent:preventDefault,
        option:wheelOpt
    },
    TouchMove:{
        type:'touchmove',
        prevent:preventDefault,
        option:wheelOpt
    },
    ByKeys:{
        type:'keydown',
        prevent:preventDefaultForScrollKeys,
        option:false
    }
}
