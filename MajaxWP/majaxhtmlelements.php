<?php
namespace MajaxWP;

use stdClass;

Class MajaxHtmlElements {	
    function showMainPlaceHolder() {    
		?>
		<div id="majaxmain" class="majaxmain">
		 <?php
		  //ajax content comes here
		 ?>
		</div> 
		<?php
    }
    function showFilters($postType,$allFields) {
		?>
		<form id="majaxform">
			<div class='majaxfiltercontainer'>			
					<input type='hidden' name='type' value='<?= $postType?>' />
				<?php		
				foreach ($allFields as $fields) {
				  ?> <div class='majaxfilterbox'> <?php  
							echo $fields->outFieldFilter();	
				  ?> </div> <?php
				}
				?>			
            </div>
            <div style='display:none;' id="majaxback">
                <div id='goBackButton' class='mbutton btn btn-primary'>
                    <a href='javascript: history.go(-1)'>zpátky</a>
                </div>
    		</div>
		</form>		
		<?php
    }
    function showPost($id,$title,$image,$content,$metas,$itemDetails) {         
        $metaOut=array();     
        for ($n=0;$n<5;$n++) {
            $metaOut[$n]="";
        }

        foreach ($metas as $metaName => $metaMisc) {           
            $metaIcon=$metaMisc["icons"];
            $displayOrder=$metaMisc["displayorder"];
            $metaVal=$itemDetails[$metaName];
            if ($metaIcon) $metaIcon="<img src='$metaIcon' />";
            else $metaIcon="<span>$metaName</span>";	
           
            if ($displayOrder<20) {
                $metaOut[0]=$metaOut[0] . "<div class='col meta'>$metaIcon $metaVal</div>";
            }
            if ($displayOrder>=20 && $displayOrder<=30) {
                $metaOut[1]=$metaOut[1] . "
                <div class='col-sm-6 price'>
                    Cena bez DPH / měsíc 
                    <div class='row'>
                        <div class='col'>
                            $metaVal
                        </div>
                    </div> 
                </div>";
                $metaOut[2]=$metaOut[2] . "
                <div class='col-sm-6 price'>
                    Cena včetně DPH / měsíc 
                    <div class='row'>
                        <div class='col'>".
                         ($metaVal*1.21)."
                        </div>
                    </div> 
                </div>";
            }
            if ($displayOrder>30 && $displayOrder<=40) {
                $propVal=$metaVal;
                if (!$propVal) $propVal="neuvedeno";
                $metaOut[3]=$metaOut[3] . "
                <div class='col-sm-3'>
                    <span>".$metaMisc["title"]."</span>
                    <div class='row'>
                        <span>
                         $propVal
                        </span>
                    </div> 
                </div>";
            }
            
        }
  
        if ($image!="") {
            $image="<img src='$image' />";
        }  
        ?>
        <div class='majaxout majaxoutStatic' id='majaxout<?=$id?>'>              
                        <div class='row flex-grow-1'>
                            <div class='col title'>                        
                                <?= $image?>
                            </div>
                        </div>
                        <div class='row mcontent'>			    
                            <span><?= $content?></span>
                        </div>
                        <div class='row bors'>			
                            <?=$metaOut[0]?>
                        </div>
                        <div class='row bort'>			
                            <?=$metaOut[1]?>
                            <?=$metaOut[2]?>                                
                        </div>
                        <div class='row borb'>
                            <div class='col action'>
                                <a data-slug='<?=$title?>' href='?id=<?=$title?>'>Objednat</a>
                            </div>
                        </div>
                    </div>
        <?php
    }
}