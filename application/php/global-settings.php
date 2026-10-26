<?php
//User Photo Upload Folder 
//Staff Photo Upload Folder 
if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/aster/application/files/staff/photo/")) {
    mkdir($_SERVER["DOCUMENT_ROOT"]."/aster/application/files/staff/photo/", 0777, true);
	}
$staff_photo_folder = $_SERVER["DOCUMENT_ROOT"]."/aster/application/files/staff/photo/";
define("STAFFPHOTO",$staff_photo_folder);


//Staff Signature Upload Folder 
if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/aster/application/files/staff/signature/")) {
    mkdir($_SERVER["DOCUMENT_ROOT"]."/aster/application/files/staff/signature/", 0777, true);
	}
$staff_signature_folder = $_SERVER["DOCUMENT_ROOT"]."/aster/application/files/staff/signature/";
define("STAFFSIGNATURE",$staff_signature_folder);

?>