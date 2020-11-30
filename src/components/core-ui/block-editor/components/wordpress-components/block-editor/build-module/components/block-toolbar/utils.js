import _slicedToArray from "@babel/runtime/helpers/esm/slicedToArray";

/**
 * External dependencies
 */
import { noop } from 'lodash';
/**
 * WordPress dependencies
 */

import { useState, useRef, useEffect } from '@wordpress/element';
var _window = window,
    clearTimeout = _window.clearTimeout,
    setTimeout = _window.setTimeout;
var DEBOUNCE_TIMEOUT = 200;
/**
 * Hook that creates a showMover state, as well as debounced show/hide callbacks.
 *
 * @param {Object}   props                       Component props.
 * @param {Object}   props.ref                   Element reference.
 * @param {boolean}  props.isFocused             Whether the component has current focus.
 * @param {number}   [props.debounceTimeout=250] Debounce timeout in milliseconds.
 * @param {Function} [props.onChange=noop]       Callback function.
 */

export function useDebouncedShowMovers(_ref) {
  var ref = _ref.ref,
      isFocused = _ref.isFocused,
      _ref$debounceTimeout = _ref.debounceTimeout,
      debounceTimeout = _ref$debounceTimeout === void 0 ? DEBOUNCE_TIMEOUT : _ref$debounceTimeout,
      _ref$onChange = _ref.onChange,
      onChange = _ref$onChange === void 0 ? noop : _ref$onChange;

  var _useState = useState(false),
      _useState2 = _slicedToArray(_useState, 2),
      showMovers = _useState2[0],
      setShowMovers = _useState2[1];

  var timeoutRef = useRef();

  var handleOnChange = function handleOnChange(nextIsFocused) {
    if (ref === null || ref === void 0 ? void 0 : ref.current) {
      setShowMovers(nextIsFocused);
    }

    onChange(nextIsFocused);
  };

  var getIsHovered = function getIsHovered() {
    return (ref === null || ref === void 0 ? void 0 : ref.current) && ref.current.matches(':hover');
  };

  var shouldHideMovers = function shouldHideMovers() {
    var isHovered = getIsHovered();
    return !isFocused && !isHovered;
  };

  var clearTimeoutRef = function clearTimeoutRef() {
    var timeout = timeoutRef.current;

    if (timeout && clearTimeout) {
      clearTimeout(timeout);
    }
  };

  var debouncedShowMovers = function debouncedShowMovers(event) {
    if (event) {
      event.stopPropagation();
    }

    clearTimeoutRef();

    if (!showMovers) {
      handleOnChange(true);
    }
  };

  var debouncedHideMovers = function debouncedHideMovers(event) {
    if (event) {
      event.stopPropagation();
    }

    clearTimeoutRef();
    timeoutRef.current = setTimeout(function () {
      if (shouldHideMovers()) {
        handleOnChange(false);
      }
    }, debounceTimeout);
  };

  useEffect(function () {
    return function () {
      return clearTimeoutRef();
    };
  }, []);
  return {
    showMovers: showMovers,
    debouncedShowMovers: debouncedShowMovers,
    debouncedHideMovers: debouncedHideMovers
  };
}
/**
 * Hook that provides a showMovers state and gesture events for DOM elements
 * that interact with the showMovers state.
 *
 * @param {Object}   props                       Component props.
 * @param {Object}   props.ref                   Element reference.
 * @param {number}   [props.debounceTimeout=250] Debounce timeout in milliseconds.
 * @param {Function} [props.onChange=noop]       Callback function.
 */

export function useShowMoversGestures(_ref2) {
  var ref = _ref2.ref,
      _ref2$debounceTimeout = _ref2.debounceTimeout,
      debounceTimeout = _ref2$debounceTimeout === void 0 ? DEBOUNCE_TIMEOUT : _ref2$debounceTimeout,
      _ref2$onChange = _ref2.onChange,
      onChange = _ref2$onChange === void 0 ? noop : _ref2$onChange;

  var _useState3 = useState(false),
      _useState4 = _slicedToArray(_useState3, 2),
      isFocused = _useState4[0],
      setIsFocused = _useState4[1];

  var _useDebouncedShowMove = useDebouncedShowMovers({
    ref: ref,
    debounceTimeout: debounceTimeout,
    isFocused: isFocused,
    onChange: onChange
  }),
      showMovers = _useDebouncedShowMove.showMovers,
      debouncedShowMovers = _useDebouncedShowMove.debouncedShowMovers,
      debouncedHideMovers = _useDebouncedShowMove.debouncedHideMovers;

  var registerRef = useRef(false);

  var isFocusedWithin = function isFocusedWithin() {
    return (ref === null || ref === void 0 ? void 0 : ref.current) && ref.current.contains(ref.current.ownerDocument.activeElement);
  };

  useEffect(function () {
    var node = ref.current;

    var handleOnFocus = function handleOnFocus() {
      if (isFocusedWithin()) {
        setIsFocused(true);
        debouncedShowMovers();
      }
    };

    var handleOnBlur = function handleOnBlur() {
      if (!isFocusedWithin()) {
        setIsFocused(false);
        debouncedHideMovers();
      }
    };
    /**
     * Events are added via DOM events (vs. React synthetic events),
     * as the child React components swallow mouse events.
     */


    if (node && !registerRef.current) {
      node.addEventListener('focus', handleOnFocus, true);
      node.addEventListener('blur', handleOnBlur, true);
      registerRef.current = true;
    }

    return function () {
      if (node) {
        node.removeEventListener('focus', handleOnFocus);
        node.removeEventListener('blur', handleOnBlur);
      }
    };
  }, [ref, registerRef, setIsFocused, debouncedShowMovers, debouncedHideMovers]);
  return {
    showMovers: showMovers,
    gestures: {
      onMouseMove: debouncedShowMovers,
      onMouseLeave: debouncedHideMovers
    }
  };
}
//# sourceMappingURL=utils.js.map