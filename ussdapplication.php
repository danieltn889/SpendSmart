<?php
include_once 'spendSmart.php';
$sessionId = $_POST['sessionId'];
$phoneNumber = $_POST['phoneNumber'];
$serviceCode = $_POST['serviceCode'];
$text = $_POST['text'];
$obj=new SpendSmart ();
$isRegistered = $obj->checkUserExistsByPhone($phoneNumber);
$menu = new Menu($text, $sessionId);
$text = $menu->middleware($text);

if ($text == "" && !$isRegistered) {
    $menu->mainMenuUnregistered();
} elseif ($text == "" && $isRegistered) {
    $menu->mainMenuRegistered();
} elseif (!$isRegistered) {
    // Do something
    $textArray = explode("*", $text);
    switch ($textArray[0]) {
        case 1:
            $menu->menuRegister($textArray,$phoneNumber);
            break;
        default:
            echo "END Invalid option, Retry";
            break;
    }
} else {
    // Do something
    $textArray = explode("*", $text);
    switch ($textArray[0]) {
        case 1:
            $menu->manageExpenses($textArray,$phoneNumber);
            break;
        case 2:
            $menu->manageResources($textArray,$phoneNumber);
            break;
        case 3:
            $menu->myAccount($textArray,$phoneNumber);
            break;
        case 4:
            $menu->exit($textArray);
            break;
        default:
            echo "END Invalid choice\n";
            break;
    }
}
?>
