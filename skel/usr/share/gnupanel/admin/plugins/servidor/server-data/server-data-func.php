<?php
/***********************************************************************************************************

GNUPanel es un programa para el control de hospedaje WEB 
Copyright (C) 2006  Ricardo Marcelo Alvarez rmalvarezkai@gmail.com

------------------------------------------------------------------------------------------------------------

Este archivo es parte de GNUPanel.

	GNUPanel es Software Libre; Usted puede redistribuirlo y/o modificarlo
	bajo los t�rminos de la GNU Licencia P�blica General (GPL) tal y como ha sido
	p�blicada por la Free Software Foundation; o bien la versi�n 2 de la Licencia,
	o (a su opci�n) cualquier versi�n posterior.

	GNUPanel se distribuye con la esperanza de que sea �til, pero SIN NINGUNA
	GARANT�A; tampoco las impl�citas garant�as de MERCANTILIDAD o ADECUACI�N A UN
	PROP�SITO PARTICULAR. Consulte la GNU General Public License (GPL) para m�s
	detalles.

	Usted debe recibir una copia de la GNU General Public License (GPL)
	junto con GNUPanel; si no, escriba a la Free Software Foundation Inc.
	51 Franklin Street, 5� Piso, Boston, MA 02110-1301, USA.

------------------------------------------------------------------------------------------------------------

This file is part of GNUPanel.

	GNUPanel is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	GNUPanel is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with GNUPanel; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

------------------------------------------------------------------------------------------------------------

***********************************************************************************************************/
if($_SESSION['logueado']!="1") exit("Error");

function dame_data_servidor($id_servidor)
{
    global $servidor_db;
    global $puerto_db;
    global $database;
    global $usuario_db;
    global $passwd_db;
    global $_SESSION;
    global $cant_max_result;
    $id_admin = $_SESSION['id_admin'];
    $retorno = NULL;
    $conectar = "host=$servidor_db dbname=$database user=$usuario_db password=$passwd_db port=$puerto_db";
    $conexion = pg_connect($conectar) OR die("No es posible conectarse con la base de datos");
    $consulta = "SELECT * from gnupanel_server_data WHERE id_servidor = $id_servidor ";
    $res_consulta = pg_query($conexion,$consulta);

    if(!$res_consulta)
	{
	$retorno = "ERROR base de datos";
	}
    else
	{
	$retorno = pg_fetch_assoc($res_consulta,0);
	}

pg_close($conexion);
return $retorno;    
}

function lista_servidores($comienzo)
{
    global $servidor_db;
    global $puerto_db;
    global $database;
    global $usuario_db;
    global $passwd_db;
    global $_SESSION;
    global $cant_max_result;
    $retorno = NULL;
    $result = NULL;
    $conectar = "host=$servidor_db dbname=$database user=$usuario_db password=$passwd_db port=$puerto_db";
    $conexion = pg_connect($conectar) OR die("No es posible conectarse con la base de datos");
    $consulta = "SELECT * from gnupanel_servidores LIMIT $cant_max_result OFFSET $comienzo";
    $res_consulta = pg_query($conexion,$consulta);

    if(!$res_consulta)
	{
	$result = NULL;
	}
    else
	{
	$retorno = pg_fetch_all($res_consulta);
	$result = array();

	if(is_array($retorno))
	{
	foreach($retorno as $devolver)
		{
		$result[] = dame_servidor($devolver['id_servidor']);
		}
	}
	}

pg_close($conexion);
return $result;    
}

function cantidad_servidores()
    {
    global $servidor_db;
    global $puerto_db;
    global $database;
    global $usuario_db;
    global $passwd_db;
    global $_SESSION;
    $id_admin = $_SESSION['id_admin'];
    $retorno = NULL;
    $conectar = "host=$servidor_db dbname=$database user=$usuario_db password=$passwd_db port=$puerto_db";
    $conexion = pg_connect($conectar) OR die("No es posible conectarse con la base de datos");
    $consulta = "SELECT * from gnupanel_servidores ";
    $res_consulta = pg_query($conexion,$consulta);
    if(!$res_consulta)
	{
	$retorno = NULL;
	}
    else
	{
	$retorno = count(pg_fetch_all($res_consulta));
	}

pg_close($conexion);
return $retorno;    
}

function dame_tema_admin()
{
    global $servidor_db;
    global $puerto_db;
    global $database;
    global $usuario_db;
    global $passwd_db;
    global $_SERVER;
    global $_SESSION;

    $id_admin = $_SESSION['id_admin'];

    $dominio = $_SERVER['SERVER_NAME'];
    $dominio = substr_replace ($dominio,"",0,9);
    $tema = "gnupanel";
    $conectar = "host=$servidor_db dbname=$database user=$usuario_db password=$passwd_db port=$puerto_db";
    $conexion = pg_connect($conectar,PGSQL_CONNECT_FORCE_NEW) OR die("No es posible conectarse con la base de datos");

    $id_tema = "(SELECT id_tema from gnupanel_admin_sets WHERE id_admin = $id_admin )" ;
    $consulta = "SELECT tema from gnupanel_temas WHERE id_tema = $id_tema " ;
    $res_consulta = pg_query($conexion,$consulta);
    $tema = pg_fetch_result($res_consulta,0,0);
    pg_close($conexion);
    return $tema;
}

function server_data_0($procesador,$mensaje)
{
	global $idioma;
	global $escribir;
	global $plugin;
	global $plugins;
	global $seccion;
	global $_SESSION;
	global $_POST;
	global $cant_max_result;
	$comienzo = $_POST['comienzo'];
	$cantidad = cantidad_servidores();
	if(!isset($comienzo)) $comienzo = 0;
	$servidores = lista_servidores($comienzo);

	print "<div id=\"formulario\" > \n";
	print "<ins> \n";

	print "<table width=\"80%\" > \n";

	print "<tr> \n";

	print "<td width=\"60%\" > \n";
	print "<br> \n";
	print "</td> \n";

	print "<td width=\"40%\" > \n";
	print "</td> \n";

	print "</tr> \n";


	print "<tr> \n";

	print "<td width=\"60%\" > \n";
	$escriba = $escribir['id_servidor'];
	print "$escriba \n";
	print "</td> \n";

	print "<td width=\"40%\" > \n";
	print "</td> \n";

	print "</tr> \n";

	print "<tr> \n";

	print "<td width=\"60%\" > \n";
	print "<br> \n";
	print "</td> \n";

	print "<td width=\"40%\" > \n";
	print "</td> \n";

	print "</tr> \n";

	if(is_array($servidores))
	{
	foreach($servidores as $server)
	{
	
	print "<tr> \n";

	print "<td width=\"60%\" > \n";
	$escriba = $server;
	print "$escriba \n";
	print "</td> \n";

	print "<td width=\"40%\" > \n";
	$escriba = $escribir['detalle'];
	$procesador_inc = $procesador."&#063;seccion&#061;".$seccion."&#038;plugin&#061;".$plugin;

	$variables = array();
	$variables['server'] = $server;
	$variables['ingresando'] = "1";
	$variables['comienzo'] = $comienzo;
	boton_con_formulario($procesador_inc,$escriba,$variables,NULL);
	print "</td> \n";

	print "</tr> \n";
	}
	}
	print "</ins> \n";
	print "</div> \n";

	print "</table> \n";

	print "</ins> \n";
	print "</div> \n";

	print "<div id=\"botones\" > \n";
	print "<ins> \n";


	if($cant_max_result < $cantidad)
	{
	print "<table width=\"80%\" > \n";
	print "<tr> \n";
	print "<td width=\"35%\" > \n";
	if($comienzo > 0)
	{
	$escriba = $escribir['anterior'];
	$procesador_inc = $procesador."&#063;seccion&#061;".$seccion."&#038;plugin&#061;".$plugin;
	$variables = array();
	$desde = $comienzo - $cant_max_result;
	$variables['comienzo'] = $desde;
	$variables['ingresando'] = "0";
	boton_con_formulario($procesador_inc,$escriba,$variables,NULL);
	}


	print "</td> \n";

	print "<td width=\"30%\" > \n";
	$num_pag = ceil($cantidad/$cant_max_result);
	$esta_pagina = ceil($comienzo/$cant_max_result)+1;
	print $escribir['pagina']." $esta_pagina/$num_pag "."\n";
	print "</td> \n";

	print "<td width=\"35%\" > \n";
	if($cantidad > ($comienzo+$cant_max_result))
	{
	$escriba = $escribir['siguiente'];
	$procesador_inc = $procesador."&#063;seccion&#061;".$seccion."&#038;plugin&#061;".$plugin;
	$variables = array();
	$desde = $comienzo + $cant_max_result;
	$variables['comienzo'] = $desde;
	$variables['ingresando'] = "0";
	boton_con_formulario($procesador_inc,$escriba,$variables,NULL);
	}
	print "</td> \n";

	print "</tr> \n";


	print "</table> \n";
	}


	print "</ins> \n";
	print "</div> \n";
	print "<div id=\"ayuda\" > \n";
	$escriba = $escribir['help'];
	print "$escriba\n";
	print "</div> \n";
}

function server_data_1($nombre_script,$mensaje)
{
	global $idioma;
	global $escribir;
	global $plugin;
	global $plugins;
	global $seccion;
	global $_SESSION;
	global $_POST;
	$server = $_POST['server'];	
	$servidordet = dame_data_servidor(dame_id_servidor($server));
	$tema = dame_tema_admin();

	print "<div id=\"formulario\" > \n";
	print "<ins> \n";

	print "<table width=\"95%\" > \n";

	print "<tr> \n";

	print "<td width=\"100%\" colspan=\"4\" > \n";
	print "<br> \n";
	print "</td> \n";


	print "</tr> \n";

	if(is_array($servidordet))
	{
	foreach($servidordet as $llave => $arreglo)
	{
		if($llave != 'id_data')
		{

		switch($llave)
		{
		case "id_servidor":
		print "<tr> \n";
		print "<td width=\"25%\" > \n";
		$escriba = $escribir[$llave];
		print "$escriba \n";
		print "</td> \n";
		print "<td width=\"75%\" colspan=\"3\" > \n";
		$escriba = dame_servidor($arreglo);
		print "$escriba \n";
		print "</td> \n";
		print "</tr> \n";
		break;
	
		case "procesador_uso":
		print "<tr> \n";
		print "<td width=\"25%\" > \n";
		print "<br/> \n";
		print "</td> \n";
		print "<td width=\"75%\" colspan=\"3\" > \n";
		print "<br/> \n";
		print "</td> \n";
		print "</tr> \n";

		print "<tr> \n";
		print "<td width=\"25%\" > \n";
		$escriba = $escribir[$llave];
		print "$escriba <br/> \n";
		print "<IMG src=\"graficos/torta.php&#063;porc=$arreglo&tema=$tema\" border=\"0\"> <br/> \n";
		print "<br/> \n";
		print "</td> \n";
		break;

		case "memoria_usada":
		print "<td width=\"25%\" > \n";
		$escriba = $escribir[$llave];
		print "$escriba <br/> \n";
		$porc = round($arreglo*100/$servidordet['memoria_total']);
		print "<IMG src=\"graficos/torta.php&#063;porc=$porc&tema=$tema\" border=\"0\"> <br/> \n";
		print "$arreglo de ".$servidordet['memoria_total'].$escribir['mb']." \n";
		print "</td> \n";
		break;

		case "swap_usada":
		print "<td width=\"25%\" > \n";
		$escriba = $escribir[$llave];
		print "$escriba <br/> \n";
		$porc = round($arreglo*100/$servidordet['swap_total']);
		print "<IMG src=\"graficos/torta.php&#063;porc=$porc&tema=$tema\" border=\"0\"> <br/> \n";
		print "$arreglo de ".$servidordet['swap_total'].$escribir['mb']." \n";
		print "</td> \n";
		break;

		case "disco_usado":
		print "<td width=\"25%\" > \n";
		$escriba = $escribir[$llave];
		print "$escriba <br/> \n";
		$porc = round($arreglo*100/$servidordet['disco_total']);
		print "<IMG src=\"graficos/torta.php&#063;porc=$porc&tema=$tema\" border=\"0\"> <br/> \n";
		print "$arreglo de ".$servidordet['disco_total'].$escribir['mb']." \n";
		print "</td> \n";
		print "</tr> \n";
		break;


		case "memoria_total":
		break;
		case "swap_total":
		break;

		case "disco_total":
		break;

		default:
		print "<tr> \n";
		print "<td width=\"25%\" > \n";
		$escriba = $escribir[$llave];
		print "$escriba \n";
		print "</td> \n";
		print "<td width=\"75%\" colspan=\"3\" > \n";
		print "$arreglo \n";
		print "</td> \n";
		print "</tr> \n";
		}
		}
	}
	}


	print "</table> \n";
	print "</ins> \n";
	print "</div> \n";

	print "<div id=\"botones\" > \n";
	print "<ins> \n";

	$escriba = $escribir['volver'];
	$procesador_inc = $procesador."&#063;seccion&#061;".$seccion."&#038;plugin&#061;".$plugin;
	$variables = array();
	$variables['ingresando'] = "0";
	$variables['comienzo'] = $_POST['comienzo'];
	boton_con_formulario($procesador_inc,$escriba,$variables,NULL);


	print "</ins> \n";
	print "</div> \n";


	print "<div id=\"ayuda\" > \n";
	$escriba = $escribir['help'];
	print "$escriba\n";
	print "</div> \n";
}

function server_data_init($nombre_script)
{
	global $_POST;
	$paso = $_POST['ingresando'];
	//print "PASO: $paso <br/> \n";

	switch($paso)
	{
		case "1":
		server_data_1($nombre_script,NULL);
		break;
		default:
		server_data_0($nombre_script,NULL);
	}
}



?>
