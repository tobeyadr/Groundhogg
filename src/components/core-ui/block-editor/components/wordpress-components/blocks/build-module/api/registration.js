import _defineProperty from "@babel/runtime/helpers/esm/defineProperty";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/* eslint no-console: [ 'error', { allow: [ 'error', 'warn' ] } ] */

/**
 * External dependencies
 */
import { get, isFunction, isNil, isPlainObject, omit, pick, pickBy, some } from 'lodash';
/**
 * WordPress dependencies
 */

import { applyFilters } from '@wordpress/hooks';
import { select, dispatch } from '@wordpress/data';
import { blockDefault } from '@wordpress/icons';
/**
 * Internal dependencies
 */

import { isValidIcon, normalizeIconObject } from './utils';
import { DEPRECATED_ENTRY_KEYS } from './constants';
/**
 * An icon type definition. One of a Dashicon slug, an element,
 * or a component.
 *
 * @typedef {(string|WPElement|WPComponent)} WPIcon
 *
 * @see https://developer.wordpress.org/resource/dashicons/
 */

/**
 * Render behavior of a block type icon; one of a Dashicon slug, an element,
 * or a component.
 *
 * @typedef {WPIcon} WPBlockTypeIconRender
 */

/**
 * An object describing a normalized block type icon.
 *
 * @typedef {Object} WPBlockTypeIconDescriptor
 *
 * @property {WPBlockTypeIconRender} src         Render behavior of the icon,
 *                                               one of a Dashicon slug, an
 *                                               element, or a component.
 * @property {string}                background  Optimal background hex string
 *                                               color when displaying icon.
 * @property {string}                foreground  Optimal foreground hex string
 *                                               color when displaying icon.
 * @property {string}                shadowColor Optimal shadow hex string
 *                                               color when displaying icon.
 */

/**
 * Value to use to render the icon for a block type in an editor interface,
 * either a Dashicon slug, an element, a component, or an object describing
 * the icon.
 *
 * @typedef {(WPBlockTypeIconDescriptor|WPBlockTypeIconRender)} WPBlockTypeIcon
 */

/**
 * Named block variation scopes.
 *
 * @typedef {'block'|'inserter'} WPBlockVariationScope
 */

/**
 * An object describing a variation defined for the block type.
 *
 * @typedef {Object} WPBlockVariation
 *
 * @property {string}   name                   The unique and machine-readable name.
 * @property {string}   title                  A human-readable variation title.
 * @property {string}   [description]          A detailed variation description.
 * @property {WPIcon}   [icon]                 An icon helping to visualize the variation.
 * @property {boolean}  [isDefault]            Indicates whether the current variation is
 *                                             the default one. Defaults to `false`.
 * @property {Object}   [attributes]           Values which override block attributes.
 * @property {Array[]}  [innerBlocks]          Initial configuration of nested blocks.
 * @property {Object}   [example]              Example provides structured data for
 *                                             the block preview. You can set to
 *                                             `undefined` to disable the preview shown
 *                                             for the block type.
 * @property {WPBlockVariationScope[]} [scope] The list of scopes where the variation
 *                                             is applicable. When not provided, it
 *                                             assumes all available scopes.
 * @property {string[]} [keywords]             An array of terms (which can be translated)
 *                                             that help users discover the variation
 *                                             while searching.
 */

/**
 * Defined behavior of a block type.
 *
 * @typedef {Object} WPBlock
 *
 * @property {string}             name          Block type's namespaced name.
 * @property {string}             title         Human-readable block type label.
 * @property {string}             [description] A detailed block type description.
 * @property {string}             [category]    Block type category classification,
 *                                              used in search interfaces to arrange
 *                                              block types by category.
 * @property {WPBlockTypeIcon}    [icon]        Block type icon.
 * @property {string[]}           [keywords]    Additional keywords to produce block
 *                                              type as result in search interfaces.
 * @property {Object}             [attributes]  Block type attributes.
 * @property {WPComponent}        [save]        Optional component describing
 *                                              serialized markup structure of a
 *                                              block type.
 * @property {WPComponent}        edit          Component rendering an element to
 *                                              manipulate the attributes of a block
 *                                              in the context of an editor.
 * @property {WPBlockVariation[]} [variations]  The list of block variations.
 * @property {Object}             [example]     Example provides structured data for
 *                                              the block preview. When not defined
 *                                              then no preview is shown.
 */

/**
 * Mapping of legacy category slugs to their latest normal values, used to
 * accommodate updates of the default set of block categories.
 *
 * @type {Record<string,string>}
 */

var LEGACY_CATEGORY_MAPPING = {
  common: 'text',
  formatting: 'text',
  layout: 'design'
};
export var serverSideBlockDefinitions = {};
/**
 * Sets the server side block definition of blocks.
 *
 * @param {Object} definitions Server-side block definitions
 */
// eslint-disable-next-line camelcase

export function unstable__bootstrapServerSideBlockDefinitions(definitions) {
  serverSideBlockDefinitions = _objectSpread({}, serverSideBlockDefinitions, {}, definitions);
}
/**
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made available as an option to any
 * editor interface where blocks are implemented.
 *
 * @param {string} name     Block name.
 * @param {Object} settings Block settings.
 *
 * @return {?WPBlock} The block, if it has been successfully registered;
 *                    otherwise `undefined`.
 */

export function registerBlockType(name, settings) {
  settings = _objectSpread({
    name: name,
    icon: blockDefault,
    keywords: [],
    attributes: {},
    providesContext: {},
    usesContext: [],
    supports: {},
    styles: [],
    save: function save() {
      return null;
    }
  }, pickBy(get(serverSideBlockDefinitions, name, {}), function (value) {
    return !isNil(value);
  }), {}, settings);

  if (typeof name !== 'string') {
    console.error('Block names must be strings.');
    return;
  }

  if (!/^[a-z][a-z0-9-]*\/[a-z][a-z0-9-]*$/.test(name)) {
    console.error('Block names must contain a namespace prefix, include only lowercase alphanumeric characters or dashes, and start with a letter. Example: my-plugin/my-custom-block');
    return;
  }

  if (select('core/blocks').getBlockType(name)) {
    console.error('Block "' + name + '" is already registered.');
    return;
  }

  var preFilterSettings = _objectSpread({}, settings);

  settings = applyFilters('blocks.registerBlockType', settings, name);

  if (settings.deprecated) {
    settings.deprecated = settings.deprecated.map(function (deprecation) {
      return pick( // Only keep valid deprecation keys.
      applyFilters('blocks.registerBlockType', // Merge deprecation keys with pre-filter settings
      // so that filters that depend on specific keys being
      // present don't fail.
      _objectSpread({}, omit(preFilterSettings, DEPRECATED_ENTRY_KEYS), {}, deprecation), name), DEPRECATED_ENTRY_KEYS);
    });
  }

  if (!isPlainObject(settings)) {
    console.error('Block settings must be a valid object.');
    return;
  }

  if (!isFunction(settings.save)) {
    console.error('The "save" property must be a valid function.');
    return;
  }

  if ('edit' in settings && !isFunction(settings.edit)) {
    console.error('The "edit" property must be a valid function.');
    return;
  } // Canonicalize legacy categories to equivalent fallback.


  if (LEGACY_CATEGORY_MAPPING.hasOwnProperty(settings.category)) {
    settings.category = LEGACY_CATEGORY_MAPPING[settings.category];
  }

  if ('category' in settings && !some(select('core/blocks').getCategories(), {
    slug: settings.category
  })) {
    console.warn('The block "' + name + '" is registered with an invalid category "' + settings.category + '".');
    delete settings.category;
  }

  if (!('title' in settings) || settings.title === '') {
    console.error('The block "' + name + '" must have a title.');
    return;
  }

  if (typeof settings.title !== 'string') {
    console.error('Block titles must be strings.');
    return;
  }

  settings.icon = normalizeIconObject(settings.icon);

  if (!isValidIcon(settings.icon.src)) {
    console.error('The icon passed is invalid. ' + 'The icon should be a string, an element, a function, or an object following the specifications documented in https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#icon-optional');
    return;
  }

  dispatch('core/blocks').addBlockTypes(settings);
  return settings;
}
/**
 * Registers a new block collection to group blocks in the same namespace in the inserter.
 *
 * @param {string} namespace       The namespace to group blocks by in the inserter; corresponds to the block namespace.
 * @param {Object} settings        The block collection settings.
 * @param {string} settings.title  The title to display in the block inserter.
 * @param {Object} [settings.icon] The icon to display in the block inserter.
 */

export function registerBlockCollection(namespace, _ref) {
  var title = _ref.title,
      icon = _ref.icon;
  dispatch('core/blocks').addBlockCollection(namespace, title, icon);
}
/**
 * Unregisters a block collection
 *
 * @param {string} namespace The namespace to group blocks by in the inserter; corresponds to the block namespace
 *
 */

export function unregisterBlockCollection(namespace) {
  dispatch('core/blocks').removeBlockCollection(namespace);
}
/**
 * Unregisters a block.
 *
 * @param {string} name Block name.
 *
 * @return {?WPBlock} The previous block value, if it has been successfully
 *                    unregistered; otherwise `undefined`.
 */

export function unregisterBlockType(name) {
  var oldBlock = select('core/blocks').getBlockType(name);

  if (!oldBlock) {
    console.error('Block "' + name + '" is not registered.');
    return;
  }

  dispatch('core/blocks').removeBlockTypes(name);
  return oldBlock;
}
/**
 * Assigns name of block for handling non-block content.
 *
 * @param {string} blockName Block name.
 */

export function setFreeformContentHandlerName(blockName) {
  dispatch('core/blocks').setFreeformFallbackBlockName(blockName);
}
/**
 * Retrieves name of block handling non-block content, or undefined if no
 * handler has been defined.
 *
 * @return {?string} Block name.
 */

export function getFreeformContentHandlerName() {
  return select('core/blocks').getFreeformFallbackBlockName();
}
/**
 * Retrieves name of block used for handling grouping interactions.
 *
 * @return {?string} Block name.
 */

export function getGroupingBlockName() {
  return select('core/blocks').getGroupingBlockName();
}
/**
 * Assigns name of block handling unregistered block types.
 *
 * @param {string} blockName Block name.
 */

export function setUnregisteredTypeHandlerName(blockName) {
  dispatch('core/blocks').setUnregisteredFallbackBlockName(blockName);
}
/**
 * Retrieves name of block handling unregistered block types, or undefined if no
 * handler has been defined.
 *
 * @return {?string} Block name.
 */

export function getUnregisteredTypeHandlerName() {
  return select('core/blocks').getUnregisteredFallbackBlockName();
}
/**
 * Assigns the default block name.
 *
 * @param {string} name Block name.
 */

export function setDefaultBlockName(name) {
  dispatch('core/blocks').setDefaultBlockName(name);
}
/**
 * Assigns name of block for handling block grouping interactions.
 *
 * @param {string} name Block name.
 */

export function setGroupingBlockName(name) {
  dispatch('core/blocks').setGroupingBlockName(name);
}
/**
 * Retrieves the default block name.
 *
 * @return {?string} Block name.
 */

export function getDefaultBlockName() {
  return select('core/blocks').getDefaultBlockName();
}
/**
 * Returns a registered block type.
 *
 * @param {string} name Block name.
 *
 * @return {?Object} Block type.
 */

export function getBlockType(name) {
  return select('core/blocks').getBlockType(name);
}
/**
 * Returns all registered blocks.
 *
 * @return {Array} Block settings.
 */

export function getBlockTypes() {
  return select('core/blocks').getBlockTypes();
}
/**
 * Returns the block support value for a feature, if defined.
 *
 * @param  {(string|Object)} nameOrType      Block name or type object
 * @param  {string}          feature         Feature to retrieve
 * @param  {*}               defaultSupports Default value to return if not
 *                                           explicitly defined
 *
 * @return {?*} Block support value
 */

export function getBlockSupport(nameOrType, feature, defaultSupports) {
  return select('core/blocks').getBlockSupport(nameOrType, feature, defaultSupports);
}
/**
 * Returns true if the block defines support for a feature, or false otherwise.
 *
 * @param {(string|Object)} nameOrType      Block name or type object.
 * @param {string}          feature         Feature to test.
 * @param {boolean}         defaultSupports Whether feature is supported by
 *                                          default if not explicitly defined.
 *
 * @return {boolean} Whether block supports feature.
 */

export function hasBlockSupport(nameOrType, feature, defaultSupports) {
  return select('core/blocks').hasBlockSupport(nameOrType, feature, defaultSupports);
}
/**
 * Determines whether or not the given block is a reusable block. This is a
 * special block type that is used to point to a global block stored via the
 * API.
 *
 * @param {Object} blockOrType Block or Block Type to test.
 *
 * @return {boolean} Whether the given block is a reusable block.
 */

export function isReusableBlock(blockOrType) {
  return blockOrType.name === 'core/block';
}
/**
 * Returns an array with the child blocks of a given block.
 *
 * @param {string} blockName Name of block (example: “latest-posts”).
 *
 * @return {Array} Array of child block names.
 */

export var getChildBlockNames = function getChildBlockNames(blockName) {
  return select('core/blocks').getChildBlockNames(blockName);
};
/**
 * Returns a boolean indicating if a block has child blocks or not.
 *
 * @param {string} blockName Name of block (example: “latest-posts”).
 *
 * @return {boolean} True if a block contains child blocks and false otherwise.
 */

export var hasChildBlocks = function hasChildBlocks(blockName) {
  return select('core/blocks').hasChildBlocks(blockName);
};
/**
 * Returns a boolean indicating if a block has at least one child block with inserter support.
 *
 * @param {string} blockName Block type name.
 *
 * @return {boolean} True if a block contains at least one child blocks with inserter support
 *                   and false otherwise.
 */

export var hasChildBlocksWithInserterSupport = function hasChildBlocksWithInserterSupport(blockName) {
  return select('core/blocks').hasChildBlocksWithInserterSupport(blockName);
};
/**
 * Registers a new block style variation for the given block.
 *
 * @param {string} blockName      Name of block (example: “core/latest-posts”).
 * @param {Object} styleVariation Object containing `name` which is the class name applied to the block and `label` which identifies the variation to the user.
 */

export var registerBlockStyle = function registerBlockStyle(blockName, styleVariation) {
  dispatch('core/blocks').addBlockStyles(blockName, styleVariation);
};
/**
 * Unregisters a block style variation for the given block.
 *
 * @param {string} blockName          Name of block (example: “core/latest-posts”).
 * @param {string} styleVariationName Name of class applied to the block.
 */

export var unregisterBlockStyle = function unregisterBlockStyle(blockName, styleVariationName) {
  dispatch('core/blocks').removeBlockStyles(blockName, styleVariationName);
};
/**
 * Returns an array with the variations of a given block type.
 *
 * @param {string}                blockName Name of block (example: “core/columns”).
 * @param {WPBlockVariationScope} [scope]   Block variation scope name.
 *
 * @return {(WPBlockVariation[]|void)} Block variations.
 */

export var getBlockVariations = function getBlockVariations(blockName, scope) {
  return select('core/blocks').getBlockVariations(blockName, scope);
};
/**
 * Registers a new block variation for the given block type.
 *
 * @param {string}           blockName Name of the block (example: “core/columns”).
 * @param {WPBlockVariation} variation Object describing a block variation.
 */

export var registerBlockVariation = function registerBlockVariation(blockName, variation) {
  dispatch('core/blocks').addBlockVariations(blockName, variation);
};
/**
 * Unregisters a block variation defined for the given block type.
 *
 * @param {string} blockName     Name of the block (example: “core/columns”).
 * @param {string} variationName Name of the variation defined for the block.
 */

export var unregisterBlockVariation = function unregisterBlockVariation(blockName, variationName) {
  dispatch('core/blocks').removeBlockVariations(blockName, variationName);
};
//# sourceMappingURL=registration.js.map