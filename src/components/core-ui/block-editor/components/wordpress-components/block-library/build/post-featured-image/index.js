"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.settings = exports.name = exports.metadata = void 0;

var _i18n = require("@wordpress/i18n");

var _icons = require("@wordpress/icons");

var _edit = _interopRequireDefault(require("./edit"));

/**
 * WordPress dependencies
 */

/**
 * Internal dependencies
 */
var metadata = {
  name: "core/post-featured-image",
  category: "design",
  usesContext: ["postId"],
  supports: {
    html: false
  }
};
exports.metadata = metadata;
var name = metadata.name;
exports.name = name;
var settings = {
  title: (0, _i18n.__)('Post Featured Image'),
  description: (0, _i18n.__)("Display a post's featured image."),
  icon: _icons.postFeaturedImage,
  edit: _edit.default
};
exports.settings = settings;
//# sourceMappingURL=index.js.map