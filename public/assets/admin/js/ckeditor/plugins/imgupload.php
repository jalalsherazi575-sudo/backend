<?php
// PHP Upload Script for CKEditor:  http://coursesweb.net/

// HERE SET THE PATH TO THE FOLDER WITH IMAGES ON YOUR SERVER (RELATIVE TO THE ROOT OF YOUR WEBSITE ON SERVER)
$upload_dir = '/uploads/';

// If 1 and filename exists, RENAME file, adding "_NR" to the end of filename (name_1.ext, name_2.ext, ..)
// If 0, will OVERWRITE the existing file
define('RENAME_F', 1);

// HERE PERMISSIONS FOR IMAGE
$imgsets = array(
 'maxsize' => 2000,     // maximum file size, in KiloBytes (2 MB)
 'maxwidth' => 2500,     // maximum allowed width, in pixels
 'maxheight' => 3000,    // maximum allowed height, in pixels
 'minwidth' => 10,      // minimum allowed width, in pixels
 'minheight' => 10,     // minimum allowed height, in pixels
 'type' => array('bmp', 'gif', 'jpg', 'jpe', 'png'),  // allowed extensions
);

$re = '';

if(isset($_FILES['upload']) && strlen($_FILES['upload']['name']) > 1) {
  $upload_dir = trim($upload_dir, '/') .'/';
  define('IMG_NAME', preg_replace('/\.(.+?)$/i', '', basename($_FILES['upload']['name'])));  //get filename without extension

  // get protocol and host name to send the absolute image path to CKEditor
  $protocol = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
  $site = $protocol. $_SERVER['SERVER_NAME'] .'/';
  $sepext = explode('.', strtolower($_FILES['upload']['name']));
  $type = end($sepext);    // gets extension
  list($width, $height) = getimagesize($_FILES['upload']['tmp_name']);  // gets image width and height
  $err = '';         // to store the errors

  //set filename; if file exists, and RENAME_F is 1, set "img_name_I"
  // $p = dir-path, $fn=filename to check, $ex=extension $i=index to rename
  function setFName($p, $fn, $ex, $i){
    if(RENAME_F ==1 && file_exists($p .$fn .$ex)) return setFName($p, IMG_NAME .'_'. ($i +1), $ex, ($i +1));
    else return $fn .$ex;
  }
  
  $img_name = setFName($_SERVER['DOCUMENT_ROOT'] .'/'. $upload_dir, IMG_NAME, ".$type", 0);
  $uploadpath = $_SERVER['DOCUMENT_ROOT'] .'/'. $upload_dir . $img_name;  // full file path

  // Checks if the file has allowed type, size, width and height (for images)
  if(!in_array($type, $imgsets['type'])) $err .= 'The file: '. $_FILES['upload']['name']. ' has not the allowed extension type.';
  if($_FILES['upload']['size'] > $imgsets['maxsize']*1000) $err .= '\\n Maximum file size must be: '. $imgsets['maxsize']. ' KB.';
  if(isset($width) && isset($height)) {
    if($width > $imgsets['maxwidth'] || $height > $imgsets['maxheight']) $err .= '\\n Width x Height = '. $width .' x '. $height .' \\n The maximum Width x Height must be: '. $imgsets['maxwidth']. ' x '. $imgsets['maxheight'];
    if($width < $imgsets['minwidth'] || $height < $imgsets['minheight']) $err .= '\\n Width x Height = '. $width .' x '. $height .'\\n The minimum Width x Height must be: '. $imgsets['minwidth']. ' x '. $imgsets['minheight'];
  }

  // If no errors, upload the image, else, output the errors
  if($err == '') {
    if(move_uploaded_file($_FILES['upload']['tmp_name'], $uploadpath)) {
      $CKEditorFuncNum = $_GET['CKEditorFuncNum'];
      $url = $site. $upload_dir . $img_name;
      $msg = IMG_NAME .'.'. $type .' successfully uploaded: \\n- Size: '. number_format($_FILES['upload']['size']/1024, 3, '.', '') .' KB \\n- Image Width x Height: '. $width. ' x '. $height;
      $re = "window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')";
    }
    else $re = 'alert("Unable to upload the file")';
  }
  else $re = 'alert("'. $err .'")';
}
echo '<script>'. $re .';</script>';