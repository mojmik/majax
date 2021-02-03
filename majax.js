
const postTemplate = (id,title,content,url,meta) => `
<div class='majaxout' id='majaxout${id}'>
 title ${title} ${content} meta <b>${meta}</b>
</div>

`;

const postTemplateNone = (id,noresult) => `
<div class='majaxout' id='majaxout${id}'>
 ${noresult}
</div>

`;


const majaxLoader = `
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

`;

function getAjaxParams(varThisObj) { 
 var thisId=0;
 var thisHtml="";
 var last_response_len = false;
  
 var outObj={
				type: 'POST',
				data: {
					  action: 'filter_projects',
					  category: varThisObj.data('slug'),
					  type: jQuery('input[name="type"]').val()
				},
				beforeSend: function() {
				 jQuery('#majaxmain').empty();				 
				 jQuery('#majaxmain').append(majaxLoader);	
				 jQuery('.majax-loader').css('display','flex');	
				},
				xhrFields: {
					onprogress: function(e)	{
						var this_response, response = e.currentTarget.response;
						var jsonObj;
						if(last_response_len === false)	{
							this_response = response;
							last_response_len = response.length;
						}
						else {
							this_response = response.substring(last_response_len);
							last_response_len = response.length;
						}
						thisId++;
						if (this_response!="") {
							jsonObj=JSON.parse(this_response);
							if (jsonObj.title=="majaxnone") thisHtml=postTemplateNone(thisId,jsonObj.content);
							else thisHtml=postTemplate(thisId,jsonObj.title,jsonObj.content,jsonObj.url,jsonObj.meta);
							jQuery('#majaxmain').append(thisHtml);
							jQuery("#majaxout"+thisId).fadeIn("slow");														
							jQuery('.majax-loader').addClass('majax-loader-disappear-anim');
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
		 outObj.data[obj.name]=formatSliderVal(obj.value);
	 }
	 else {		
		//ordinary input
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

function runAjax(firingElement) {
 var ajaxPar=getAjaxParams(jQuery(firingElement));	 
 jQuery.ajax(majax.ajax_url, ajaxPar)
			.done(function(dataOut)
			{
				//console.log('Complete response = ' + dataOut);
			})
			.fail(function(dataOut)
			{
				//console.log('Error: ', dataOut);
			});
			//console.log('Request Sent');
}


function formatState (state) {
  if (!state.id) {
    return state.text;
  }

  var baseUrl = "/select2-icons";
  var $state = jQuery(
    '<span><img class="img-flag" /> <span></span></span>'
  );

  // Use .text() instead of HTML string concatenation to avoid script injection issues
  $state.find("span").text(state.text);
  $state.find("img").attr("src", baseUrl + "/" + state.element.value.toLowerCase() + ".png");

  return $state;
};

jQuery(document).ready(function() {
	//fire event handlers	
	jQuery('.majax-select').on('change', function() {	
			runAjax(this);
	});
	jQuery('.majax-fireinputs').on('change', function() {	
			runAjax(this);
	});
	
	//select2
    jQuery(".majax-select").select2({
		templateResult: formatState,
		templateSelection: formatState
	});
	
	//sliders
	initSliders(); 
});

function formatSliderVal(val1,val2=0,dir=1) {
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
}

function initSliders() {
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
        jQuery('#'+inputId).val(formatSliderVal(ui.values[ 0 ],ui.values[ 1 ],0));
      }
    });
	sliderRange.on('slidestop',function(e) {
	  runAjax(this);
	});
    jQuery(this).val(formatSliderVal(jQuery(sliderRange).slider( "values", 0 ),jQuery(sliderRange).slider( "values", 1 ),0));
 });
}