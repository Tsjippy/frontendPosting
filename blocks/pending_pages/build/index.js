(()=>{"use strict";var e={n:n=>{var t=n&&n.__esModule?()=>n.default:()=>n;return e.d(t,{a:t}),t},d:(n,t)=>{for(var i in t)e.o(t,i)&&!e.o(n,i)&&Object.defineProperty(n,i,{enumerable:!0,get:t[i]})},o:(e,n)=>Object.prototype.hasOwnProperty.call(e,n)};const n=window.wp.blocks,t=(window.wp.i18n,window.wp.blockEditor),i=window.wp.apiFetch;var o=e.n(i);const r=window.wp.element,s=window.ReactJSXRuntime,a=JSON.parse('{"UU":"sim/pendingpages"}');(0,n.registerBlockType)(a.UU,{icon:"admin-page",edit:()=>{const[e,n]=(0,r.useState)([]);return(0,r.useEffect)((()=>{!async function(){const e=await o()({path:sim.restApiPrefix+"/frontendposting/pending_pages"});n(e)}()}),[]),(0,s.jsx)(s.Fragment,{children:(0,s.jsx)("div",{...(0,t.useBlockProps)(),children:wp.element.RawHTML({children:e})})})},save:()=>null})})();