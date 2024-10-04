(()=>{"use strict";const e=window.wp.element,t=window.wp.components,n=window.wp.data,a=window.wp.editPost,i=window.wp.coreData,{__}=wp.i18n,{registerPlugin:o}=wp.plugins;o("expiry-date",{render:function(){const o=(0,n.useSelect)((e=>e("core/editor").getCurrentPostType()),[]);if(null==o)return"";const[c,r]=(0,i.useEntityProp)("postType",o,"meta"),s=c.expirydate,l=c.static_content,p=(e,t)=>{let n={...c};n[t]=e,r(n)};return(0,e.createElement)(e.Fragment,null,(0,e.createElement)(a.PluginDocumentSettingPanel,{name:"expiry-dates",title:__("Expiry date","sim"),className:"expiry-date"},(0,e.createElement)(t.DatePicker,{currentDate:s,value:s,onChange:e=>p(e,"expirydate")})),(0,e.createElement)(a.PluginDocumentSettingPanel,{name:"static_content",title:__("Static content","sim"),className:"static_content"},(0,e.createElement)(t.ToggleControl,{label:__("Do not send update warnings for this page","sim"),checked:l,onChange:e=>p(e,"static_content")})))},icon:!1})})();