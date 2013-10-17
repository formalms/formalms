<?php
$htmlPageAttachmentsDir = "../html".DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."appLms".DIRECTORY_SEPARATOR."htmlpages";
echo "CREATING DIR: ".$htmlPageAttachmentsDir."... \n";
if (mkdir($htmlPageAttachmentsDir, 0777)){ 
    echo "CREATING DIR: ".$htmlPageAttachmentsDir." [OK]\n";
}else{
    echo "CREATING DIR: ".$htmlPageAttachmentsDir." [KO]\n";
}
?>