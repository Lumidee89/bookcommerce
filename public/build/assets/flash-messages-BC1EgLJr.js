import{K as m,r as l,j as r}from"./app-D3ncnpLh.js";import{c as a}from"./utils-Bqs7xs9s.js";import{c as i}from"./createLucideIcon-YVIZPCUT.js";import{X as u}from"./sheet-D84UqodP.js";/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const x=[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["line",{x1:"12",x2:"12",y1:"8",y2:"12",key:"1pkeuh"}],["line",{x1:"12",x2:"12.01",y1:"16",y2:"16",key:"4dfq90"}]],p=i("CircleAlert",x);/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const d=[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]],f=i("CircleCheck",d);function N({className:n}){const{flash:s}=m().props,[c,t]=l.useState({success:s.success,error:s.error});return l.useEffect(()=>{if(t({success:s.success,error:s.error}),s.success&&!s.error){const e=setTimeout(()=>t(o=>({...o,success:null})),7e3);return()=>clearTimeout(e)}},[s.success,s.error]),!c.success&&!c.error?null:r.jsx("div",{className:a("pointer-events-none fixed inset-x-0 top-20 z-50 flex flex-col items-center gap-2 px-4",n),"aria-live":"polite",children:["error","success"].map(e=>c[e]?r.jsxs("div",{role:e==="error"?"alert":"status",className:a("pointer-events-auto flex w-full max-w-xl items-start gap-3 rounded-2xl border p-4 text-sm shadow-lg",e==="success"?"border-brand-200 bg-brand-50 text-brand-900":"border-red-200 bg-red-50 text-red-900"),children:[e==="success"?r.jsx(f,{className:"mt-0.5 size-5 shrink-0"}):r.jsx(p,{className:"mt-0.5 size-5 shrink-0"}),r.jsx("p",{className:"flex-1",children:c[e]}),r.jsx("button",{type:"button",onClick:()=>t(o=>({...o,[e]:null})),"aria-label":"Dismiss",className:"opacity-60 hover:opacity-100",children:r.jsx(u,{className:"size-4"})})]},e):null)})}export{f as C,N as F};
