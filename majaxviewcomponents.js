var majaxModule=(function (my) {
    const majaxViewComponents = {
        majaxContactForm: () =>  { 
            //jQuery(document).on("click", "label.leisLabel", function() {               		            
            jQuery(document).on("click", "label.leisLabel", function(e) {               		                            
                e.stopImmediatePropagation();
                let forCheckBox=jQuery(this).attr("for");
            
                jQuery("#"+forCheckBox).prop('checked', !jQuery("#"+forCheckBox).prop('checked'));
                jQuery("#"+forCheckBox+"Box").toggleClass('checked');
                alert (my.mUrl.prevUrl);
                return false;
		    });
            return `
            <div class="row frameGray">
                <div class="col-md-11 col-xs-12 mcent">
                    <div class="row">
                        <div class="yellowBand" id="enquiryP">
                            Pokud potřebujete více informací nebo se zajímáte o pronájem vozu na delší dobu, vyplňte prosím níže uvedený formulář a my vás budeme kontaktovat.
                        </div>
                    </div>
                    <div class="col-md-12 col-xs-12">
                            <div class="row">
                                <div class="col-xs-12">
                                    <p class="formhead">
                                        Můžete nám také zavolat na +420 225 345 000 nebo zavoláme my vám ve vámi zvoleném čase.
                                    </p>								
                                </div>
                            </div>
                        <form id="majaxContactForm" method="post">
                            <div class="row formGroup">
                                <div class="col-sm-6">                                    
                                    <input type="text" class="form-control" id="fname" name="fname" placeholder="Jméno*">
                                </div>
                                <div class="col-sm-6">                                    
                                    <input type="text" class="form-control" id="lname" name="lname" placeholder="Příjmení*">
                                </div>
                            </div>
                            <div class="row formGroup">                                
                                <div class="col-sm-6">                                                                        
                                    <input type="text" class="form-control email" id="email" name="email" placeholder="Email*">
                                </div>                                
                                <div class="col-sm-6">                                    
                                    <input type="text" class="form-control email" id="remail" name="cemail" placeholder="Email*">
                                </div>
                            </div>
                            <div class="row formGroup">
                                <div class="col-sm-3">
                                    <input type="text" class="form-control cal pointerEvent" id="pickDate" placeholder="Začátek pronájmu*" name="start_date" readonly="readonly">
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control cal pointerEvent" id="dropDate" placeholder="Konec pronájmu*" name="end_date" readonly="readonly">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control tel" id="phone_no" name="phone_no" placeholder="Telefon*">
                                </div>
                            </div>
                            <div class="row formGroup">
                                <div class="col-sm-6">
                                    <input type="text" class="form-control mileage" id="mileage" placeholder="Předpoklad najetých kilometrů*" name="expected_mileage">
                                </div>
                                <div class="col-sm-6 p-spc-0">
                                            <label for="business" id="leasing-for-leisure" class="leisLabel">
                                                   <input name="business" id="leisure" type="checkbox" class="leisCheck">
                                                   <em id="leisureBox" class="sprite"></em>
                                                   Jste již naším firemním zákazníkem*
                                            </label>
                                </div>
                            </div>
                            <div class="row2">	
                                    <div class="col-sm-3 pullRight col-xs-12">
                                        <input type="submit" class="btn btn-primary btn-block" name="submit" id="submit" value="Potvrdit">
                                            <input type="Button" class="btn btn-primary btn block" value="Processing.." id="divprocessing" style="display: none;">
                                    </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            `;
            }
        ,
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
        mForms: {
            formId:"",
            postedFields:[],
            formatFields: {
                "letters":/^[a-zA-Z]*$/,
                "email": /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                "phone": /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im,
                "number":/^-?\d*\.?\d*$/,
                "date":/^\d{2}[./-]\d{2}[./-]\d{4}$/
            },
            formatErrors: {
                "letters":"Zadaný text musí obsahovat pouze písmena",
                "email": "Zadaný text musí platný email",
                "phone": "Zadaný text musí být platné telefonní číslo",
                "number":"Zadaný text musí být platné číslo",
                "date":"Zadaný text musí být platné datum",
                "required":"Toto je povinné pole",
                "sameLikeName":"Kontrolní pole se neshoduje"
            },
            addInput: function (idName,inputType,isRequired=true,sameLikeName=false) {
                this.postedFields.push({"name": idName,"type": inputType,"required": isRequired, "sameLikeName" : sameLikeName});                                  
            },
            check: function(postedFieldKey,val) {
                let postedField=this.postedFields[postedFieldKey];
                if (postedField["required"]===true && val == "") return this.formatErrors["required"];
                if (!this.formatFields[postedField["type"]].test(val)) return this.formatErrors[postedField["type"]];
                if (postedField["sameLikeName"]!==false) {
                    let otherVal=this.getPostedFieldValByName(postedField["sameLikeName"]);
                    if (otherVal !== val) return this.formatErrors["sameLikeName"];
                }
                return true;
            },
            getPostedFieldValByName: function(name) {
                for (let field in this.postedFields) {
                  let otherFieldName=this.postedFields[field]["name"];
                  if (otherFieldName == name) {
                      return jQuery(this.formId).find(`input[name="${otherFieldName}"]`).val();
                  }
                }
            },
            setForm(formId) {
                this.formId=formId;
                this.postedFields=[];
            }
        },
        validateForm: (checkedForm) => {
            majaxViewComponents.mForms.setForm(checkedForm);
            majaxViewComponents.mForms.addInput("fname","letters");
            majaxViewComponents.mForms.addInput("lname","letters");
            majaxViewComponents.mForms.addInput("email","email");
            majaxViewComponents.mForms.addInput("cemail","email","true","email");            
            majaxViewComponents.mForms.addInput("start_date","date");
            majaxViewComponents.mForms.addInput("end_date","date");
            majaxViewComponents.mForms.addInput("phone_no","phone");
            majaxViewComponents.mForms.addInput("expected_mileage","number");      
            
            let merrors=[];            
            for (let key in majaxViewComponents.mForms.postedFields) {
                let name=majaxViewComponents.mForms.postedFields[key]["name"];
                let checkedElement=jQuery(checkedForm).find('input[name="'+name+'"]');
                let val = jQuery(checkedElement).val();
                let checkType=majaxViewComponents.mForms.postedFields[key]["type"];
                let checkRegex=majaxViewComponents.mForms.formatFields[checkType];
                let mErr=majaxViewComponents.mForms.check(key,val);
                let prev=jQuery(checkedElement).prev();
                if (mErr !== true) {                    
                    if (jQuery(prev).data('formerr')=="1") jQuery(prev).text(mErr);
                    else jQuery('<span class="formerr" data-formerr="1">'+mErr+'</span>').insertBefore(checkedElement);
                } else {
                   if (jQuery(prev).data('formerr')=="1") jQuery(prev).text("");                   
                }
                merrors.push([key,mErr]);                
            }
            /*
            for (let n=0;n<merrors.length;n++) {
                let errorVal=merrors[n][0];
                let errorMsg=merrors[n][1];
                console.log(errorMsg);
            }
            */
        }
    }
     
    

    my.majaxViewComponents=majaxViewComponents;
    return my;

 }(majaxModule || {} ));