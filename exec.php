<?php 

include("config.php");
$lifx = new LIFX();

$action = $_GET['a']; 

switch ($action) {
	case 'on':
		$lifx->power("on");
		break;
	case 'off':
		$lifx->power("off");
		break;
	case 'scenes':
		$lifx->getScenes();
		break;
	case 'setscene':
		$lifx->setScenes($_GET['id']);
		break;
	case 'lights':
		$lifx->listLights();
		break;
	default:
		$lifx->debug();
		break;
}

?>
