import{c as s}from"./createLucideIcon-YVIZPCUT.js";import{j as r,$ as i}from"./app-D3ncnpLh.js";import{c as o}from"./utils-Bqs7xs9s.js";/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const l=[["circle",{cx:"11",cy:"11",r:"8",key:"4ej97u"}],["path",{d:"m21 21-4.3-4.3",key:"1qie3q"}]],x=s("Search",l);function d({meta:a,className:n}){return a.last_page<=1?null:r.jsx("nav",{"aria-label":"Pagination",className:o("mt-10 flex flex-wrap items-center justify-center gap-1",n),children:a.links.map((e,t)=>{const c=e.label.replace("&laquo;","«").replace("&raquo;","»");return e.url?r.jsx(i,{href:e.url,preserveScroll:!0,className:o("min-w-10 rounded-full border px-3 py-2 text-center text-sm",e.active?"bg-primary text-primary-foreground border-primary":"border-border hover:bg-accent"),"aria-current":e.active?"page":void 0,children:c},t):r.jsx("span",{className:"text-muted-foreground min-w-10 px-3 py-2 text-center text-sm",children:c},t)})})}export{d as P,x as S};
