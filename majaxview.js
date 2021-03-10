var majaxModule=(function (my) {
        
    const majaxRender = {        
        postTemplate: (id,title,content,url,meta,image,single) => { 
            let metaOut=[];
            for (let n=0;n<5;n++) {
                metaOut[n]="";    
            }            
        
            for (const property in meta) {
                let metaIcon=my.metaMisc.icons[property];
                let displayOrder=my.metaMisc.displayorder[property];
                if (typeof metaIcon!== 'undefined' && metaIcon!="") metaIcon=`<img src='${metaIcon}' />`;
                else metaIcon=`<span>${property}</span>`;	
                if (displayOrder<20) {
                    metaOut[0]=metaOut[0] + `<div class='col meta'>${metaIcon} ${meta[property]}</div>`;
                }
                if (displayOrder>=20 && displayOrder<=30) {
                    metaOut[1]=metaOut[1] + `
                    <div class='col-sm-6 price'>
                        Cena bez DPH / měsíc 
                        <div class='row'>
                            <div class='col'>
                                ${meta[property]}
                            </div>
                        </div> 
                    </div>`;
                    metaOut[2]=metaOut[2] + `
                    <div class='col-sm-6 price'>
                        Cena včetně DPH / měsíc 
                        <div class='row'>
                            <div class='col'>
                             ${meta[property]*1.21}
                            </div>
                        </div> 
                    </div>`;
                }
                if (displayOrder>30 && displayOrder<=40) {
                    let propVal=meta[property];
                    if (propVal == null) propVal="neuvedeno";
                    metaOut[3]=metaOut[3] + `
                    <div class='col-sm-3'>
                        <span>${my.metaMisc.title[property]}</span>
                        <div class='row'>
                            <span>
                             ${propVal}
                            </span>
                        </div> 
                    </div>`
                }
            }	
            if (image!="") {
                image=`<img src='${image}' />`;
            }            
            if (typeof single !== 'undefined' ) {
                 //single                  
                 return(
                    `
                    <div class='majaxout row2' id='majaxout${id}'>
                        <div class='row flex-grow-1'>
                            <div class='col title'>                        
                                ${image}                        
                            </div>
                        </div>
                        <div class='row mcontent'>			    
                            <span>${content}</span>
                        </div>
                        <div class='row bors'>			
                             ${metaOut[0]}                    
                        </div>
                        <div class='row bort'>			
                                ${metaOut[1]}
                                ${metaOut[2]}	
                        </div>    
                        <div class='row borb'>
                                ${metaOut[3]}	
                        </div>
                    </div>
                    `);            
            }
            else {
                return(
                    `
                    <div class='majaxout' id='majaxout${id}'>
                        <div class='row flex-grow-1'>
                            <div class='col title'>                        
                                ${image}                        
                            </div>
                        </div>
                        <div class='row mcontent'>			    
                            <span>${content}</span>
                        </div>
                        <div class='row bors'>			
                             ${metaOut[0]}                    
                        </div>
                        <div class='row bort'>			
                                ${metaOut[1]}
                                ${metaOut[2]}	
                        </div>
                        <div class='row borb'>
                            <div class='col action'>
                                <a data-slug='${my.mStrings.mNormalize(title)}' href='?id=${my.mStrings.mNormalize(title)}'>Objednat</a>
                            </div>
                        </div>
                    </div>
                    `);
            }            
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
                            //console.log(spanCounter);
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
                        <span><a data-slug='pagination' data-page='${p}' href='${url}'>${p+1}</a></span>
                        `;
                    } else {
                        content+=`
                        <span>${p+1}</span> 
                        `; 
                    }
                } 
                else {
                    if (p==aktPage-3) content+=`..`;
                    if (p==aktPage+3) content+=`..`;
                }
            }
            return(
                `    
                <div class='mpagination'>            
                ${content}                
                </div>
                `);   
        },
        postTemplateAction: (content) => {
            if (content) {
                return(
                    `    
                    <div class='mpagination'>            
                    ${content}                      
                    </div>
                    `);   
            }            
            else {
                return(
                    `    
                    <div class='mpagination'>            
                    ${content}  
                    ${my.majaxViewComponents.majaxContactForm()}              
                    </div>
                    `);   
            }
        },
        sendClearFunction: (firingAction) => {
            jQuery('#majaxmain').empty();				 
            jQuery('#majaxmain').append(my.majaxViewComponents.majaxLoader);
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
        showBack: () => {     
             jQuery("#majaxform").hide();
             jQuery("#majaxback").show();
        },
        hideBack: () => { 
            jQuery("#majaxform").show();
            jQuery("#majaxback").hide();
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
            else if (jsonObj.title=="action") {                
                let thisHtml=majaxRender.postTemplateAction(jsonObj.content);
                jQuery('#majaxmain').append(thisHtml);
                jQuery("#pickDate").datepicker();
                jQuery("#dropDate").datepicker();
                majaxRender.showBack();
                jQuery("#majaxContactForm").on('submit', function(event) {				
                    my.majaxPrc.runAjax(this);
                    event.preventDefault();			
			        return false;
                });                
        
            }
            else if (jsonObj.title=="empty") {
                thisHtml=majaxRender.postTemplateEmpty(thisId,jsonObj.content);
                majaxRender.animateMajaxBox(thisHtml,thisId);			
            }
            else { 
                thisHtml=majaxRender.postTemplate(thisId,jsonObj.title,jsonObj.content,jsonObj.url,jsonObj.meta,jsonObj.image,jsonObj.single);
                majaxRender.animateMajaxBox(thisHtml,thisId);			
            }
         }
    }

    my.majaxRender=majaxRender;
    return my;

}(majaxModule || {} ));
