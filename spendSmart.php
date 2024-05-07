<?php
// Include database connection
require_once 'db_connect.php';
include 'util.php';
$spendSmart = new SpendSmart(); // Corrected capitalization
$pdo=$spendSmart->getPDO();
class Menu {
    protected $text;
    protected $sessionId;
    protected $pdo;

    function __construct($text, $sessionId) {
        $this->text = $text;
        $this->sessionId = $sessionId;
        $spendSmart = new SpendSmart();
        $db=$spendSmart->getPDO();
        $this->pdo = $db;
    }

    public function mainMenuUnregistered() {
        $response = "CON Welcome to Spend Smart\n";
        $response .= "1. Register\n";
        echo $response;
    }

    public function menuRegister($textArray,$phoneNumber) {
        // Do something
        $level = count($textArray);
        if($level == 1) {
            echo "CON Enter Your Name\n";
        } elseif($level == 2) {
            if ($textArray[1]){}
            echo "CON Enter Your PIN\n";
            
        } elseif($level == 3) {
            echo "CON Rewrite Your PIN\n";
            
        } elseif($level == 4) {
            $name = $textArray[1];
            $pin = $textArray[2];
            $confirm_pin = $textArray[3];
            if($pin != $confirm_pin) {                               
                echo "CON PINs do not match, Retry \n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            } else {
                // Register user
                echo "END ";
                $name = $textArray[1];
                $spendSmart = new SpendSmart();
                $pdo=$spendSmart->getPDO();
                $account = $spendSmart->checkUserExistsByPhone($phoneNumber);
                if($account){
                    echo "CON PLease the phone number is aready registered \n"; 
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response; 
                }else{
                    $pin = $pin;
                    // Proceed with adding the semester
                    $data = [
                        'phone' => $phoneNumber,
                        'name' => $name,
                        'pin' => $pin,
                        'date' => date('Y-m-d'),
                        'status' => 'Active'
                    ];
                    $insert = $spendSmart->addUser($data);
                    echo $insert['message'].$name;
                }
                
            }
        }
    }

    public function mainMenuRegistered() {
        $response = "CON Welcome back to Spend Smart USSD Application.\n";
        $response .= "1. Manage Expenses\n";
        $response .= "2. Manage Resources\n";
        $response .= "3. My Account\n";
        $response .= "4. Exit\n";
        echo $response;
    }

    public function manageExpenses($textArray,$phoneNumber) {
        // Do something 
        $level = count($textArray);
        if($level == 1) {
            echo "CON Enter Your PIN to do stock out\n";
        } elseif($level == 2) {
            echo "CON List of Resources and Enter Resource ID for Stock out \n";
            $pin=md5($textArray[1]);
            $phone=$phoneNumber;
            $spendSmart=new SpendSmart();
            $userId=$spendSmart->checkUserByPhone($phone,$pin);
            if(!$userId){
                echo "CON Invalid username or pin \n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }else{
                $re = $spendSmart->getResources($userId, $resourceAvId='');
                if (!empty($re)) {
                    $response = '';
                    foreach ($re as $value) {
                        $response .= $value['re_av_id'] . ". " . $value['re_name'] . ' ' . $value['qty'] . "kg\n";
                    }
                    echo $response;

                    // Options appended only when resources are available
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU . " Main menu\n";
                    echo $response;
                } else {
                    echo "CON there is no resources Available\n";

                    // Options appended only when no resources are available
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU . " Main menu\n";
                    echo $response;
                }
            }
        } elseif($level == 3) {
            $pin=md5($textArray[1]);
            $phone=$phoneNumber;
            $spendSmart=new SpendSmart();
            $userId=$spendSmart->checkUserByPhone($phone,$pin);
            if(!$userId){
                echo "CON Invalid username or pin \n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }else{
                $re=$spendSmart->getResources($userId,$textArray[2]);
                if($re){
                    foreach ($re as $value){
                        echo "CON Enter Quantity in ".$value['unit']."\n";
                    }
                }else{
                    echo "CON Invalid choice of resource ID.";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }
            }
           
        }elseif($level == 4) { 
            $response='';
            echo "CON Choose Decision \n";
            $response .= "1. Confirm\n";
            $response .= "2. Cancel\n";
            $response .= Util::$GO_BACK . " Back\n";
            $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
            echo $response;
        } else {
            if($textArray[4] == 1) {
                echo "END ";
                $pin=md5($textArray[1]);
                $phone=$phoneNumber;
                $spendSmart=new SpendSmart();
                $userId=$spendSmart->checkUserByPhone($phone,$pin);
                if($userId){
                    $reAvailable=$spendSmart->getResources($userId,$textArray[2]);
                   
                    if($reAvailable){
                        foreach($reAvailable as $value){
                            $re_av_id=$value['re_av_id'];
                            $re_id=$value['re_id'];
                            $qty=$value['qty'];
                        }
                        if($textArray[3]<=$qty){
                            $data=[
                                're_av_id'=>$re_av_id,
                                'quantity'=>$textArray[3],
                                'qty'=>$qty,
                                'userId'=>$userId,
                                're_id'=>$re_id
                            ];
                            $upendin=$spendSmart->spendin($data);
                            //var_dump($upendin)
                            echo $upendin['message']."\n";
                        }else{
                            echo "CON Amount you Enter is too high \n";
                            $response .= Util::$GO_BACK . " Back\n";
                            $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                            echo $response;
                        }
                    }else{
                        echo "CON No resource available \n";
                        $response .= Util::$GO_BACK . " Back\n";
                        $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                        echo $response;
                    }
                }else{
                    echo "CON Invalid username or pin \n";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }
           } elseif($textArray[4] == 2) {
                echo "END Successfull to exit\n";
                exit();
            } else {
                echo "CON Invalid Choice Please try again \n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }
        }
    }

    public function manageResources($textArray,$phoneNumber) {
        // Do something 
        $level = count($textArray);
        $response='';
        if($level == 1) {
            echo "CON Enter Your PIN to manage your resources\n";
        } elseif($level == 2) {
            echo "CON Enter Your Choice for what you want to manage\n";
            $response .= "1. New Resource\n";
            $response .= "2. Exist Resource\n";
            $response .= Util::$GO_BACK . " Back\n";
            $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
            echo $response;
        } elseif($level == 3) {
            if ($textArray[2] == 1) {
                echo "CON Enter Resource Name\n"; 
            } elseif ($textArray[2] == 2) {
                $pin=md5($textArray[1]);
                $phone=$phoneNumber;
                $spendSmart=new SpendSmart();
                $userId=$spendSmart->checkUserByPhone($phone,$pin);
                if(!$userId){
                    echo "CON Invalid username or pin \n";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }else{
                    
                    $re=$spendSmart->getResources($userId,'');
                    $response='';
                    if(count($re)>0){
                        echo "CON List of Resources and Enter resource Id\n"; 
                        foreach ($re as $value){
                            $response .=$value['re_av_id'].". ".$value['re_name']."\n";
                        }
                        $response .= Util::$GO_BACK . " Back\n";
                        $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                        echo $response;
                    }else{
                        echo "CON No resource available in stock \n";
                        $response = Util::$GO_BACK . " Back\n";
                        $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                        echo $response;
                        exit(); 
                    }
                }
                
            } else {
                echo "CON Invalid Input Please try again\n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
                exit();
            }
        } elseif($level == 4) {
            if($textArray[2] == 1) {
                echo "CON Enter Resource Quantity in kg\n";
            } elseif($textArray[2] == 2) {
                $spendSmart=new SpendSmart();
                $userId=$spendSmart->checkUserByPhone($phoneNumber,md5($textArray[1]));
                if($userId){
                    $result = $spendSmart->getResources($userId, $textArray[3]);
                    if ($result) {
                        // Output the result
                        foreach ($result as $re) {
                            echo "CON Enter Your Choice \n";
                            // Check if 're_name' index exists before accessing it
                            if (isset($re['re_name'])) {
                                $response .= "1. Add quantity to " . $re['re_name'] . "\n";
                                $response .= "2. Edit " . $re['re_name']."\n";
                            } else {
                                // Handle the case where 're_name' index is not set
                                $response .= "1. Add quantity to [Unknown Resource]\n";
                                $response .= "2. Edit Resource [Unknown Resource] \n";
                            }
                            $response .= Util::$GO_BACK . " Back\n";
                            $response .= Util::$GO_TO_MAIN_MENU . " Main menu\n";
                            echo $response;
                        }
                    }else{
                        echo "CON invalid choice of reosurce ID \n";
                        $response .= Util::$GO_BACK . " Back\n";
                        $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                        echo $response;
                    }
                }else{
                    
                    echo "CON invalid password. \n";
                    $response .= Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }
                
            } else {
                echo "CON Invalid input!! \n";
                $response .= Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }
        } elseif($level == 5) {
            if($textArray[2] == 1) {
                echo "CON Choose Decision \n";
                $response .= "1. Confirm\n";
                $response .= "2. Cancel\n";
                $response .= Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            } elseif($textArray[4] == 1 && $textArray[2] == 2) {
                $spendSmart = new SpendSmart();
                $userId = $spendSmart->checkUserByPhone($phoneNumber, md5($textArray[1]));
                if ($userId) {
                    $result = $spendSmart->getResources($userId, $textArray[3]);
                    if ($result) {
                        // Output the result
                        foreach ($result as $re) {
                            echo "CON Enter Resource Quantity " . $re['unit'] . "\n";
                        }
                    }
                } else {
                    echo "CON Invalid password \n";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }  
            } elseif($textArray[4] == 2 && $textArray[2] == 2) {
                echo "CON Enter New Name\n";  
            } else {
                echo "CON Invalid input \n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }
        } elseif($level == 6) {
            if($textArray[2] == 1) {
                if($textArray[5] == 1 && $textArray[2] == 1) {
                    $re_name=$textArray[3]; 
                    $re_qty=$textArray[4]; 
                    $pin=$textArray[1];  
                    $pin=md5($pin);
                    $phone=$phoneNumber;
                    $spendSmart = new SpendSmart();
                    $userId=$spendSmart->checkUserByPhone($phone,$pin);
                    if(!$userId){
                        echo "CON Invalid username or pin \n";
                        $response = Util::$GO_BACK . " Back\n";
                        $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                        echo $response;
                    }else{
                        $data = [
                            're_name' => $re_name,
                            're_qty' => $re_qty,
                            'pin' => $pin,
                            'userId' => $userId,
                            'date' => date('Y-m-d'),
                            're_status' => 'Active'
                        ]; 
                        //var_dump($data);
                        $insert = $spendSmart->addResource($data); 

                    }
                    
                } elseif($textArray[5] == 2) {
                    
                } else {
                    echo "CON Invalid Choice Please try again \n";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }
            } elseif($textArray[4] == 1 && $textArray[2] == 2) {
                echo "CON Choose Decision \n";
                $response .= "1. Confirm\n";
                $response .= "2. Cancel \n";
                $response .= Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            } elseif($textArray[4] == 2 && $textArray[2] == 2) {
                echo "CON Choose Decision \n";
                $response .= "1. Confirm\n";
                $response .= "2. Cancel\n";
                $response .= Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;   
            } else {
                echo "CON Invalid input \n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }
        } elseif($level == 7) {
            if ($textArray[4] == 1 && $textArray[2] == 2) {
                if($textArray[6] == 1) {
                    $re_av_id=$textArray[3];
                    $re_qty=$textArray[5]; 
                    $pin=$textArray[1];  
                    $pin=md5($pin);
                    $phone=$phoneNumber;
                    $spendSmart = new SpendSmart();
                    $userId=$spendSmart->checkUserByPhone($phone,$pin);
                    if(!$userId){
                        echo "CON Invalid username or pin\n";
                        $response = Util::$GO_BACK . " Back\n";
                        $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                        echo $response;
                    }else{
                        $result =$spendSmart->getResources($userId,$re_av_id);
                        if($result){
                            // Output the result
                            foreach($result as $re){
                                $re_name=$re['re_name'];
                                $qty=$re['qty'];
                                $re_id=$re['re_id'];
                            }

                            $data = [
                                're_name' => $re_name,
                                'qty' => $qty+$re_qty,
                                're_av_id' => $re_av_id,
                                're_id' => $re_id,
                                're_qty' => $re_qty,
                                'pin' => $pin,
                                'userId' => $userId,
                                'date' => date('Y-m-d'),
                                're_status' => 'Active'
                            ];
                            $save=$spendSmart->savein($data);
                            echo 'END '.$save['message'];
                        }else{
                            echo 'CON Please you choice invalid recouce id \n';
                            $response = Util::$GO_BACK . " Back\n";
                            $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                            echo $response;
                        }

                        
                    }
                    
                } elseif($textArray[5] == 2) {
                    echo "END Successful to Cancel\n";
                    exit();
                } else {
                    echo "CON Invalid Choice Please try again \n";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }
            } elseif($textArray[4] == 2 && $textArray[2] == 2) {
                if($textArray[6] == 1) {
                    $re_av_id=$textArray[3];
                    $pin=$textArray[1];  
                    $pin=md5($pin);
                    $phone=$phoneNumber;
                    $spendSmart = new SpendSmart();
                    $userId=$spendSmart->checkUserByPhone($phone,$pin);
                    if(!$userId){
                       echo 'CON No user have the '.$phone." is found\n";
                       $response = Util::$GO_BACK . " Back\n";
                       $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                       echo $response;
                    }else{
                        $result =$spendSmart->getResources($userId,$re_av_id);
                        //var_dump($result);
                        if($result){
                            // Output the result
                            foreach($result as $re){
                                $re_name=$re['re_name'];
                                $qty=$re['qty'];
                                $re_id=$re['re_id'];
                            }

                            $data = [
                                're_name' => $re_name,
                                're_av_id' => $re_av_id,
                                'new_name' => $textArray[5],
                                're_id' => $re_id,
                                'pin' => $pin,
                                'userId' => $userId,
                                'date' => date('Y-m-d'),
                                're_status' => 'Active'
                            ]; 
                            //var_dump($data);
                            $update=$spendSmart->updateResource($data);
                            //var_dump($save);
                            echo 'END '.$update['message'];
                        }else{
                            echo 'CON Please you choice invalid recouce id\N';
                            $response = Util::$GO_BACK . " Back\n";
                            $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                            echo $response;
                        } 
                    }
                } elseif($textArray[6] == 2) {
                    echo "END Successful to Cancel\n";
                    exit();
                } else {
                    echo "CON Invalid Choice Please try again \N";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }  
            } else {
                echo "CON Invalid input\n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }
        }
    }


    public function myAccount($textArray,$phoneNumber) {
        // Do something 
        $level = count($textArray);
        if($level == 1) {
            echo "CON Choose Enter Your Pin\n";
        } elseif($level == 2) {
            echo "CON Account Infor Choose 1 to Edit Name or choose 2 to Change Password\n";
            $pin=$textArray['1'];
            $pin=md5($pin);
            $phone=$phoneNumber;
            $spendSmart = new SpendSmart();
            $userId=$spendSmart->checkUserByPhone($phone,$pin);
            if(!$userId){
                echo "Invalid username or pin \n";
            }else{
                $dat = $spendSmart->getUserByPhone($phone);
                //var_dump($user);
              
                echo 'Name: ' . $dat['name'] . "\n";
                echo 'Phone Number: ' . $dat['phone'] . "\n";
                echo "===================\n";
                echo "Select Operation\n";
                echo "1. Edit Name\n";
                echo "2. Change Password\n";
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }
        } elseif($level == 3) {
            if($textArray[2] == 1) {
                echo "CON Enter New Name \n";
            } elseif($textArray[2] == 2) {
                echo "CON Enter New Password\n";
            } else {
                echo "CON Invalid input\n";  
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }
        } elseif($level == 4) {
            if($textArray[2] == 1) {
                echo "CON Choose \n";
                $response = "1. Save Name\n";
                $response .= "2. Cancel\n";
                $response .= Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            } elseif($textArray[2] == 2) {
                echo "CON Choose \n";
                $response = "1. Save Password\n";
                $response .= "2. Cancel\n";
                $response .= Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            } else {
                echo "CON Invalid input\n";  
                $response = Util::$GO_BACK . " Back\n";
                $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                echo $response;
            }
        } else {
            if($textArray[2] == 1 && $textArray[4] == 1) {
                $newName=$textArray[3];
                $pin=$textArray['1'];
                $pin=md5($pin);
                $phone=$phoneNumber;
                $spendSmart = new SpendSmart();
                $userId=$spendSmart->checkUserByPhone($phone,$pin);
                if(!$userId){
                    echo "CON Invalid username or pin \n";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }else{
                    $dat=$spendSmart->getUserByPhone($phone);
                    $data = [
                        'userId' => $dat['userId'],
                        'newName'=>$newName,
                        'name' => $dat['name'],
                        'phone' => $dat['phone'],
                        'pin' => $dat['pin'],
                        'newPin'=>$dat['pin'],
                        'date' => $dat['date'],
                        'status' => $dat['status']
                    ];
                
                    $userUpdate=$spendSmart->updateUser($data);
                    echo 'END '.$userUpdate['message'];
                }
            }else if($textArray[2] == 1 && $textArray[4] == 2) {
                echo "END Successful to Cancel\n";
                exit();
            }else if($textArray[2] == 2 && $textArray[4] == 1) {
                $newPassword=$textArray[3];
                $newPassword=md5($newPassword);
                $pin=$textArray['1'];
                $pin=md5($pin);
                $phone=$phoneNumber;
                $spendSmart = new SpendSmart();
                $userId=$spendSmart->checkUserByPhone($phone,$pin);
                if(!$userId){
                    echo "CON Invalid username or pin \n";
                    $response = Util::$GO_BACK . " Back\n";
                    $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
                    echo $response;
                }else{
                    $dat=$spendSmart->getUserByPhone($phone);
                    $data = [
                        'userId' => $dat['userId'],
                        'newName'=>$dat['name'],
                        'name' => $dat['name'],
                        'phone' => $dat['phone'],
                        'pin' => $dat['pin'],
                        'newPin'=>$newPassword,
                        'date' => $dat['date'],
                        'status' => $dat['status']
                    ];
                
                    $userUpdate=$spendSmart->updateUser($data);
                    echo 'END '.$userUpdate['message'];
                }
            }else if($textArray[2] == 2 && $textArray[4] == 2) {
                echo "END Successful to Cancel\n";
                exit();
            }
        }
    }

    public function exit($textArray) {
        // Do something 
        $level = count($textArray);
        if($level == 1) {
            echo "END successfully to be Exited\n";
            exit();
        } else {
            echo "CON Invalid input\n"; 
            $response = Util::$GO_BACK . " Back\n";
            $response .= Util::$GO_TO_MAIN_MENU .  " Main menu\n";
            echo $response;
        }
    }


    public function middleware($text){
        //remove entries for going back and going to the main menu
        return $this->goBack($this->goToMainMenu($text));
    }

    public function goBack($text){
        //1*4*5*1*98*2*1234
        $explodedText = explode("*",$text);
        while(array_search(Util::$GO_BACK, $explodedText) != false){
            $firstIndex = array_search(Util::$GO_BACK, $explodedText);
            array_splice($explodedText, $firstIndex-1, 2);
        }
        return join("*", $explodedText);
    }

    public function goToMainMenu($text){
        //1*4*5*1*99*2*1234*99
        $explodedText = explode("*",$text);
        while(array_search(Util::$GO_TO_MAIN_MENU, $explodedText) != false){
            $firstIndex = array_search(Util::$GO_TO_MAIN_MENU, $explodedText);
            $explodedText = array_slice($explodedText, $firstIndex + 1);
        }
        return join("*",$explodedText);
    }
}
?>
