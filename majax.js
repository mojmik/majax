const mCounts = {
	metaCounts: [],
	clearAll: function() {		
		for (let key in this.metaCounts) {
			this.metaCounts[key]["rowsCount"]=0;
		}
	},
	addMetaCount: function(mkey,mname,mcnt) {
	  let key=this.getMetaRowKey(mkey,mname);
	  if (!key) {
		  this.metaCounts.push({
			  metaKey: mkey,
			  metaName: mname,
			  rowsCount: mcnt
		  })
	  } else {
		  this.metaCounts[key]["metaKey"]=mkey;
		  this.metaCounts[key]["metaName"]=mname;
		  this.metaCounts[key]["rowsCount"]=mcnt;
	  }	
	},
	getMetaRowKey: function(mkey,mname) {
	  for (let key in this.metaCounts) {
		  if (this.metaCounts[key]["metaKey"]==mkey && this.metaCounts[key]["metaName"]==mname) return key;
	  }
	  return false;
	},
	getMetaCnt: function(mkey,mname) {
		let key=this.getMetaRowKey(mkey,mname);
		if (!key) {
			return "0";
		}
		return this.metaCounts[key]["rowsCount"]=this.metaCounts[key]["rowsCount"];
	  }
	  
}
const metaMisc = {
	icons: [],
	valMin: [],
	valMax: [],
	fieldformat: [],
	aktMin:[],
	aktMax:[],
	addMetaMisc: function(misc) {
		for (let key in misc) {
			this.icons[key]=misc[key]["icon"];
			this.valMin[key]=misc[key]["min"];
			this.valMax[key]=misc[key]["max"];
			this.fieldformat[key]=misc[key]["fieldformat"];
		}
	},
	getIcon: function(key) {
		return this.icons[key];
	},
	getMin: function(key) {
		return this.valMin[key];
	},
	getMax: function(key) {
		return this.valMax[key];
	},
	getFieldFormat: function(key) {
		return this.fieldformat[key];
	},
	setAktMin: function(key,val) {
		this.aktMin[key]=val;
	},
	setAktMax: function(key,val) {
		this.aktMax[key]=val;
	}	
}

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
		let metaOut="";
		for (const property in meta) {
			let metaIcon=metaMisc.getIcon(property);
			if (typeof metaIcon!== 'undefined') metaIcon=`<img src='${metaIcon}' />`;
			else metaIcon=`<span>${property}</span>`;
			metaOut=metaOut + `<div class='col meta'>${metaIcon} ${meta[property]}</div>`;
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
				<div class='col meta'>
					meta
				</div>				
				 ${metaOut}
				
			</div>
			<div class='row'>
				<div class='col-sm-6 price'>
					cena bez dph
				</div>
				<div class='col-sm-6 price'>
					cena vcetne dph
				</div>
			</div>
			<div class='row'>
				<div class='col action'>
					akce
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
			mCounts.clearAll();		
			return;
		}				
		//update inputs counts
		if (meta["meta_key"]=="endall" && meta["meta_value"]=="endall") { 
			//last  record- processing
			for (let key in mCounts.metaCounts) {
				let mMeta=mCounts.metaCounts[key];
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
		mCounts.addMetaCount(mStrings.mNormalize(meta["meta_key"]),mStrings.mNormalize(meta["meta_value"]),meta["count"]);
	},
	sendClearFunction: () => {
		jQuery('#majaxmain').empty();				 
		jQuery('#majaxmain').append(majaxRender.majaxLoader);	
		jQuery('.majax-loader').css('display','flex');	 
		//mCounts.clearCounts();	
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
			metaMisc.addMetaMisc(jsonObj.misc);
			//update sliders min-max
			majaxSlider.initSlidersMinMax(); 
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





const majaxPrc = {
	ajaxSeq:0,
	runAjax: function(firingElement) { 
		var ajaxPar=majaxPrc.getAjaxParams(jQuery(firingElement));	 
		
		jQuery.ajax(majax.ajax_url, ajaxPar)
				   .done(function(dataOut)			{
					   //console.log('Complete response = ' + dataOut);
				   })
				   .fail(function(dataOut)			{
					   //console.log('Error: ', dataOut);
				   });
				   
	},
	getAjaxParams:function (varThisObj) { 
	 var seqNumber = ++this.ajaxSeq;
	 var thisId=0;	 
	 var last_response_len = false;
	 var actionFunction='filter_rows';
	 if (varThisObj.length!=0) {		
		var objCategory=varThisObj.data('slug');
		//draw posts		
	 }
	 
	
	
	
	let fullResponse = {
	 thisId:0,
	 fullResp:"",  
	 wholeResp:"",
	 addResp: function(resp) {
		this.fullResp = this.fullResp + resp; 	
		this.wholeResp = this.wholeResp + resp; 	
		var pos=this.fullResp.indexOf("\n");
		while (pos!==-1) {
			newObj="";	 
			this.thisId++;
			newObj=this.fullResp.slice(0,pos);
			this.fullResp=this.fullResp.slice(pos+1);
			let jsonObj=JSON.parse(newObj);
			majaxRender.drawResultsFunction(this.thisId,jsonObj);
			pos=this.fullResp.indexOf("\n");
		 }
	 }
	}	 
	
	 var outObj={
					type: 'POST',
					data: {
						  action: actionFunction,
						  category: objCategory,
						  type: jQuery('input[name="type"]').val(),
						  security: majax.nonce
					},
					beforeSend: majaxRender.sendClearFunction,
					xhrFields: {
						onprogress: function(e)	{
							if (seqNumber === majaxPrc.ajaxSeq) { //check we are processing correct response
								var this_response, response = e.currentTarget.response;
								var jsonObj;
								if(last_response_len === false)	{
									//first response in stream
									this_response = response;
									last_response_len = response.length;
								}
								else {
									//another response in stream
									this_response = response.substring(last_response_len);
									last_response_len = response.length;
								}
								thisId++;
								if (this_response!="") {
									//we have a response
									fullResponse.addResp(this_response);					
									//jsonObj=JSON.parse(this_response);								
								}
							}
							else {
								//ignore this seq
							}
						}
					}
					
	 };			
	 
	 //inputs
	 var inputFields = jQuery('input[data-group="majax-fields"]');
	 inputFields.each(function (i,obj) {
		 let sliderId=jQuery(this).attr('data-mslider');	 
		 if (typeof sliderId !== 'undefined') {
			 //special slider input
			 let defaultVal = "0 - 1";
			 if (obj.value != defaultVal) {
				let inputName=jQuery(this).attr('name');
				let fieldFormat=metaMisc.getFieldFormat(inputName);
				fieldFormat = (fieldFormat == "") ? 2 : fieldFormat;
				outObj.data[obj.name]=majaxSlider.formatSliderVal(obj.value,0,fieldFormat,"fromFormat");
			 } 
		 }
		 else {		
			//ordinary input
			if (obj.type == "checkbox") {
				if (obj.checked==true) obj.value="1";
				if (obj.checked==false) obj.value="0";
			} 		
			outObj.data[obj.name]=obj.value;
		 }
	 });
	 
	 //selects
	 var selects=jQuery('select[data-group="majax-fields"]');
	 selects.each(function (i,obj) {
					//outObj.data[obj.name]=obj.value;				
					//rozbaleni dat pro vyfiltrovani
					var selectedText="";
					var selectData=jQuery(obj).select2('data');	
					var n=0;				
					jQuery.each(selectData, function (selIndex,selObj) {
						if (selObj.selected) { 
						 if (n>0) selectedText += "|";
						 selectedText += selObj.id;
						 n++;
						}
					});				
					outObj.data[obj.name]=selectedText;
	 });
	 return outObj;
	}
};



(function() {
	
	

	
	jQuery(document).ready(function() {	
		//fire event handlers	
		jQuery('.majax-select').on('change', function() {				
				majaxPrc.runAjax(this);
		});
		jQuery('.majax-fireinputs').on('change', function() {	
				majaxPrc.runAjax(this);
		});		
		//select2
		let sliders2 =jQuery(".majax-select");
		if (sliders2.length>0) {
			sliders2.select2({
				templateResult: majaxSelect.formatState,
				templateSelection: majaxSelect.formatState
			});
		}		
		//sliders
		majaxSlider.initSliders(); 
		
		//load
		majaxPrc.runAjax(false);
	}); 
})();


const mStrings = {
	mNormalize: (mStr) => {
		let newStr="" + mStr;
		return newStr.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(" ","-");		 
	}
}

const majaxSelect = {
	formatState: function(state) {
		if (!state.id) {
		  return state.text;
		}
		//var mValNormalized=state.element.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(" ","-");
		let mValNormalized=mStrings.mNormalize(state.element.value);
		//console.log(state.element.value);
		//console.log(mValNormalized);
		let metaKey=state.element.parentElement.name;		
		let mCntVal="("+mCounts.getMetaCnt(metaKey,mValNormalized)+")";
		var baseUrl = "/select2-icons";
		var $state = jQuery(
		  `<span>
			  <img class="img-flag" />
			  <span data-cap></span>
			  <span data-cnt="cnt-${mValNormalized}">${mCntVal}</span>
			</span>`
		);
	  
		// Use .text() instead of HTML string concatenation to avoid script injection issues
		$state.find("span[data-cap]").text(state.text);
		//$state.find("span").text(state.text + " - cus");

		$state.find("img").attr("src", baseUrl + "/" + mValNormalized + ".png");
	  
		return $state;
	  }
}

const majaxSlider =  {

	formatSliderVal: (val1 , val2=0, format=2, direction="toFormat") => {		
		if (direction=="toFormat") {			
			if (format===2) return ""	+ val1 + " - " + "" + val2 + "";
			else {
			 return format.replace("%1",val1) + " - " + format.replace("%1",val2);
			}
		}
		else {
			if (format!==2) {
				let formatStr=format.replace("%1","");
				val1=val1.split(formatStr).join(""); //replaceAll added on August2020
				val1=val1.split(" - ").join("|"); //or use replaceAll
				return val1;
			}
			//convert from format			
			//let's take 2 numbers
			const rex = /-?\d(?:[,\d]*\.\d+|[,\d]*)/g;
			let out="";
			while ((match = rex.exec(val1)) !== null) {
			if (out!="") out+='|'; 
			out+=match[0];
			}
			return out;
		}
	},
	initSlidersMinMax: function() {		
		var fs=this.formatSliderVal;
		jQuery('input[data-mslider]').each(function(index) {
			var inputId=this.id;
			let sliderId=jQuery(this).attr('data-mslider');
			let sliderRange=jQuery('#'+sliderId+'');			
			let inputName=jQuery(this).attr('name');
			let valMin=Number(metaMisc.getMin(inputName));
			let valMax=Number(metaMisc.getMax(inputName));
			let fieldFormat=metaMisc.getFieldFormat(inputName);
			fieldFormat = (fieldFormat == "") ? 2 : fieldFormat;
			if (!isNaN(valMin) && !isNaN(valMax)) {	
				let aktMin=metaMisc.aktMin[inputName];
				let aktMax=metaMisc.aktMax[inputName];
				if (typeof aktMin === 'undefined') aktMin=valMin;
				if (typeof aktMax === 'undefined') aktMax=valMax;				
				jQuery(sliderRange).slider({
					range: true,
					min: valMin,
					max: valMax,
					slide: function( event, ui ) {
						metaMisc.aktMin[inputName]=ui.values[0];
						metaMisc.aktMax[inputName]=ui.values[1];
						jQuery('#'+inputId).val(fs(ui.values[ 0 ],ui.values[ 1 ],fieldFormat));			
					}
				});	
				
				jQuery(sliderRange).slider("option", "values",[aktMin,aktMax]);
				jQuery(sliderRange).slider("option", "min",valMin);
				jQuery(sliderRange).slider("option", "max",valMax);
							
				jQuery(this).val(fs(aktMin,aktMax,fieldFormat));
			}			
		});
	},
	initSliders: function() {
			var fs=this.formatSliderVal;		
			//initialize numeric sliders
			jQuery('input[data-mslider]').each(function(index) {
				var inputId=this.id;
				let sliderId=jQuery(this).attr('data-mslider');
				let sliderRange=jQuery('#'+sliderId+'');
				jQuery(sliderRange).slider({
				range: true,
				min: 0,
				max: 1,
				values: [ 0, 1 ],
				slide: function( event, ui ) {
					metaMisc.aktMin[inputName]=ui.values[0];
					metaMisc.aktMax[inputName]=ui.values[1];
					jQuery('#'+inputId).val(fs(ui.values[0],ui.values[1],2));			
				}
				});
				sliderRange.on('slidestop',function(e) {			
					majaxPrc.runAjax(this);
					//loadCounters();
				});
				jQuery(this).val(fs(jQuery(sliderRange).slider( "values", 0 ),jQuery(sliderRange).slider( "values", 1 ),2));
			})
	}	
}

 
