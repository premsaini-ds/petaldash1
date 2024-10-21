(()=>{"use strict";const e=window.React,t=window.wp.i18n,l=window.wp.blocks,o=window.wp.blockEditor,a=window.wp.components;(0,l.registerBlockType)("lightweight-accordion/lightweight-accordion",{title:(0,t.__)("Lightweight Accordion"),category:"widgets",icon:"editor-ul",keywords:[(0,t.__)("accordion"),(0,t.__)("list"),(0,t.__)("collapse"),(0,t.__)("collapsable")],supports:{anchor:!0},attributes:{content:{type:"array",source:"children"},anchor:{type:"string",default:null},title:{type:"string",default:null},title_tag:{type:"string",default:"span"},title_text_color:{type:"string",default:""},title_background_color:{type:"string",default:""},accordion_open:{type:"boolean",default:!1},bordered:{type:"boolean",default:!1},schema:{type:"string",default:!1}},example:{attributes:{title:(0,t.__)("Accordion Title"),bordered:!0}},edit:function({attributes:l,setAttributes:n,className:r}){const{title:c,title_tag:i,title_text_color:d,title_background_color:s,accordion_open:u,bordered:_,schema:g}=l;return(0,e.createElement)("div",{className:"lightweight-accordion "+(_?"bordered":""),id:l.anchor},(0,e.createElement)("summary",{className:`lightweight-accordion-title ${r}`,style:{color:d,background:s}},(0,e.createElement)(o.RichText,{tagName:i,value:c,onChange:e=>n({title:e}),placeholder:(0,t.__)("Accordion title..."),allowedFormats:["core/bold","core/italic"]})),(0,e.createElement)("div",{className:`lightweight-accordion-body ${r}`,style:{borderColor:s}},(0,e.createElement)(o.InnerBlocks,null)),(0,e.createElement)(o.InspectorControls,null,(0,e.createElement)(a.PanelBody,null,(0,e.createElement)(a.ToggleControl,{label:(0,t.__)("Open by default?"),checked:u,onChange:e=>n({accordion_open:e})}),(0,e.createElement)(a.ToggleControl,{label:(0,t.__)("Border?"),checked:_,onChange:e=>n({bordered:e})}),(0,e.createElement)(a.SelectControl,{label:(0,t.__)("HTML tag for accordion title"),value:i,onChange:e=>n({title_tag:e}),options:[{value:"span",label:"span"},{value:"div",label:"div"},{value:"p",label:"p"},{value:"h1",label:"H1"},{value:"h2",label:"H2"},{value:"h3",label:"H3"},{value:"h4",label:"H4"}]}),(0,e.createElement)(a.SelectControl,{label:(0,t.__)("Schema Markup?"),value:g,onChange:e=>n({schema:e}),options:[{value:!1,label:"None"},{value:"faq",label:"FAQ"}]})),(0,e.createElement)(o.PanelColorSettings,{title:(0,t.__)("Color Settings"),colorSettings:[{value:d||void 0,onChange:e=>n({title_text_color:e}),label:(0,t.__)("Title Text Color")},{value:s||void 0,onChange:e=>n({title_background_color:e}),label:(0,t.__)("Title Background Color")}]})))},save:function({attributes:t}){return(0,e.createElement)(o.InnerBlocks.Content,null)}})})();