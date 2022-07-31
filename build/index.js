(()=>{"use strict";const e=window.wp.blocks,i=window.wp.plugins,t=window.wp.element,r=window.wp.i18n,n=window.wp.components,o=window.wp.editPost,c=window.wp.data,d=window.wp.coreData,l=window.wp.primitives,s=(0,t.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,t.createElement)(l.Path,{fillRule:"evenodd",d:"M18.646 9H20V8l-1-.5L12 4 5 7.5 4 8v1h14.646zm-3-1.5L12 5.677 8.354 7.5h7.292zm-7.897 9.44v-6.5h-1.5v6.5h1.5zm5-6.5v6.5h-1.5v-6.5h1.5zm5 0v6.5h-1.5v-6.5h1.5zm2.252 8.81c0 .414-.334.75-.748.75H4.752a.75.75 0 010-1.5h14.5a.75.75 0 01.749.75z",clipRule:"evenodd"})),a=window.wp.blockEditor,w=JSON.parse('{"u2":"credibility-indicators/credibility-indicators"}');(0,e.registerBlockType)(w.u2,{edit:function(e){let{attributes:i,setAttributes:o}=e;const c=(0,a.useBlockProps)();return(0,t.createElement)("div",c,(0,t.createElement)(n.Placeholder,{label:(0,r.__)("Credibility Indicators","credibility-indicators"),icon:s}))}}),(0,i.registerPlugin)("credibility-indicator-panel",{render:()=>{const e=(0,c.useSelect)((()=>(0,c.select)("core/editor").getCurrentPostType()),[]);if(!["post"].includes(e))return null;const[i,l]=(0,d.useEntityProp)("postType",e,"meta"),{credibility_indicators:s}=i,a=(0,c.useSelect)((e=>e("core/editor").getEditedPostAttribute("credibility_indicators")),[]),w={...Object.fromEntries(new Map(a.map((e=>{let{slug:i}=e;return[i,!1]})))),...i.credibility_indicators};return(0,t.createElement)(o.PluginDocumentSettingPanel,{title:(0,r.__)("Credibility Indicators","credibility-indicators"),icon:!1},a.map((e=>{let{description:r,label:o,slug:c}=e;return(0,t.createElement)(n.CheckboxControl,{key:c,label:o,help:r,checked:w[c],onChange:()=>{const e={...i,credibility_indicators:{...w,[c]:!w[c]}};l(e)}})})))}}),window.addEventListener("load",(()=>{wp.data.dispatch("core/edit-post").hideBlockTypes(["credibility-indicators/credibility-indicators"])}))})();