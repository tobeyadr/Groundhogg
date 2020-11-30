import _defineProperty from "@babel/runtime/helpers/esm/defineProperty";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/**
 * External dependencies
 */
import { every, has, isFunction, isString } from 'lodash';
import { default as tinycolor, mostReadable } from 'tinycolor2';
/**
 * WordPress dependencies
 */

import { Component, isValidElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { __unstableStripHTML as stripHTML } from '@wordpress/dom';
/**
 * Internal dependencies
 */

import { getBlockType, getDefaultBlockName } from './registration';
import { createBlock } from './factory';
/**
 * Array of icon colors containing a color to be used if the icon color
 * was not explicitly set but the icon background color was.
 *
 * @type {Object}
 */

var ICON_COLORS = ['#191e23', '#f8f9f9'];
/**
 * Determines whether the block is a default block
 * and its attributes are equal to the default attributes
 * which means the block is unmodified.
 *
 * @param  {WPBlock} block Block Object
 *
 * @return {boolean}       Whether the block is an unmodified default block
 */

export function isUnmodifiedDefaultBlock(block) {
  var defaultBlockName = getDefaultBlockName();

  if (block.name !== defaultBlockName) {
    return false;
  } // Cache a created default block if no cache exists or the default block
  // name changed.


  if (!isUnmodifiedDefaultBlock.block || isUnmodifiedDefaultBlock.block.name !== defaultBlockName) {
    isUnmodifiedDefaultBlock.block = createBlock(defaultBlockName);
  }

  var newDefaultBlock = isUnmodifiedDefaultBlock.block;
  var blockType = getBlockType(defaultBlockName);
  return every(blockType.attributes, function (value, key) {
    return newDefaultBlock.attributes[key] === block.attributes[key];
  });
}
/**
 * Function that checks if the parameter is a valid icon.
 *
 * @param {*} icon  Parameter to be checked.
 *
 * @return {boolean} True if the parameter is a valid icon and false otherwise.
 */

export function isValidIcon(icon) {
  return !!icon && (isString(icon) || isValidElement(icon) || isFunction(icon) || icon instanceof Component);
}
/**
 * Function that receives an icon as set by the blocks during the registration
 * and returns a new icon object that is normalized so we can rely on just on possible icon structure
 * in the codebase.
 *
 * @param {WPBlockTypeIconRender} icon Render behavior of a block type icon;
 *                                     one of a Dashicon slug, an element, or a
 *                                     component.
 *
 * @return {WPBlockTypeIconDescriptor} Object describing the icon.
 */

export function normalizeIconObject(icon) {
  if (isValidIcon(icon)) {
    return {
      src: icon
    };
  }

  if (has(icon, ['background'])) {
    var tinyBgColor = tinycolor(icon.background);
    return _objectSpread({}, icon, {
      foreground: icon.foreground ? icon.foreground : mostReadable(tinyBgColor, ICON_COLORS, {
        includeFallbackColors: true,
        level: 'AA',
        size: 'large'
      }).toHexString(),
      shadowColor: tinyBgColor.setAlpha(0.3).toRgbString()
    });
  }

  return icon;
}
/**
 * Normalizes block type passed as param. When string is passed then
 * it converts it to the matching block type object.
 * It passes the original object otherwise.
 *
 * @param {string|Object} blockTypeOrName  Block type or name.
 *
 * @return {?Object} Block type.
 */

export function normalizeBlockType(blockTypeOrName) {
  if (isString(blockTypeOrName)) {
    return getBlockType(blockTypeOrName);
  }

  return blockTypeOrName;
}
/**
 * Get the label for the block, usually this is either the block title,
 * or the value of the block's `label` function when that's specified.
 *
 * @param {Object} blockType  The block type.
 * @param {Object} attributes The values of the block's attributes.
 * @param {Object} context    The intended use for the label.
 *
 * @return {string} The block label.
 */

export function getBlockLabel(blockType, attributes) {
  var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'visual';
  var getLabel = blockType.__experimentalLabel,
      title = blockType.title;
  var label = getLabel && getLabel(attributes, {
    context: context
  });

  if (!label) {
    return title;
  } // Strip any HTML (i.e. RichText formatting) before returning.


  return stripHTML(label);
}
/**
 * Get a label for the block for use by screenreaders, this is more descriptive
 * than the visual label and includes the block title and the value of the
 * `getLabel` function if it's specified.
 *
 * @param {Object}  blockType              The block type.
 * @param {Object}  attributes             The values of the block's attributes.
 * @param {?number} position               The position of the block in the block list.
 * @param {string}  [direction='vertical'] The direction of the block layout.
 *
 * @return {string} The block label.
 */

export function getAccessibleBlockLabel(blockType, attributes, position) {
  var direction = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'vertical';
  // `title` is already localized, `label` is a user-supplied value.
  var title = blockType.title;
  var label = getBlockLabel(blockType, attributes, 'accessibility');
  var hasPosition = position !== undefined; // getBlockLabel returns the block title as a fallback when there's no label,
  // if it did return the title, this function needs to avoid adding the
  // title twice within the accessible label. Use this `hasLabel` boolean to
  // handle that.

  var hasLabel = label && label !== title;

  if (hasPosition && direction === 'vertical') {
    if (hasLabel) {
      return sprintf(
      /* translators: accessibility text. 1: The block title. 2: The block row number. 3: The block label.. */
      __('%1$s Block. Row %2$d. %3$s'), title, position, label);
    }

    return sprintf(
    /* translators: accessibility text. 1: The block title. 2: The block row number. */
    __('%1$s Block. Row %2$d'), title, position);
  } else if (hasPosition && direction === 'horizontal') {
    if (hasLabel) {
      return sprintf(
      /* translators: accessibility text. 1: The block title. 2: The block column number. 3: The block label.. */
      __('%1$s Block. Column %2$d. %3$s'), title, position, label);
    }

    return sprintf(
    /* translators: accessibility text. 1: The block title. 2: The block column number. */
    __('%1$s Block. Column %2$d'), title, position);
  }

  if (hasLabel) {
    return sprintf(
    /* translators: accessibility text. %1: The block title. %2: The block label. */
    __('%1$s Block. %2$s'), title, label);
  }

  return sprintf(
  /* translators: accessibility text. %s: The block title. */
  __('%s Block'), title);
}
//# sourceMappingURL=utils.js.map