(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[3],{roL4:function(e,t,a){"use strict";a.r(t);var n=a("6kfY"),c=a("tJVT"),s=a("9og8"),r=(a("/xke"),a("TeRw")),i=a("WmNS"),l=a.n(i),o=a("ZqBY"),d=Object(o["a"])({prefix:"../api/docs/",timeout:1e4,headers:{"Content-Type":"application/json"},errorHandler:function(e){r["default"]["error"]({message:e.message})}});d.interceptors.response.use(function(){var e=Object(s["a"])(l.a.mark((function e(t){var a;return l.a.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return e.next=2,t.clone().json();case 2:return a=e.sent,0!==a.code&&(r["default"]["error"]({message:a.message,description:a.description}),Promise.reject(a)),e.abrupt("return",t);case 5:case"end":return e.stop()}}),e)})));return function(t){return e.apply(this,arguments)}}());var u=d,j=a("q1tI"),b=a("9kvl"),p=(a("OaEy"),a("2fM7")),h=a("nKUr"),O=function(e){var t=Object(j["useState"])([]),a=Object(c["a"])(t,2),n=a[0],s=a[1],r=[],i=e=>{e.map((e=>{e.isLeaf&&r.push({label:e.key,value:e.key}),e.children&&i(e.children)}))},l=e=>{e&&b["a"].push({query:{key:e}})};return Object(j["useEffect"])((()=>{i(e.items),s(r)}),[e.items]),Object(h["jsx"])(p["a"],{showSearch:!0,placeholder:"\u641c\u7d22",options:n,allowClear:!0,onChange:l,style:{width:"100%",textAlign:"left"}})},m=(a("ozfa"),a("MJZm")),f=e=>{var t=e.title,a=e.subTitle,n=(e.multi,a?"tree-title-multi-inline":"tree-title-single");return Object(h["jsxs"])("div",{className:n,children:[Object(h["jsx"])("p",{children:t}),Object(h["jsx"])("p",{children:a})]})},x=function(e){var t=Object(j["useState"])([]),a=Object(c["a"])(t,2),n=a[0],s=a[1],r=Object(j["useState"])([]),i=Object(c["a"])(r,2),l=i[0],o=i[1],d=Object(j["useState"])(!1),u=Object(c["a"])(d,2),p=u[0],O=u[1],x=e=>{o(e),O(!0)},v=(e,t)=>{var a=e[0];t.node.isLeaf&&(s([a]),o([a]),b["a"].push({query:{key:a}}))};return Object(j["useEffect"])((()=>{var e,t=null===(e=b["a"].location.query)||void 0===e?void 0:e.key;t&&(s([t]),o([t]))}),[b["a"].location.query]),Object(h["jsx"])(h["Fragment"],{children:e.items.length>0?Object(h["jsx"])(m["a"].DirectoryTree,{defaultExpandAll:!1,defaultExpandedKeys:n,defaultSelectedKeys:n,autoExpandParent:p,onSelect:v,onExpand:x,className:"sidebar",showLine:!1,showIcon:!0,treeData:e.items,titleRender:e=>Object(h["jsx"])(f,{title:e.title,subTitle:e.subTitle,multi:null===e||void 0===e?void 0:e.children}),selectedKeys:n,expandedKeys:l}):""})},v=function(){var e=Object(j["useState"])([]),t=Object(c["a"])(e,2),a=t[0],n=t[1];return Object(j["useEffect"])((()=>{var e=b["a"].location.pathname.split("/"),t=e[e.length-1];u.post("docs/get_menu",{data:{type:t}}).then((e=>{n(e.data)}))}),[b["a"].location.pathname]),Object(h["jsxs"])("div",{className:"sidebar withMargin panel",children:[Object(h["jsx"])("div",{className:"menuSearch",children:Object(h["jsx"])(O,{items:a})}),Object(h["jsx"])("div",{className:"menuStyle",children:Object(h["jsx"])("div",{className:"leftTree",children:Object(h["jsx"])(x,{items:a})})})]})},y=a("IujW"),w=a("wH4i"),g=(a("Cjh2"),function(e){var t=Object(j["useState"])(""),a=Object(c["a"])(t,2),n=a[0],s=a[1],r=Object(j["useState"])(!1),i=Object(c["a"])(r,2),l=i[0],o=i[1];return Object(j["useEffect"])((()=>{var e,t,a=b["a"].location.pathname.split("/"),n=a[a.length-1],c=null===(e=b["a"].location.query)||void 0===e?void 0:e.key;null!==(t=b["a"].location.query)&&void 0!==t&&t.key&&!l&&(o(!0),u.post("docs/get_content",{data:{type:n,key:c}}).then((e=>{o(!1),s(e.data&&e.data.content?e.data.content:"\u6682\u65e0\u6570\u636e")})).catch((e=>{o(!1)})))}),[b["a"].location.query]),Object(h["jsx"])("div",{style:{padding:"15px 30px"},children:Object(h["jsx"])(y["a"],{plugins:[w["a"]],className:"markdown-body",children:n})})});t["default"]=function(){return Object(h["jsx"])("div",{className:"container",children:Object(h["jsxs"])("div",{className:"body",children:[Object(h["jsx"])(n["a"],{direction:"e",style:{flexGrow:"0.7"},children:Object(h["jsx"])(v,{})}),Object(h["jsx"])("div",{className:"content panel",children:Object(h["jsx"])(g,{})})]})})}}}]);