import _toConsumableArray from "@babel/runtime/helpers/esm/toConsumableArray";
import _defineProperty from "@babel/runtime/helpers/esm/defineProperty";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/**
 * External dependencies
 */
import { without } from 'lodash';
/**
 * WordPress dependencies
 */

import { addFilter } from '@wordpress/hooks';
import { hasBlockSupport } from '@wordpress/blocks';
import { WIDE_ALIGNMENTS } from '@wordpress/components';
var ALIGNMENTS = ['left', 'center', 'right'];
export { AlignmentHookSettingsProvider } from './align.js'; // Used to filter out blocks that don't support wide/full alignment on mobile

addFilter('blocks.registerBlockType', 'core/react-native-editor/align', function (settings, name) {
  if (!WIDE_ALIGNMENTS.supportedBlocks.includes(name) && hasBlockSupport(settings, 'align')) {
    var blockAlign = settings.supports.align;
    settings.supports = _objectSpread(_objectSpread({}, settings.supports), {}, {
      align: Array.isArray(blockAlign) ? without.apply(void 0, [blockAlign].concat(_toConsumableArray(Object.values(WIDE_ALIGNMENTS.alignments)))) : blockAlign,
      alignWide: false
    });
    settings.attributes = _objectSpread(_objectSpread({}, settings.attributes), {}, {
      align: {
        type: 'string',
        // Allow for '' since it is used by updateAlignment function
        // in withToolbarControls for special cases with defined default values.
        enum: [].concat(ALIGNMENTS, [''])
      }
    });
  }

  return settings;
});
//# sourceMappingURL=align.native.js.map