<?php
//init Joomla Framework
define('_JEXEC', 1);
require_once '_auth.php';
// Note: $app and $user vars created.
// Allowed - continue:

require_once __DIR__ . '/vendor/autoload.php';
use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTiff;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelExif;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelEntryCopyright;
#Pel::setDebug(true);

$message     = '';
$success     = false;
$return_data = array();

function send_response($app, $success, $message, $return_data) {
    if ($success) {
        $message_type = 'success';
    } else {
        $message_type = 'error';
    }

    $app->enqueueMessage($message, $message_type);
    echo new JResponseJson($return_data, $message, !$success);
    $app->close();
    exit;
}

if (empty($_GET['image'])) {
    $message = 'No image supplied';
    send_response($app, $success, $message, $return_data);
}

$image_path = JPATH_ROOT . '/' . trim(base64_decode($_GET['image']), '/');

if (!file_exists($image_path)) {
    $message = 'Image could not be found: ' . $image_path;
    send_response($app, $success, $message, $return_data);
}


$data = new PelDataWindow(file_get_contents($image_path));


#echo '<pre>'; var_dump($data); echo '</pre>'; exit;

if (PelJpeg::isValid($data)) {
    $jpeg = $file = new PelJpeg();
    $jpeg->load($data);
    $exif = $jpeg->getExif();

    #echo '<pre>'; var_dump($exif); echo '</pre>'; exit;

    // If no EXIF in image, create it
    if ($exif == null) {
        $exif = new PelExif();
        $jpeg->setExif($exif);
        #echo '<pre>'; var_dump($exif); echo '</pre>'; exit;
        
        $tiff = new PelTiff();
        $exif->setTiff($tiff);
        #echo '<pre>'; var_dump($exif); echo '</pre>'; exit;
    } else {
        $tiff = $exif->getTiff();
    }
} else {
    $message = 'Unsupoorted format.';
    send_response($app, $success, $message, $return_data);
    exit;
}

// Get the first Ifd, where most common EXIF-tags reside
$ifd0 = $tiff->getIfd();
#echo '<pre>'; var_dump($ifd0); echo '</pre>'; exit;
// If no Ifd info found, create it
if($ifd0 == null) {
    $ifd0 = new PelIfd(PelIfd::IFD0);
    $tiff->setIfd($ifd0);
}
#echo '<pre>'; var_dump($exif); echo '</pre>'; exit;
#echo '<pre>'; var_dump(PelTag::COPYRIGHT); echo '</pre>'; exit;
$copyright      = $ifd0->getEntry(PelTag::COPYRIGHT);
#echo '<pre>'; var_dump($copyright); echo '</pre>'; exit;
$copyright_text = '';
if ($copyright != null) {
    $copyright_text = (string) $copyright->getValue()[0];
}

#echo '$copyright_text<pre>'; var_dump($copyright_text); echo '</pre>'; exit;
#echo '<pre>'; var_dump(empty($new_copyright)); echo '</pre>'; exit;
#echo '<pre>'; var_dump($_POST); echo '</pre>'; exit;


// Right so we've done the common stuff. Are we GETing or POSTing?
// Change to POST
if (empty($_POST['copyright'])) {
    // We're GETing:
    $message = 'Image info retrieved.';
    $success = true;
    $return_data['copyright'] = $copyright_text;
    send_response($app, $success, $message, $return_data);
    exit;
} else {
    // We're POSTing:
    $new_copyright = $_POST['copyright'];
    
    // Validate new_copyright here:
    // @TODO
    
    if (!empty($new_copyright)) {
        if ($copyright == null) {

            $copyright = new PelEntryCopyright(PelTag::COPYRIGHT, $new_copyright);
            #echo '<pre>'; var_dump($copyright); echo '</pre>'; exit;
            // This will insert the newly created entry with the copyright into the IFD.
            $ifd0->addEntry($copyright);
        } else {
            // The copyright is simply updated with the new copyright.
            $copyright->setValue($new_copyright);
        }
        //$copyright_text = (string) $copyright->getValue()[0];
        // Save the file:
        #$output = preg_replace('#\.?[\d]*\.jpg$#', '.' . time() . '.jpg', $image_path);
        $output = $image_path;
        $file->saveFile($output);
        
        $message = 'Image info updated.';
        $success = true;
        send_response($app, $success, $message, $return_data);
        exit;
        
        #echo '<pre>'; var_dump($copyright->getValue()[0]); echo '</pre>'; #exit;
        #echo '<pre>'; var_dump($output); echo '</pre>'; #exit;
    } else {
        // Something was wrong with the data (e.g. empty)
        $message = 'Image info could not be updated - empty copyright.';
        send_response($app, $success, $message, $return_data);
        exit;
    }
}
