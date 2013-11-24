<?php

/* Exposure front controller */

include '../sys/core/bootstrap.php' ;
include '../sys/config/config.inc.php' ; // initialises $config

// split request URI into pre-'?' and post-'?'
preg_match('/([^\?]*)\??(.*)/', substr($_SERVER['REQUEST_URI'], 1), $request_uri);
$request = explode('/', $request_uri[1]);
$query = $request_uri[2] ;

// Clean request array of empty elements
foreach($request as $k => $v) {
    if($v=='') { unset($request[$k]); } // Clear any empty elements
}
$request = array_values($request);  // Renumber array keys
if(count($request) == 0) {
    $request[] = 'index';  // Responsible for home page
}
$action = $request[0] ;

// dispatch by request type
switch($_SERVER['REQUEST_METHOD']) {
case 'GET':
    if (isAjax()) {
        $decorator = new \Sociable\View\RawDecorator() ;
    }
    else {
        $decorator = new \Sociable\View\HTMLDecorator(
            $config->getTwig(), 
            $config->getParam('appName'), 
            $config->getParam('title'), 
            $config->getParam('cssFiles')
        ) ;
    }
    $controller = new \Exposure\Controller\GetController($decorator) ;

    $controller->dispatch($action, $request, $query, $config) ;
    break ;
case 'POST':
    $controller = new \Exposure\Controller\PostController() ;
    $controller->process($config) ;
    break ;
}

function isAjax() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
}
