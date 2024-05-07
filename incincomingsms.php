<?php
    include_once 'spendSmart.php'; 
    //receive data from the gateway 
    $phoneNumber = $_POST['from'];
    $text = $_POST['text']; //name pin; John 1234
    $text = explode(" ", $text);
    if(isset($text[0]) && isset($text[1])){
        $name=$text[0];
        $pin=$text[1];
        if($name ==''){
            echo "END Fill your name";
        }else if($pin ==''){
            echo "END Fill your pin";
        }else{
            $obj=new SpendSmart ();
            $isRegistered = $obj->checkUserExistsByPhone($phoneNumber);
            if($isRegistered){
                echo "END Aready registered";
               
            }else{
                $data = [
                    'phone' => $phoneNumber,
                    'name' => $name,
                    'pin' => $pin,
                    'date' => date('Y-m-d'),
                    'status' => 'Active'
                ];
                $insert = $obj->addUser($data);
                echo $insert['message'].$name;
            }     
        }
    }else{
        if((!isset($text[0]) && $text[0]=='' ) && !isset($text[1])){
            echo "END Fill your name and password";
        }else if(!isset($text[0])){
            echo "END Fill your name";
        }else if(!isset($text[1])){
            echo "END Fill your pin";
        }
        
    } 
?>