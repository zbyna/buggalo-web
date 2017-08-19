<?php
	require_once('config.php');

	$data = json_decode($HTTP_RAW_POST_DATA);

//	$bind = array(
//		'addon_name' => $data->addon->name,
//		'addon_version' => $data->addon->version,
//		'title' => $data->exception->value,
//		'json' => base64_encode($HTTP_RAW_POST_DATA),
//		'ip' => $_SERVER['REMOTE_ADDR']
//	);
        
        $bind = array(      // nutno změnit manipulace se sloupci - přidávání nebo ubírání
                'doplnek_verze' => $data->addon->version,
		'addon_name' => $data->xbmc->buildVersion,
		'addon_version' => $data->system->sysname.' '.$data->system->release.' '.$data->system->machine,
		'title' => trim($data->exception->type,"<>").' '.$data->exception->value,
		'json' => base64_encode($HTTP_RAW_POST_DATA),
		'ip' => $data->system->nodename
	);

	$conn->execute('INSERT INTO addon_exception(doplnek_verze,addon_name, addon_version, title, json, ip) VALUES(:doplnek_verze,:addon_name, :addon_version, :title, :json, :ip)', $bind);
