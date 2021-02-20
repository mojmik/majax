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
			metaOut=metaOut + `<div style='background:#ccc;'>${property} ${meta[property]}</div>`;
		}
		return(
		`
		<div class='majaxout' id='majaxout${id}'>
		title ${title} ${content} meta ${metaOut}
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
	drawResultsFunction: (thisId,jsonObj) => {	
		if (jsonObj.title=="majaxcounts") thisHtml=majaxRender.postTemplateCounts(thisId,jsonObj);
		else { 
			thisHtml=majaxRender.postTemplate(thisId,jsonObj.title,jsonObj.content,jsonObj.url,jsonObj.meta);
			jQuery('#majaxmain').append(thisHtml);
			jQuery("#majaxout"+thisId).fadeIn("slow");														
			jQuery('.majax-loader').addClass('majax-loader-disappear-anim');
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
	 if (varThisObj.length==0) {
		actionFunction='filter_count_results';
		//refresh counts
	 }
	 else {
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
			 outObj.data[obj.name]=majaxSlider.formatSliderVal(obj.value);
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
		jQuery(".majax-select").select2({
			templateResult: majaxSelect.formatState,
			templateSelection: majaxSelect.formatState
		});
		
		//sliders
		majaxSlider.initSliders(); 
			
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

	formatSliderVal: (val1 , val2=0, dir=1) => {
		const mask="$"	+ val1 + " - " + "$" + val2;	
		if (dir==0) return "$"	+ val1 + " - " + "$" + val2;
		
		//let's take 2 numbers
		const rex = /-?\d(?:[,\d]*\.\d+|[,\d]*)/g;
		let out="";
		while ((match = rex.exec(val1)) !== null) {
		   if (out!="") out+='|'; 
		   out+=match[0];
		}
		return out;
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
			max: 500,
			values: [ 1, 300 ],
			slide: function( event, ui ) {
			jQuery('#'+inputId).val(fs(ui.values[ 0 ],ui.values[ 1 ],0));
			}
		});
		sliderRange.on('slidestop',function(e) {
			majaxPrc.runAjax(this);
			//loadCounters();
		});
		jQuery(this).val(fs(jQuery(sliderRange).slider( "values", 0 ),jQuery(sliderRange).slider( "values", 1 ),0));
		})
	}	
}

 
