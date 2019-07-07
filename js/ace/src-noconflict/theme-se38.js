ace.define("ace/theme/se38",["require","exports","module","ace/lib/dom"], function(require, exports, module) {

exports.isDark = false;
exports.cssClass = "ace-se38";
exports.cssText = "\
.ace-se38 .ace_gutter {\
background: #e8e8e8;\
color: #AAA;\
}\
.ace-se38  {\
background: #fff;\
color: #000;\
font-family: \"Courier New\";\
}\
.ace-se38 .ace_keyword {\
font-weight: bold;\
color: #0000FF;\
}\
.ace-se38 .ace_string {\
color: #4DA619;\
}\
.ace-se38 .ace_variable.ace_class {\
color: teal;\
}\
.ace-se38 .ace_constant.ace_numeric {\
color: #099;\
}\
.ace-se38 .ace_constant.ace_buildin {\
color: #6457E5;\
}\
.ace-se38 .ace_support.ace_function {\
color: #6457E5;\
}\
.ace-se38 .ace_comment {\
color: #998;\
font-style: italic;\
}\
.ace-se38 .ace_variable.ace_language  {\
color: #6457E5;\
}\
.ace-se38 .ace_paren {\
font-weight: bold;\
}\
.ace-se38 .ace_boolean {\
font-weight: bold;\
}\
.ace-se38 .ace_string.ace_regexp {\
color: #009926;\
font-weight: normal;\
}\
.ace-se38 .ace_variable.ace_instance {\
color: teal;\
}\
.ace-se38 .ace_constant.ace_language {\
font-weight: bold;\
}\
.ace-se38 .ace_cursor {\
color: black;\
}\
.ace-se38.ace_focus .ace_marker-layer .ace_active-line {\
// background: rgb(255, 255, 204);\
background: rgb(217, 229, 242);\
}\
.ace-se38 .ace_marker-layer .ace_active-line {\
background: rgb(245, 245, 245);\
}\
.ace-se38 .ace_marker-layer .ace_selection {\
background: rgb(115, 177, 230);\
color: #fff !important;\
}\
.ace-se38.ace_multiselect .ace_selection.ace_start {\
box-shadow: 0 0 3px 0px white;\
}\
.ace-se38.ace_nobold .ace_line > span {\
font-weight: normal !important;\
}\
.ace-se38 .ace_marker-layer .ace_step {\
background: rgb(252, 255, 0);\
}\
.ace-se38 .ace_marker-layer .ace_stack {\
background: rgb(164, 229, 101);\
}\
.ace-se38 .ace_marker-layer .ace_bracket {\
margin: -1px 0 0 -1px;\
border: 1px solid rgb(192, 192, 192);\
}\
.ace-se38 .ace_gutter-active-line {\
background-color : rgba(0, 0, 0, 0.07);\
}\
.ace-se38 .ace_marker-layer .ace_selected-word {\
background: rgb(250, 250, 255);\
border: 1px solid rgb(200, 200, 250);\
}\
.ace-se38 .ace_invisible {\
color: #BFBFBF\
}\
.ace-se38 .ace_print-margin {\
width: 1px;\
background: #e8e8e8;\
}\
.ace-se38 .ace_indent-guide {\
background: url(\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAE0lEQVQImWP4////f4bLly//BwAmVgd1/w11/gAAAABJRU5ErkJggg==\") right repeat-y;\
}";

    var dom = require("../lib/dom");
    dom.importCssString(exports.cssText, exports.cssClass);
});                (function() {
                    ace.require(["ace/theme/se38"], function(m) {
                        if (typeof module == "object" && typeof exports == "object" && module) {
                            module.exports = m;
                        }
                    });
                })();
            