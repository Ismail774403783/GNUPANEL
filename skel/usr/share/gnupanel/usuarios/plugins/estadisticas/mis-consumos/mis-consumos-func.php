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

function dame_usuario_principal()
    {
    global $servidor_db;
    global $puerto_db;
    global $database;
    global $usuario_db;
    global $passwd_db;
    global $_SESSION;
    $id_usuario = $_SESSION['id_usuario'];
    $retorno = NULL;
    $conectar = "host=$servidor_db dbname=$database user=$usuario_db password=$passwd_db port=$puerto_db";
    $conexion = pg_connect($conectar,PGSQL_CONNECT_FORCE_NEW) OR die("No es posible conectarse con la base de datos");
    $consulta = "SELECT id_usuario from gnupanel_usuario_plan WHERE id_plan = 0 AND id_usuario = (SELECT id_usuario FROM gnupanel_usuario ORDER BY id_usuario LIMIT 1) ";
    $res_consulta = pg_query($conexion,$consulta);
    $retorno = pg_fetch_result($res_consulta,0,0);
    pg_close($conexion);
    return $retorno;
}

function dame_consumos_usuario($id_usuario)
    {
    global $servidor_db;
    global $puerto_db;
    global $database;
    global $usuario_db;
    global $passwd_db;
    global $_SESSION;

    $retorno = NULL;
    $result = NULL;
    $checkeo = NULL;
    $result = array();

    $conectar = "host=$servidor_db dbname=$database user=$usuario_db password=$passwd_db port=$puerto_db";
    $conexion = pg_connect($conectar,PGSQL_CONNECT_FORCE_NEW) OR die("No es posible conectarse con la base de datos");


    $consulta = "SELECT http,ftp,smtp,pop3,total,tope FROM gnupanel_transferencias WHERE id_dominio = $id_usuario ";
    $res_consulta = pg_query($conexion,$consulta);
    $checkeo = $res_consulta;
    $result['transferencia'] = pg_fetch_assoc($res_consulta,0);

    $consulta = "SELECT ftpweb,correo,postgres,mysql,total,tope FROM gnupanel_espacio WHERE id_dominio = $id_usuario ";
    $res_consulta = pg_query($conexion,$consulta);
    $checkeo = $checkeo && $res_consulta;
    $result['espacio'] = pg_fetch_assoc($res_consulta,0);

    if($id_usuario == dame_usuario_principal())
	{
	$id_reseller = "(SELECT cliente_de FROM gnupanel_usuario WHERE id_usuario = $id_usuario)";
	$consulta = "SELECT sum(total) AS total FROM gnupanel_espacio WHERE dueno = $id_reseller AND id_dominio<>$id_usuario ";
	$res_consulta = pg_query($conexion,$consulta);
	$checkeo = $checkeo && $res_consulta;
	$result['espacio']['tope'] = $result['espacio']['tope'] - pg_fetch_result($res_consulta,0,0);

	$consulta = "SELECT sum(total) AS total FROM gnupanel_transferencias WHERE dueno = $id_reseller AND id_dominio <> $id_usuario ";
	$res_consulta = pg_query($conexion,$consulta);
	$checkeo = $checkeo && $res_consulta;
	$result['transferencia']['tope'] = $result['transferencia']['tope'] - pg_fetch_result($res_consulta,0,0);
	}

pg_close($conexion);
return $result;
}

function dame_usuario_usuario($id_usuario)
{
    global $servidor_db;
    global $puerto_db;
    global $database;
    global $usuario_db;
    global $passwd_db;
    global $_SESSION;

    $retorno = NULL;
    $result = NULL;
    $checkeo = NULL;
    $result = array();

    $conectar = "host=$servidor_db dbname=$database user=$usuario_db password=$passwd_db port=$puerto_db";
    $conexion = pg_connect($conectar,PGSQL_CONNECT_FORCE_NEW) OR die("No es posible conectarse con la base de datos");

    $consulta = "SELECT usuario,dominio FROM gnupanel_usuario WHERE id_usuario = $id_usuario ";
    $res_consulta = pg_query($conexion,$consulta);
    $result = pg_fetch_assoc($res_consulta,0);

pg_close($conexion);
return $result;


}


function dame_tema_usuario()
{
    global $servidor_db;
    global $puerto_db;
    global $database;
    global $usuario_db;
    global $passwd_db;
    global $_SERVER;
    global $_SESSION;

    $id_usuario = $_SESSION['id_usuario'];

    $dominio = $_SERVER['SERVER_NAME'];
    $dominio = substr_replace ($dominio,"",0,9);
    $tema = "gnupanel";
    $conectar = "host=$servidor_db dbname=$database user=$usuario_db password=$passwd_db port=$puerto_db";
    $conexion = pg_connect($conectar,PGSQL_CONNECT_FORCE_NEW) OR die("No es posible conectarse con la base de datos");

    $id_tema = "(SELECT id_tema from gnupanel_usuario_sets WHERE id_usuario = $id_usuario )" ;
    $consulta = "SELECT tema from gnupanel_temas WHERE id_tema = $id_tema " ;
    $res_consulta = pg_query($conexion,$consulta);
    $tema = pg_fetch_result($res_consulta,0,0);
    pg_close($conexion);
    return $tema;
}



function mis_consumos_0($nombre_script,$mensaje)
{
	global $idioma;
	global $escribir;
	global $plugin;
	global $plugins;
	global $seccion;
	global $_SESSION;
	global $_POST;
	$id_usuario = $_SESSION['id_usuario'];	
	$usuario_data = dame_usuario_usuario($id_usuario);
	$usuario_consumos = dame_consumos_usuario($id_usuario);
	$tema = dame_tema_usuario();
	print "<div id=\"formulario\" > \n";
	print "<ins> \n";

	print "<table width=\"100%\" > \n";

		print "<tr> \n";
		print "<td> \n";
		print "<br> \n";
		print "</td> \n";
		print "<td> \n";
		print "<br> \n";
		print "</td> \n";
		print "</tr> \n";


		print "<tr> \n";

		print "<td> \n";
	print "<table> \n";

		print "<tr> \n";
		print "<td> \n";

		$escriba = $escribir['espacio_mas'];
		print "$escriba <br>\n";
		$porcentaje = round(($usuario_consumos['espacio']['total']/$usuario_consumos['espacio']['tope'])*100);
		print "<IMG src=\"graficos/torta.php&#063;porc=$porcentaje&tema=$tema\" border=\"0\"> <br/> \n";
	print "</td> \n";
	print "</tr> \n";

	print "</table> \n";

		print "</td> \n";

		print "<td> \n";
	print "<table> \n";

		print "<tr> \n";
		print "<td> \n";

		$escriba = $escribir['transferencia_mas'];
		print "$escriba <br>\n";
		$porcentaje = round(($usuario_consumos['transferencia']['total']/$usuario_consumos['transferencia']['tope'])*100);
		print "<IMG src=\"graficos/torta.php&#063;porc=$porcentaje&tema=$tema\" border=\"0\"> <br/> \n";
	print "</td> \n";
	print "</tr> \n";

	print "</table> \n";

		print "</td> \n";
		print "</tr> \n";
	print "</table> \n";

print "<br>\n";

	print "<table width=\"100%\" > \n";
		print "<tr> \n";
		print "<td> \n";
	print "<table> \n";

		print "<tr> \n";
		print "<td> \n";
		$escriba = $escribir['espacio'];
		print "$escriba\n";
		print "</td> \n";

		print "<td> \n";
		$escriba = $escribir['cantidad'];
		print "$escriba\n";
		print "</td> \n";
		print "</tr> \n";

		print "<tr> \n";
		print "<td> \n";
		print "<br>\n";
		print "</td> \n";

		print "<td> \n";
		print "<br>\n";
		print "</td> \n";
		print "</tr> \n";

		if(is_array($usuario_consumos['espacio']))
		{
		foreach($usuario_consumos['espacio'] as $llave => $arreglo)
		{
		print "<tr> \n";
		print "<td> \n";
		$escriba = $escribir[$llave];
		print "$escriba\n";
		print "</td> \n";
		print "<td> \n";
		print "$arreglo";
		print "</td> \n";
		print "</tr> \n";
		}
		}

	print "</table> \n";
	print "</td> \n";
	print "<td> \n";
	print "<table> \n";

		print "<tr> \n";
		print "<td> \n";
		$escriba = $escribir['transferencia'];
		print "$escriba\n";
		print "</td> \n";

		print "<td> \n";
		$escriba = $escribir['cantidad'];
		print "$escriba\n";
		print "</td> \n";


		print "</tr> \n";

		print "<tr> \n";
		print "<td> \n";
		print "<br>\n";
		print "</td> \n";

		print "<td> \n";
		print "<br>\n";
		print "</td> \n";
		print "</tr> \n";

		if(is_array($usuario_consumos['transferencia']))
		{
		foreach($usuario_consumos['transferencia'] as $llave => $arreglo)
		{
		print "<tr> \n";
		print "<td> \n";
		$escriba = $escribir[$llave];
		print "$escriba\n";
		print "</td> \n";
		print "<td> \n";
		$escriba = round($arreglo/1048576);
		print "$escriba";
		print "</td> \n";
		print "</tr> \n";
		}
		}

	print "</td> \n";
	print "</tr> \n";

	print "</table> \n";
	print "</table> \n";

	print "</ins> \n";

	print "</div> \n";

	print "<div id=\"botones\" > \n";
	print "<ins> \n";

	print "</ins> \n";
	print "</div> \n";

	print "<div id=\"ayuda\" > \n";
	$escriba = $escribir['help'];
	print "$escriba\n";
	print "</div> \n";
}

function mis_consumos_init($nombre_script)
{
	global $_POST;
	$paso = $_POST['ingresando'];

	switch($paso)
	{
		case "1":
		mis_consumos_0($nombre_script,NULL);
		break;
		default:
		mis_consumos_0($nombre_script,NULL);
	}
}



?>


