var majaxModule=(function (my) {
    
    
    const majaxRender = {
        majaxLoader: () => `
        <div class="majax-loader" data-component="loader" style="display: none;">
        <svg width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg">
        <defs>
        <linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="gradient">
        <stop stop-color="#ffc107" stop-opacity="0" offset="0%"></stop>
        <stop stop-color="#ffc107" stop-opacity=".631" offset="63.146%"></stop>
        <stop stop-color="#ffc107" offset="100%"></stop>
        </linearGradient>
        </defs>
        <g fill="none" fill-rule="evenodd">
        <g transform="translate(1 1)">
        <path d="M36 18c0-9.94-8.06-18-18-18" stroke="url(#gradient)" stroke-width="3"></path>
        <circle fill="#fff" cx="36" cy="18" r="1"></circle>
        </g>
        </g>
        </svg></div>	
        `,
        postTemplate: (id,title,content,url,meta) => { 
            let metaOut=[];
            metaOut[0]="";
            metaOut[1]="";
            metaOut[2]="";
        
            for (const property in meta) {
                let metaIcon=my.metaMisc.icons[property];
                let displayOrder=my.metaMisc.displayorder[property];
                if (typeof metaIcon!== 'undefined' && metaIcon!="") metaIcon=`<img src='${metaIcon}' />`;
                else metaIcon=`<span>${property}</span>`;	
                if (displayOrder<20) {
                    metaOut[0]=metaOut[0] + `<div class='col meta'>${metaIcon} ${meta[property]}</div>`;
                }
                else {
                    metaOut[1]=metaOut[1] + `<div class='col-sm-6 price'>${metaIcon} ${meta[property]}</div>`;
                    metaOut[2]=metaOut[2] + `<div class='col-sm-6 price'>${metaIcon} ${meta[property]*1.21}</div>`;
                }
                
            }	
    
            return(
            `
            <div class='majaxout' id='majaxout${id}'>
                <div class='row flex-grow-1'>
                    <div class='col title'>
                        title ${title}
                        ${content}
                    </div>
                </div>
                <div class='row'>			
                     ${metaOut[0]}
                    
                </div>
                <div class='row'>			
                        ${metaOut[1]}
                        ${metaOut[2]}	
                </div>
                <div class='row'>
                    <div class='col action'>
                        <a data-slug='${my.mStrings.mNormalize(title)}' href='?id=${my.mStrings.mNormalize(title)}'>akce</a>
                    </div>
                </div>
            </div>
            `);
        },
        postTemplateEmpty: (id,content) => { 
            let metaOut="";	
            return(
            `
            <div class='majaxout' id='majaxout${id}'>
            ${content}
            </div>
            `);
        },
        postTemplateCounts: (id,meta) => {		
            if (meta["meta_key"]=="clearall" && meta["meta_value"]=="clearall") {
                //first record- clearall
                my.mCounts.clearAll();		
                return;
            }				
            //update inputs counts
            if (meta["meta_key"]=="endall" && meta["meta_value"]=="endall") { 
                //last  record- processing
                for (let key in my.mCounts.metaCounts) {
                    let mMeta=my.mCounts.metaCounts[key];
                    let filterElement=jQuery('#custField'+mMeta.metaKey);
                    let labelFor=jQuery('label[for="custField'+mMeta.metaKey);	
                    let cnt="("+mMeta.rowsCount+")";	
                    if (typeof filterElement[0] !== 'undefined' && filterElement[0].type=="checkbox" && mMeta.metaName == "1") {
                        let followingElement=labelFor.next();
                        let spanCounter="<span class='counter'>"+cnt+"</span>";
                        if (followingElement.length>0 && followingElement[0].tagName=="SPAN") {
                            followingElement.text(cnt);
                        } else {
                            labelFor.after(spanCounter);
                            console.log(spanCounter);
                        }
                        
                    }
                }	
                //update selects
                jQuery(".majax-select").trigger('change.select2');	
                
                
                return;
            }
            my.mCounts.addMetaCount(my.mStrings.mNormalize(meta["meta_key"]),my.mStrings.mNormalize(meta["meta_value"]),meta["count"]);
        },
        postTemplatePagination: (pages) => {
            let content="";
            let n=0;
            let aktPage=0;
            let cntPage=0;
            for (let page in pages) { 
                if (pages[page]=="2") {
                 aktPage=n;
                }
                if (pages[page]!="pagination") n++;
            }            
            cntPage=n;
            if (cntPage==1) return "";
            let p=0;
            let url="";
            for (p=0;p<cntPage;p++) {
                if (p==0 || p==cntPage-1 || (p>aktPage-3 && p<aktPage+3) || p==aktPage) {
                    if (p!=aktPage) {
                        if (p!=0) url=my.mUrl.generateUrl("aktPage",p);
                        else url=my.mUrl.generateUrl("aktPage",null);
                        content+=`
                        <span><a data-slug='pagination' data-page='${p}' href='${url}'>page ${p+1}</a></span>
                        `;
                    } else {
                        content+=`
                        <span>aktpage ${p+1}</span> 
                        `; 
                    }
                } 
                else {
                    if (p==aktPage-3) content+=`..`;
                    if (p==aktPage+3) content+=`..`;
                }
            }

            /*
            for (let page in pages) {                
                if (page!="title") { 
                    if (pages[page]=="1") {
                        let url=my.mUrl.generateUrl("aktPage",page);
                        content+=`
                        <span><a data-slug='pagination' data-page='${page}' href='${url}'>page ${page}</a></span>
                        `;
                    } else {
                        content+=`
                        <span>aktpage ${page}</span> 
                        `;
                    }
                }
            }
            */
            return(
                `    
                <div class='mpagination'>            
                ${content}                
                </div>
                `);   
        },
        sendClearFunction: (firingAction) => {
            jQuery('#majaxmain').empty();				 
            jQuery('#majaxmain').append(majaxRender.majaxLoader);	
            if (firingAction!="single_row") jQuery('.majax-loader').css('display','flex');	 
            //my.mCounts.clearCounts();	
            //nastavit vsem checkboxum a dalsim elementum nuly- neni potreba, posilaji se i nuly
            //jQuery('.counter').text("(0)");
        },
        animateMajaxBox: (thisHtml,thisId) => {
            jQuery('#majaxmain').append(thisHtml);
            //jQuery("#majaxout"+thisId).fadeIn("slow");														
            jQuery("#majaxout"+thisId).css("display", "flex").hide().fadeIn("slow");
            jQuery('.majax-loader').addClass('majax-loader-disappear-anim');
        },
        drawResultsFunction: (thisId,jsonObj) => {	
            if (jsonObj.title=="majaxcounts") thisHtml=majaxRender.postTemplateCounts(thisId,jsonObj);
            else if (jsonObj.title=="buildInit") {
                my.metaMisc.addMetaMisc(jsonObj.misc);
                //update sliders min-max
                my.majaxSlider.initSlidersMinMax(); 
            }
            else if (jsonObj.title=="pagination") {                
                let thisHtml=majaxRender.postTemplatePagination(jsonObj);
                jQuery('#majaxmain').append(thisHtml);
            }
            else if (jsonObj.title=="empty") {
                thisHtml=majaxRender.postTemplateEmpty(thisId,jsonObj.content);
                majaxRender.animateMajaxBox(thisHtml,thisId);			
            }
            else { 
                thisHtml=majaxRender.postTemplate(thisId,jsonObj.title,jsonObj.content,jsonObj.url,jsonObj.meta);
                majaxRender.animateMajaxBox(thisHtml,thisId);			
            }
         }
    }

    my.majaxRender=majaxRender;
    return my;

}(majaxModule || {} ));
