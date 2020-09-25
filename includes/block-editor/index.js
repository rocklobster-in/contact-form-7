!function(t){var e={};function n(r){if(e[r])return e[r].exports;var o=e[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=t,n.c=e,n.d=function(t,e,r){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(r,o,function(e){return t[e]}.bind(null,o));return r},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=12)}([function(t,e){!function(){t.exports=this.wp.element}()},function(t,e){!function(){t.exports=this.wp.i18n}()},function(t,e){!function(){t.exports=this.wp.blocks}()},function(t,e,n){var r=n(7),o=n(8),c=n(9),i=n(11);t.exports=function(t,e){return r(t)||o(t,e)||c(t,e)||i()}},function(t,e){!function(){t.exports=this.wp.apiFetch}()},function(t,e){!function(){t.exports=this.wp.compose}()},function(t,e){!function(){t.exports=this.wp.components}()},function(t,e){t.exports=function(t){if(Array.isArray(t))return t}},function(t,e){t.exports=function(t,e){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(t)){var n=[],r=!0,o=!1,c=void 0;try{for(var i,a=t[Symbol.iterator]();!(r=(i=a.next()).done)&&(n.push(i.value),!e||n.length!==e);r=!0);}catch(t){o=!0,c=t}finally{try{r||null==a.return||a.return()}finally{if(o)throw c}}return n}}},function(t,e,n){var r=n(10);t.exports=function(t,e){if(t){if("string"==typeof t)return r(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?r(t,e):void 0}}},function(t,e){t.exports=function(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,r=new Array(e);n<e;n++)r[n]=t[n];return r}},function(t,e){t.exports=function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}},function(t,e,n){"use strict";n.r(e);var r=n(0),o=n(1),c=n(2),i=n(3),a=n.n(i),u=n(4),l=n.n(u),f=n(5),s=n(6),p=new Map;l()({path:"contact-form-7/v1/contact-forms"}).then((function(t){Object.entries(t).forEach((function(t){var e=a()(t,2),n=(e[0],e[1]);p.set(n.id,n)}))}));var d={from:[{type:"shortcode",tag:"contact-form-7",attributes:{id:{type:"integer",shortcode:function(t){var e=t.named.id;return parseInt(e)}},title:{type:"string",shortcode:function(t){return t.named.title}}}}],to:[{type:"block",blocks:["core/shortcode"],transform:function(t){return Object(c.createBlock)("core/shortcode",{text:'[contact-form-7 id="'.concat(t.id,'" title="').concat(t.title,'"]')})}}]};Object(c.registerBlockType)("contact-form-7/contact-form-selector",{title:Object(o.__)("Contact Form 7","contact-form-7"),description:Object(o.__)("Insert a contact form you have created with Contact Form 7.","contact-form-7"),icon:"email",category:"widgets",attributes:{id:{type:"integer"},title:{type:"string"}},transforms:d,edit:function t(e){var n=e.attributes,c=e.setAttributes;if(!p.size&&!n.id)return Object(r.createElement)("div",{className:"components-placeholder"},Object(r.createElement)("p",null,Object(o.__)("No contact forms were found. Create a contact form first.","contact-form-7")));var i=Array.from(p.values(),(function(t){return{value:t.id,label:t.title}}));if(n.id)i.length||i.push({value:n.id,label:n.title});else{var a=i[0];c({id:parseInt(a.value),title:a.label})}var u=Object(f.useInstanceId)(t),l="contact-form-7-contact-form-selector-".concat(u);return Object(r.createElement)("div",{className:"components-placeholder"},Object(r.createElement)("label",{htmlFor:l,className:"components-placeholder__label"},Object(o.__)("Select a contact form:","contact-form-7")),Object(r.createElement)(s.SelectControl,{id:l,options:i,value:n.id,onChange:function(t){return c({id:parseInt(t),title:p.get(parseInt(t)).title})}}))},save:function(t){var e=t.attributes;return Object(r.createElement)("div",null,'[contact-form-7 id="',e.id,'" title="',e.title,'"]')}})}]);