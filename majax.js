var majaxModule=(function (my) {

const mStrings = {
	mReplaceAll: (mStr,from,to) => {
		return mStr.split(from).join(to); //replaceAll added on August2020
	},
	mNormalize: (mStr) => {
		let newStr="" + mStr;
		newStr=newStr.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
			return (mStrings.mReplaceAll(newStr," ","-"));		 
	}
}



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
	displayorder:[],
	addMetaMisc: function(misc) {
		for (let key in misc) {
			this.icons[key]=misc[key]["icon"];
			this.valMin[key]=misc[key]["min"];
			this.valMax[key]=misc[key]["max"];
			this.fieldformat[key]=misc[key]["fieldformat"];
			this.displayorder[key]=misc[key]["displayorder"];
		}
	}
};



(function() {
	
	

	
	jQuery(document).ready(function() {	
		my.mUrl.readUrl();
		//fire event handlers			
		jQuery('.majax-select').on('change', function() {				
				my.majaxPrc.runAjax(this);
		});
		jQuery('.majax-fireinputs').on('change', function() {	
				my.majaxPrc.runAjax(this);
		});		
		//select2
		let sliders2 =jQuery(".majax-select");
		if (sliders2.length>0) {
			sliders2.select2({
				templateResult: my.majaxSelect.formatState,
				templateSelection: my.majaxSelect.formatState
			});
		}		

		//click items anchors 
		jQuery('#majaxmain').on('click', 'a', function(event) {
			let href=jQuery(this).attr('href');
			//console.info('Anchor clicked!' + href);
			//window.history.pushState({href: href}, '', href);
			window.history.pushState({href: href}, '', href);
			my.majaxPrc.runAjax(this);
			event.preventDefault();			
			return false;
		});
		
		window.addEventListener('popstate', function(e){
			let href="";
			if(e.state) href=e.state.href;
			//reset filter boxes
			my.majaxSelect.resetAll();
			my.majaxPrc.runAjax(this);			
		 }); 
		 
		//sliders
		my.majaxSlider.initSliders(); 
		
		//load
		my.majaxPrc.runAjax(false);
	}); 
})();


my.mStrings=mStrings;
my.metaMisc=metaMisc;
my.mCounts=mCounts;
my.metaMisc=metaMisc;
return my;

}(majaxModule || {} ));