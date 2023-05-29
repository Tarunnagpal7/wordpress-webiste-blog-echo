(()=>{"use strict";function t(t,a,r,n,o,i,l,d,y,u,v,c){a=parseFloat(a),n=yasrTrueFalseStringConvertion(n);const g=document.getElementById(o),m=JSON.parse(yasrWindowVar.isUserLoggedIn);s(v,!1);yasrSetRaterValue(t,o,g,1,n,a,(function(t,a){s(v,!0);let n={action:"yasr_send_visitor_rating",rating:t,post_id:r,is_singular:d};!0===m&&Object.assign(n,{nonce_visitor:l}),this.setRating(t),this.disable(),jQuery.post(yasrWindowVar.ajaxurl,n).done((function(t){if(!1===(t=yasrValidJson(t)))return s(v,!1),void e(c,"<span>Not a valid Json Element, rating can't be saved.</span>");let r,n=`yasr-vote-${t.status}`;"success"===t.status&&(e(y,t.number_of_votes),e(u,t.average_rating)),r=`<span class="yasr-small-block-bold" id="${n}"> ${t.text} </span>`,e(c,r),s(v,!1),a()})).fail((function(t,e,s,a){console.error("YASR ajax call failed. Can't save data"),console.log(t)}))}))}function e(t,e){null!==t&&(t.innerHTML=e,t.style.display="")}function s(t,s=!0){let a="";!0===s&&(a=yasrWindowVar.loaderHtml),e(t,a)}function a(t){const e=t.medium_rating;delete t.medium_rating;let s=0;for(let e=1;e<=5;e++)(1===e||t[e].n_of_votes>s)&&(s=t[e].n_of_votes);let a=Math.log(s)*Math.LOG10E+1|0,r="5%";a<=3&&(r="5%"),a>3&&a<=5&&(r="10%"),a>5&&(r="15%");let n='<div class="yasr-visitors-stats-tooltip">';n+='<span id="yasr-medium-rating-tooltip">'+e+" "+JSON.parse(yasrWindowVar.textVvStats)+"</span>",n+='<div class="yasr-progress-bars-container">';let o=JSON.parse(yasrWindowVar.starsPluralForm),i=0,l=0;for(let e=5;e>0;e--)1===e&&(o=JSON.parse(yasrWindowVar.starSingleForm)),void 0!==t[e]&&(i=t[e].progressbar,l=t[e].n_of_votes),n+=`<div class='yasr-progress-bar-row-container yasr-w3-container'>\n                               <div class='yasr-progress-bar-name'>${e} ${o}</div> \n                               <div class='yasr-single-progress-bar-container'> \n                                   <div class='yasr-w3-border'> \n                                       <div class='yasr-w3-amber' style='height:17px;width:${i}'></div> \n                                   </div>\n                               </div> \n                               <div class='yasr-progress-bar-votes-count' style="flex-basis:${r} ">${l}</div>\n                           </div>`;return n+="</div></div>",n}!function(r){if(r.length>0&&(function(a){for(let r=0;r<a.length;r++)!function(r){if(!1!==a.item(r).classList.contains("yasr-star-rating"))return;const n=a.item(r),o=n.getAttribute("data-rater-postid"),i=n.id,l=i.replace("yasr-visitor-votes-rater-",""),d=document.getElementById("yasr_visitor_votes_"+l),y=parseInt(n.getAttribute("data-rater-starsize")),u=n.getAttribute("data-rater-nonce"),v=n.getAttribute("data-issingular"),c="yasr-vv-votes-number-container-"+l,g="yasr-vv-average-container-"+l,m="yasr-vv-bottom-container-"+l,_="yasr-vv-loader-"+l,f=document.getElementById(c),p=document.getElementById(g),b=document.getElementById(m),w=document.getElementById(_);let S=n.getAttribute("data-rating"),E=n.getAttribute("data-readonly-attribute"),V=n.getAttribute("data-rater-readonly");if(null===E&&(E=!1),E=yasrTrueFalseStringConvertion(E),V=yasrTrueFalseStringConvertion(V),!0===E&&(V=!0),"yes"===yasrWindowVar.ajaxEnabled){s(w);let a={action:"yasr_load_vv",post_id:o};jQuery.get(yasrWindowVar.ajaxurl,a).done((function(a){let r,n=yasrValidJson(a);if(!1===n){let t="Not a valid Json Element";return s(w,!1),void e(d,t)}if(r=!0===E||n.yasr_visitor_votes.stars_attributes.read_only,S=n.yasr_visitor_votes.number_of_votes>0?n.yasr_visitor_votes.sum_votes/n.yasr_visitor_votes.number_of_votes:0,S=S.toFixed(1),S=parseFloat(S),t(y,S,o,r,i,l,u,v,f,p,w,b),!0!==E&&(e(f,n.yasr_visitor_votes.number_of_votes),e(p,S),!1!==n.yasr_visitor_votes.stars_attributes.span_bottom)){let t=n.yasr_visitor_votes.stars_attributes.span_bottom;e(b,t)}})).fail((function(e,s,a,r){console.info("YASR ajax call failed. Showing ratings from html"),t(y,S,o,V,i,l,u,v,f,p,w,b),!0!==E&&(b.style.display="")}))}else t(y,S,o,V,i,l,u,v,f,p,w,b)}(r)}(r),"yes"===yasrWindowVar.visitorStatsEnabled)){let t=document.getElementsByClassName("yasr-dashicons-visitor-stats");t&&function(t){let e,s,r=!1;for(let n=0;n<t.length;n++)!function(n){let o="#"+t.item(n).id,i=t.item(n).getAttribute("data-postid");if(0===n&&(e=document.getElementsByClassName("yasr-vv-text-container"),null!==e&&(s=window.getComputedStyle(e[0],null).getPropertyValue("color"))),s){document.getElementById(t.item(n).id).style.fill=s}let l={action:"yasr_stats_visitors_votes",post_id:i};"function"==typeof tippy&&tippy(o,{allowHTML:!0,content:'<span style="color: #0a0a0a">Loading...</span>',theme:"yasr",arrow:!0,arrowType:"round",onShow:function(t){o!==r&&jQuery.post(yasrWindowVar.ajaxurl,l,(function(e){if(!1!==(e=yasrValidJson(e)))return"error"===e.status?(console.error(e.text),void t.setContent(e.text)):void t.setContent(a(e));t.setContent("Error!")})).fail((function(e,s,a,r){let n="YASR ajax call failed.";console.log(e),t.setContent(n)}))},onHidden:function(){r=o}})}(n)}(t)}}(document.getElementsByClassName("yasr-rater-stars-vv"))})();