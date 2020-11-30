"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = isURLLike;

var _lodash = require("lodash");

var _url = require("@wordpress/url");

/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */

/**
 * Determines whether a given value could be a URL. Note this does not
 * guarantee the value is a URL only that it looks like it might be one. For
 * example, just because a string has `www.` in it doesn't make it a URL,
 * but it does make it highly likely that it will be so in the context of
 * creating a link it makes sense to treat it like one.
 *
 * @param {string} val the candidate for being URL-like (or not).
 * @return {boolean}   whether or not the value is potentially a URL.
 */
function isURLLike(val) {
  var isInternal = (0, _lodash.startsWith)(val, '#');
  return (0, _url.isURL)(val) || val && val.includes('www.') || isInternal;
}
//# sourceMappingURL=is-url-like.js.map