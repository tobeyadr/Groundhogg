import { createElement } from "@wordpress/element";

/**
 * WordPress dependencies
 */
import { SVG, Rect } from '@wordpress/components';
export var buttonOnly = createElement(SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, createElement(Rect, {
  x: "7",
  y: "10",
  width: "10",
  height: "4",
  rx: "1",
  fill: "currentColor"
}));
export var buttonOutside = createElement(SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, createElement(Rect, {
  x: "4.75",
  y: "15.25",
  width: "6.5",
  height: "9.5",
  transform: "rotate(-90 4.75 15.25)",
  stroke: "currentColor",
  "stroke-width": "1.5",
  fill: "none"
}), createElement(Rect, {
  x: "16",
  y: "10",
  width: "4",
  height: "4",
  rx: "1",
  fill: "currentColor"
}));
export var buttonInside = createElement(SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, createElement(Rect, {
  x: "4.75",
  y: "15.25",
  width: "6.5",
  height: "14.5",
  transform: "rotate(-90 4.75 15.25)",
  stroke: "currentColor",
  "stroke-width": "1.5",
  fill: "none"
}), createElement(Rect, {
  x: "14",
  y: "10",
  width: "4",
  height: "4",
  rx: "1",
  fill: "currentColor"
}));
export var noButton = createElement(SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, createElement(Rect, {
  x: "4.75",
  y: "15.25",
  width: "6.5",
  height: "14.5",
  transform: "rotate(-90 4.75 15.25)",
  stroke: "currentColor",
  fill: "none",
  "stroke-width": "1.5"
}));
export var buttonWithIcon = createElement(SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, createElement(Rect, {
  x: "4.75",
  y: "7.75",
  width: "14.5",
  height: "8.5",
  rx: "1.25",
  stroke: "currentColor",
  fill: "none",
  "stroke-width": "1.5"
}), createElement(Rect, {
  x: "8",
  y: "11",
  width: "8",
  height: "2",
  fill: "currentColor"
}));
export var toggleLabel = createElement(SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, createElement(Rect, {
  x: "4.75",
  y: "17.25",
  width: "5.5",
  height: "14.5",
  transform: "rotate(-90 4.75 17.25)",
  stroke: "currentColor",
  fill: "none",
  "stroke-width": "1.5"
}), createElement(Rect, {
  x: "4",
  y: "7",
  width: "10",
  height: "2",
  fill: "currentColor"
}));
//# sourceMappingURL=icons.js.map