<?php
namespace MajaxWP;

class MajaxForm {   
    private $postType;
    private $postedFields;
    function __construct($type="",$fields=[]) {            
        $this->postType=$type;
        $this->postedFields=$fields;
        if (empty($fields)) {
            if ($type=="mycka") {                
                $this->postedFields=["fname" => "Jméno", 
                "lname" => "Příjmení", 
                "email" => "Email", 
                "start_date" => "Začátek pronájmu", 
                "start_time" => "Čas mytí", 
                "phone_no" => "Telefon",
                "postTitle" => "Vybraný program"
                ];
            }
            else if ($type=="dotaz") {
                $this->postedFields=[                
                "fname" => "Jméno", 
                "email" => "Email",                 
                "msg" => "Zpráva"
                ];
            }
            else $this->postedFields=["fname" => "Jméno", 
                "lname" => "Příjmení", 
                "email" => "Email", 
                "start_date" => "Začátek pronájmu", 
                "end_date" => "Konec pronájmu", 
                "phone_no" => "Telefon", 
                "expected_mileage" => "Předpoklad km", 
                "business" => "Již je firemní zákazník", 
                "postTitle" => "Vybrané auto"
                ];
        }
    }
    function printForm($id,$title) {        
        ?>
        <div class="mpagination">
            <div class="row frameGray">
                <div class="col-md-11 col-xs-12 mcent">
                    <form id="<?= $id?>" method="post">
                                                <div class="row formGroup">
                                                    <div class="col-sm-6">                                    
                                                        <input type="text" class="form-control" id="fname" name="fname" placeholder="Jméno">
                                                    </div>
                                                    <div class="col-sm-6">                                                                        
                                                        <input type="text" class="form-control email" id="email" name="email" placeholder="Email*">
                                                    </div>                                
                                                </div>
                                                <div class="row formGroup">                                                                                    
                                                    <div class="col-sm-12">                                    
                                                        <textarea class="form-control" id="txtmsg" name="msg" placeholder="Vaše zpráva*"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row3">	
                                                        <div class="col-sm-3 pullRight col-xs-12">
                                                            <input type="submit" class="btn btn-primary btn-block" name="submit" id="submit" value="Potvrdit">
                                                                <input type="Button" class="btn btn-primary btn block" value="Processing.." id="divprocessing" style="display: none;">
                                                        </div>
                                                </div>
                                                <input type='hidden' name='postTitle' value='<?= $title?>' />
                                                <input type='hidden' name='postType' value='<?= $this->postType?>' />
                        </form>
                    </div>
                </div>
            </div>
        <?php             
   }
   function processForm($action="",$type="") {		
        $row=[];
        if ($action=="action") {
            $row["title"]="action";
            $row["content"]="";
            $row["postTitle"]=$this->postType;
        }
        if ($action=="contactFilled") {                       
            $outHtml="";
            $outTxt="";
            foreach ($this->postedFields as $name => $value) {
                if ($outTxt) $outTxt.="<br />";
                //$out.="$name: ".filter_var($_POST[$name], FILTER_SANITIZE_STRING);
                $formVal=$_POST[$name];
                $outTxt.="$value - $formVal";	
                $outHtml.="<tr><td><b>$value</b></td><td>".$formVal."</td></tr>";	
                if ($name=="email") $replyTo=$formVal;
            }			
            $outHtml="<table>$outHtml</table>";			
            
            //$to      = ['mkavan@hertz.cz','mfrencl@hertz.cz'];
            $to      = ['mkavan@hertz.cz'];
            $subject = 'objednavka z hertz-autopujcovna.cz';
            $body = "<h1>Objednavka z webu</h1> <h3>Typ: $type</h3> <br /><br />$outHtml";
            $altBody=strip_tags($outTxt);
            $from="objednavky@hertz-autopujcovna.cz";
            $fromName="objednavky";			
            require_once "vendor/mmail.php";
            mSendMail($subject,$body,$altBody,$to,$from,$fromName,$replyTo);			
            $this->logWrite("".$outTxt." replyto $replyTo.","filledform.txt");
            /*
            
            $headers = 'From: objednavky@hertz-autopujcovna.cz' . "\r\n" .
                'Reply-To: objednavky@hertz-autopujcovna.cz' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            mail($to, $subject, $message, $headers);
            */
            $row["title"]="action";
            $row["content"]="Díky za odeslání. Budeme vás brzy kontaktovat.";
        }
        return $row;
    }	
    
    function logWrite($val,$file="log.txt") {
        file_put_contents(plugin_dir_path( __FILE__ ) . $file,date("d-m-Y h:i:s")." ".$val."\n",FILE_APPEND | LOCK_EX);
       }
}