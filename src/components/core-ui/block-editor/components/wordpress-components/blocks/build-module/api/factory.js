import _toConsumableArray from "@babel/runtime/helpers/esm/toConsumableArray";
import _defineProperty from "@babel/runtime/helpers/esm/defineProperty";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/**
 * External dependencies
 */
import { v4 as uuid } from 'uuid';
import { every, reduce, castArray, findIndex, isObjectLike, filter, first, flatMap, has, uniq, isFunction, isEmpty, map } from 'lodash';
/**
 * WordPress dependencies
 */

import { createHooks, applyFilters } from '@wordpress/hooks';
/**
 * Internal dependencies
 */

import { getBlockType, getBlockTypes, getGroupingBlockName } from './registration';
import { normalizeBlockType } from './utils';
/**
 * Returns a block object given its type and attributes.
 *
 * @param {string} name        Block name.
 * @param {Object} attributes  Block attributes.
 * @param {?Array} innerBlocks Nested blocks.
 *
 * @return {Object} Block object.
 */

export function createBlock(name) {
  var attributes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var innerBlocks = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
  // Get the type definition associated with a registered block.
  var blockType = getBlockType(name);

  if (undefined === blockType) {
    throw new Error("Block type '".concat(name, "' is not registered."));
  } // Ensure attributes contains only values defined by block type, and merge
  // default values for missing attributes.


  var sanitizedAttributes = reduce(blockType.attributes, function (accumulator, schema, key) {
    var value = attributes[key];

    if (undefined !== value) {
      accumulator[key] = value;
    } else if (schema.hasOwnProperty('default')) {
      accumulator[key] = schema.default;
    }

    if (['node', 'children'].indexOf(schema.source) !== -1) {
      // Ensure value passed is always an array, which we're expecting in
      // the RichText component to handle the deprecated value.
      if (typeof accumulator[key] === 'string') {
        accumulator[key] = [accumulator[key]];
      } else if (!Array.isArray(accumulator[key])) {
        accumulator[key] = [];
      }
    }

    return accumulator;
  }, {});
  var clientId = uuid(); // Blocks are stored with a unique ID, the assigned type name, the block
  // attributes, and their inner blocks.

  return {
    clientId: clientId,
    name: name,
    isValid: true,
    attributes: sanitizedAttributes,
    innerBlocks: innerBlocks
  };
}
/**
 * Given a block object, returns a copy of the block object, optionally merging
 * new attributes and/or replacing its inner blocks.
 *
 * @param {Object} block              Block instance.
 * @param {Object} mergeAttributes    Block attributes.
 * @param {?Array} newInnerBlocks     Nested blocks.
 *
 * @return {Object} A cloned block.
 */

export function cloneBlock(block) {
  var mergeAttributes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var newInnerBlocks = arguments.length > 2 ? arguments[2] : undefined;
  var clientId = uuid();
  return _objectSpread({}, block, {
    clientId: clientId,
    attributes: _objectSpread({}, block.attributes, {}, mergeAttributes),
    innerBlocks: newInnerBlocks || block.innerBlocks.map(function (innerBlock) {
      return cloneBlock(innerBlock);
    })
  });
}
/**
 * Returns a boolean indicating whether a transform is possible based on
 * various bits of context.
 *
 * @param {Object} transform The transform object to validate.
 * @param {string} direction Is this a 'from' or 'to' transform.
 * @param {Array} blocks The blocks to transform from.
 *
 * @return {boolean} Is the transform possible?
 */

var isPossibleTransformForSource = function isPossibleTransformForSource(transform, direction, blocks) {
  if (isEmpty(blocks)) {
    return false;
  } // If multiple blocks are selected, only multi block transforms
  // or wildcard transforms are allowed.


  var isMultiBlock = blocks.length > 1;
  var firstBlockName = first(blocks).name;
  var isValidForMultiBlocks = isWildcardBlockTransform(transform) || !isMultiBlock || transform.isMultiBlock;

  if (!isValidForMultiBlocks) {
    return false;
  } // Check non-wildcard transforms to ensure that transform is valid
  // for a block selection of multiple blocks of different types


  if (!isWildcardBlockTransform(transform) && !every(blocks, {
    name: firstBlockName
  })) {
    return false;
  } // Only consider 'block' type transforms as valid.


  var isBlockType = transform.type === 'block';

  if (!isBlockType) {
    return false;
  } // Check if the transform's block name matches the source block (or is a wildcard)
  // only if this is a transform 'from'.


  var sourceBlock = first(blocks);
  var hasMatchingName = direction !== 'from' || transform.blocks.indexOf(sourceBlock.name) !== -1 || isWildcardBlockTransform(transform);

  if (!hasMatchingName) {
    return false;
  } // Don't allow single Grouping blocks to be transformed into
  // a Grouping block.


  if (!isMultiBlock && isContainerGroupBlock(sourceBlock.name) && isContainerGroupBlock(transform.blockName)) {
    return false;
  } // If the transform has a `isMatch` function specified, check that it returns true.


  if (isFunction(transform.isMatch)) {
    var attributes = transform.isMultiBlock ? blocks.map(function (block) {
      return block.attributes;
    }) : sourceBlock.attributes;

    if (!transform.isMatch(attributes)) {
      return false;
    }
  }

  return true;
};
/**
 * Returns block types that the 'blocks' can be transformed into, based on
 * 'from' transforms on other blocks.
 *
 * @param {Array}  blocks  The blocks to transform from.
 *
 * @return {Array} Block types that the blocks can be transformed into.
 */


var getBlockTypesForPossibleFromTransforms = function getBlockTypesForPossibleFromTransforms(blocks) {
  if (isEmpty(blocks)) {
    return [];
  }

  var allBlockTypes = getBlockTypes(); // filter all blocks to find those with a 'from' transform.

  var blockTypesWithPossibleFromTransforms = filter(allBlockTypes, function (blockType) {
    var fromTransforms = getBlockTransforms('from', blockType.name);
    return !!findTransform(fromTransforms, function (transform) {
      return isPossibleTransformForSource(transform, 'from', blocks);
    });
  });
  return blockTypesWithPossibleFromTransforms;
};
/**
 * Returns block types that the 'blocks' can be transformed into, based on
 * the source block's own 'to' transforms.
 *
 * @param {Array} blocks The blocks to transform from.
 *
 * @return {Array} Block types that the source can be transformed into.
 */


var getBlockTypesForPossibleToTransforms = function getBlockTypesForPossibleToTransforms(blocks) {
  if (isEmpty(blocks)) {
    return [];
  }

  var sourceBlock = first(blocks);
  var blockType = getBlockType(sourceBlock.name);
  var transformsTo = getBlockTransforms('to', blockType.name); // filter all 'to' transforms to find those that are possible.

  var possibleTransforms = filter(transformsTo, function (transform) {
    return transform && isPossibleTransformForSource(transform, 'to', blocks);
  }); // Build a list of block names using the possible 'to' transforms.

  var blockNames = flatMap(possibleTransforms, function (transformation) {
    return transformation.blocks;
  }); // Map block names to block types.

  return blockNames.map(function (name) {
    return getBlockType(name);
  });
};
/**
 * Determines whether transform is a "block" type
 * and if so whether it is a "wildcard" transform
 * ie: targets "any" block type
 *
 * @param {Object} t the Block transform object
 *
 * @return {boolean} whether transform is a wildcard transform
 */


export var isWildcardBlockTransform = function isWildcardBlockTransform(t) {
  return t && t.type === 'block' && Array.isArray(t.blocks) && t.blocks.includes('*');
};
/**
 * Determines whether the given Block is the core Block which
 * acts as a container Block for other Blocks as part of the
 * Grouping mechanics
 *
 * @param  {string} name the name of the Block to test against
 *
 * @return {boolean} whether or not the Block is the container Block type
 */

export var isContainerGroupBlock = function isContainerGroupBlock(name) {
  return name === getGroupingBlockName();
};
/**
 * Returns an array of block types that the set of blocks received as argument
 * can be transformed into.
 *
 * @param {Array} blocks Blocks array.
 *
 * @return {Array} Block types that the blocks argument can be transformed to.
 */

export function getPossibleBlockTransformations(blocks) {
  if (isEmpty(blocks)) {
    return [];
  }

  var blockTypesForFromTransforms = getBlockTypesForPossibleFromTransforms(blocks);
  var blockTypesForToTransforms = getBlockTypesForPossibleToTransforms(blocks);
  return uniq([].concat(_toConsumableArray(blockTypesForFromTransforms), _toConsumableArray(blockTypesForToTransforms)));
}
/**
 * Given an array of transforms, returns the highest-priority transform where
 * the predicate function returns a truthy value. A higher-priority transform
 * is one with a lower priority value (i.e. first in priority order). Returns
 * null if the transforms set is empty or the predicate function returns a
 * falsey value for all entries.
 *
 * @param {Object[]} transforms Transforms to search.
 * @param {Function} predicate  Function returning true on matching transform.
 *
 * @return {?Object} Highest-priority transform candidate.
 */

export function findTransform(transforms, predicate) {
  // The hooks library already has built-in mechanisms for managing priority
  // queue, so leverage via locally-defined instance.
  var hooks = createHooks();

  var _loop = function _loop(i) {
    var candidate = transforms[i];

    if (predicate(candidate)) {
      hooks.addFilter('transform', 'transform/' + i.toString(), function (result) {
        return result ? result : candidate;
      }, candidate.priority);
    }
  };

  for (var i = 0; i < transforms.length; i++) {
    _loop(i);
  } // Filter name is arbitrarily chosen but consistent with above aggregation.


  return hooks.applyFilters('transform', null);
}
/**
 * Returns normal block transforms for a given transform direction, optionally
 * for a specific block by name, or an empty array if there are no transforms.
 * If no block name is provided, returns transforms for all blocks. A normal
 * transform object includes `blockName` as a property.
 *
 * @param {string}  direction Transform direction ("to", "from").
 * @param {string|Object} blockTypeOrName  Block type or name.
 *
 * @return {Array} Block transforms for direction.
 */

export function getBlockTransforms(direction, blockTypeOrName) {
  // When retrieving transforms for all block types, recurse into self.
  if (blockTypeOrName === undefined) {
    return flatMap(getBlockTypes(), function (_ref) {
      var name = _ref.name;
      return getBlockTransforms(direction, name);
    });
  } // Validate that block type exists and has array of direction.


  var blockType = normalizeBlockType(blockTypeOrName);

  var _ref2 = blockType || {},
      blockName = _ref2.name,
      transforms = _ref2.transforms;

  if (!transforms || !Array.isArray(transforms[direction])) {
    return [];
  } // Map transforms to normal form.


  return transforms[direction].map(function (transform) {
    return _objectSpread({}, transform, {
      blockName: blockName
    });
  });
}
/**
 * Switch one or more blocks into one or more blocks of the new block type.
 *
 * @param {Array|Object} blocks Blocks array or block object.
 * @param {string}       name   Block name.
 *
 * @return {?Array} Array of blocks or null.
 */

export function switchToBlockType(blocks, name) {
  var blocksArray = castArray(blocks);
  var isMultiBlock = blocksArray.length > 1;
  var firstBlock = blocksArray[0];
  var sourceName = firstBlock.name; // Find the right transformation by giving priority to the "to"
  // transformation.

  var transformationsFrom = getBlockTransforms('from', name);
  var transformationsTo = getBlockTransforms('to', sourceName);
  var transformation = findTransform(transformationsTo, function (t) {
    return t.type === 'block' && (isWildcardBlockTransform(t) || t.blocks.indexOf(name) !== -1) && (!isMultiBlock || t.isMultiBlock);
  }) || findTransform(transformationsFrom, function (t) {
    return t.type === 'block' && (isWildcardBlockTransform(t) || t.blocks.indexOf(sourceName) !== -1) && (!isMultiBlock || t.isMultiBlock);
  }); // Stop if there is no valid transformation.

  if (!transformation) {
    return null;
  }

  var transformationResults;

  if (transformation.isMultiBlock) {
    if (has(transformation, '__experimentalConvert')) {
      transformationResults = transformation.__experimentalConvert(blocksArray);
    } else {
      transformationResults = transformation.transform(blocksArray.map(function (currentBlock) {
        return currentBlock.attributes;
      }), blocksArray.map(function (currentBlock) {
        return currentBlock.innerBlocks;
      }));
    }
  } else if (has(transformation, '__experimentalConvert')) {
    transformationResults = transformation.__experimentalConvert(firstBlock);
  } else {
    transformationResults = transformation.transform(firstBlock.attributes, firstBlock.innerBlocks);
  } // Ensure that the transformation function returned an object or an array
  // of objects.


  if (!isObjectLike(transformationResults)) {
    return null;
  } // If the transformation function returned a single object, we want to work
  // with an array instead.


  transformationResults = castArray(transformationResults); // Ensure that every block object returned by the transformation has a
  // valid block type.

  if (transformationResults.some(function (result) {
    return !getBlockType(result.name);
  })) {
    return null;
  }

  var firstSwitchedBlock = findIndex(transformationResults, function (result) {
    return result.name === name;
  }); // Ensure that at least one block object returned by the transformation has
  // the expected "destination" block type.

  if (firstSwitchedBlock < 0) {
    return null;
  }

  return transformationResults.map(function (result, index) {
    var transformedBlock = _objectSpread({}, result, {
      // The first transformed block whose type matches the "destination"
      // type gets to keep the existing client ID of the first block.
      clientId: index === firstSwitchedBlock ? firstBlock.clientId : result.clientId
    });
    /**
     * Filters an individual transform result from block transformation.
     * All of the original blocks are passed, since transformations are
     * many-to-many, not one-to-one.
     *
     * @param {Object}   transformedBlock The transformed block.
     * @param {Object[]} blocks           Original blocks transformed.
     */


    return applyFilters('blocks.switchToBlockType.transformedBlock', transformedBlock, blocks);
  });
}
/**
 * Create a block object from the example API.
 *
 * @param {string} name
 * @param {Object} example
 *
 * @return {Object} block.
 */

export var getBlockFromExample = function getBlockFromExample(name, example) {
  return createBlock(name, example.attributes, map(example.innerBlocks, function (innerBlock) {
    return getBlockFromExample(innerBlock.name, innerBlock);
  }));
};
//# sourceMappingURL=factory.js.map