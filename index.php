<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Router for putting CSS styles inline
 *
 * @author     Dylan Wood <dwood@mrn.org>
 */

define('VENDOR_DIR', realpath(dirname(__FILE__)) . '/vendor');

require VENDOR_DIR . '/Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//specify PHP routes
$app->any('/', function($test = false) use ($app) {
    require_once 'classes/TempFile.php';
    $inputFile = TempFile::createFromUpload('html', false);
    $responseFilename = TempFile::getUploadedFilename(false);
    $inputFileName = $inputFile->get('filename');

    $cmd = 'node js/cssInliner.js ' . escapeshellarg($inputFileName);
    $result = exec($cmd, $outputs, $returnVar);

    if($returnVar === 0 && file_exists($result)) {
	$app = $this->get('routerApp');
        $app->response->headers->set('Content-Description', 'File Transfer');
        $app->response->headers->set('Content-Type', 'application/octet-stream');
        $app->response->headers->set('Content-Disposition', 'attachment; filename=' . $responseFilename);
        $app->response->headers->set('Expires', '0');
        $app->response->headers->set('Cache-Control', 'must-revalidate');
        $app->response->headers->set('Pragma', 'public');
	echo file_get_contents($result);
    } else {
        throw new Exception (print_r($outputs, true));
    }
    return;
});

$app->any('/php/lint(/:test)', function($test = false) use ($app) {
    require_once 'classes/PHPLinterWrapper.php';
    PHPLinterWrapper::run($app, $test);
});

//specify JS routes
$app->any('/js/format(/:test)', function($test = false) use ($app) {
    require_once 'classes/JSFormatterWrapper.php';
    JSFormatterWrapper::run($app, $test);
});

$app->any('/js/lint(/:test)', function($test = false) use ($app) {
    require_once 'classes/JSLinterWrapper.php';
    JSLinterWrapper::run($app, $test);
});

$app->run();
