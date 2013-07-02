#!/usr/bin/perl

#############################################################################################################
#
#GNUPanel es un programa para el control de hospedaje WEB 
#Copyright (C) 2006  Ricardo Marcelo Alvarez rmalvarezkai@gmail.com
#
#------------------------------------------------------------------------------------------------------------
#
#Este archivo es parte de GNUPanel.
#
#	GNUPanel es Software Libre; Usted puede redistribuirlo y/o modificarlo
#	bajo los t�rminos de la GNU Licencia P�blica General (GPL) tal y como ha sido
#	p�blicada por la Free Software Foundation; o bien la versi�n 2 de la Licencia,
#	o (a su opci�n) cualquier versi�n posterior.
#
#	GNUPanel se distribuye con la esperanza de que sea �til, pero SIN NINGUNA
#	GARANT�A; tampoco las impl�citas garant�as de MERCANTILIDAD o ADECUACI�N A UN
#	PROP�SITO PARTICULAR. Consulte la GNU General Public License (GPL) para m�s
#	detalles.
#
#	Usted debe recibir una copia de la GNU General Public License (GPL)
#	junto con GNUPanel; si no, escriba a la Free Software Foundation Inc.
#	51 Franklin Street, 5� Piso, Boston, MA 02110-1301, USA.
#
#------------------------------------------------------------------------------------------------------------
#
#This file is part of GNUPanel.
#
#	GNUPanel is free software; you can redistribute it and/or modify
#	it under the terms of the GNU General Public License as published by
#	the Free Software Foundation; either version 2 of the License, or
#	(at your option) any later version.
#
#	GNUPanel is distributed in the hope that it will be useful,
#	but WITHOUT ANY WARRANTY; without even the implied warranty of
#	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#	GNU General Public License for more details.
#
#	You should have received a copy of the GNU General Public License
#	along with GNUPanel; if not, write to the Free Software
#	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
#------------------------------------------------------------------------------------------------------------
#
#############################################################################################################

use File::Tail;
use Pg;

    sub senal_de_fin
	{
	    my $signal = shift;
	    $SIG{$signal} = \&senal_de_fin;
	    close MENSAGES;
	    exit(0);
	}

    sub senal_de_kill
	{
	    my $signal = shift;
	    $SIG{$signal} = \&senal_de_kill;
	    close MENSAGES;
	    exit(0);
	}

sub analiza_pop3
    {
    my $renglon = $_[0];
    my $conexion = $_[1];
    my $logueo = $_[2];
    open(MENSPOP3,">> $logueo");
    my $pop3 = index($renglon,"retr");
    my @datos = NULL;
    my @dominios = NULL;
    my @transfer = NULL;
    my $mensaje;
    if($pop3>=0)
	{
	@datos = split(',',$renglon);
	@dominios = split('@',$datos[1]);
	my $dominio = $dominios[1];
	@transfer = split('=',$datos[4]);
	my $transferencia = $transfer[1];
	my $result = NULL;
        $result = $conexion->exec("BEGIN");
	my $sql = "SELECT dominio FROM gnupanel_transferencias WHERE dominio='$dominio' ";
        my $result = $conexion->exec($sql);
	$result = $conexion->getResult;
	if($result)
	    {
	    $sql = "UPDATE gnupanel_transferencias SET pop3 = pop3 + $transferencia WHERE dominio = '$dominio' ";
    	    $result = $conexion->exec($sql);
	    $estado = $result->resultStatus;

	    $sql = "UPDATE gnupanel_transferencias SET total = http + ftp + smtp + pop3 WHERE dominio = '$dominio' ";
    	    $result = $conexion->exec($sql);
	    my $estado = $estado && $result->resultStatus;

	    if($estado == PGRES_COMMAND_OK)
		{
        	$result = $conexion->exec("END");
		}
	    else
		{
		$mensaje = $conexion->errorMessage;
		print MENSPOP3 $mensaje;
        	$result = $conexion->exec("ROLLBACK");
		}
	    }
	else
	    {
	    $mensaje = "El dominio $dominio no es local \n";
	    print MENSPOP3 $mensaje;
    	    $result = $conexion->exec("END");
	    }
	}
    close MENSPOP3;
    }
    
sub analiza_smtp
    {
    my $renglon = $_[0];
    my $conexion = $_[1];
    my $logueo = $_[2];
    open(MENSSMTP,">> $logueo");

    my $smtp = index($renglon,"from");
    if($smtp>=0)
	{
	$smtp = index($renglon,"size");
	}
    
    my @datos = NULL;
    my @dominios = NULL;
    my @transfer = NULL;
    my $mensaje;
    if($smtp>=0)
	{
	@datos = split(',',$renglon);
	@dominios = split('@',$datos[0]);
	my $dominio = $dominios[1];
        chop($dominio);
	@transfer = split('=',$datos[1]);
	my $transferencia = $transfer[1];
	$transferencia = 0 + $transferencia;
	my $result = NULL;
        $result = $conexion->exec("BEGIN");
        $result = $conexion->exec("SELECT dominio FROM gnupanel_transferencias WHERE dominio='$dominio'");
	$result = $conexion->getResult;
	if($result)
	    {
	    my $sql = "UPDATE gnupanel_transferencias SET smtp=smtp+$transferencia WHERE dominio = '$dominio'";
    	    $result = $conexion->exec($sql);
	    my $estado = $result->resultStatus;

	    $sql = "UPDATE gnupanel_transferencias SET total = http + ftp + smtp + pop3 WHERE dominio = '$dominio' ";
    	    $result = $conexion->exec($sql);
	    $estado = $estado && $result->resultStatus;

	    if($estado == PGRES_COMMAND_OK)
		{
        	$result = $conexion->exec("END");
		}
	    else
		{
		$mensaje = $conexion->errorMessage;
		print MENSSMTP $mensaje;
        	$result = $conexion->exec("ROLLBACK");
		}
	    }
	else
	    {
	    $mensaje = "El dominio $dominio no es local \n";
	    print MENSSMTP $mensaje;
    	    $result = $conexion->exec("END");
	    }
	}
    close MENSSMTP;	
    }

#######################################################################################################################
## Put ourselves in background if not in debug mode. 
require "/etc/gnupanel/gnupanel.conf.pl";

$nombre = $0;
$nombre = substr($nombre,rindex($nombre,"/")+1,length($nombre)-1);

$logueo = "/var/log/".$nombre.".log";
$pidfile = "/var/run/".$nombre.".pid";
$conexion = NULL;

open(STDERR, ">> $logueo");

$SIG{'TERM'} = \&senal_de_fin;
$SIG{'KILL'} = \&senal_de_kill;

    if (open(TTY, "/dev/tty"))
	{
	    ioctl(TTY, $TIOCNOTTY, 0);
	    close(TTY);
	}

    $definida = setpgrp(0, 0);

    $proceso = getppid;

    $child_pid = fork;

    if ( $child_pid != 0)
	{
	    print "Inicializando el medidor de trafico de Correo \n";
	    exit(0);
	}
    else
	{

#Inicio del programa
	    open(STDOUT, ">> $logueo");
	    open(PIDO,"> $pidfile");
	    print PIDO "$$ \n";
	    close PIDO;
	    
#	    my ($login,$pass,$uid,$gid) = getpwnam($usuario);
#	    $( = $gid;
#	    $) = $gid;
#	    $< = $uid;
#	    $> = $uid;
#
#	    if (  ((split(/ /,$)))[0] ne $gid) || ((split(/ /,$())[0] ne $gid)  ) 
#		{
#		open(MENSAGES,">> $logueo");
#		print MENSAGES "No se pudo cambiar el GID \n";
#		close MENSAGES;
#		}
#	 
#	    if (  ($> ne $uid) || ($< ne $uid)  )
#		{
#		open(MENSAGES,">> $logueo");
#		print MENSAGES "No se pudo cambiar el UID \n";
#		close MENSAGES;
#		}
#
#	    undef($login);
#	    undef($pass);
#	    undef($uid);
#	    undef($gid);
	    
	    $file = File::Tail->new(name=>$archivo_log_mail,interval=>1,maxinterval=>1,adjustafter=>1,ignore_noexistant=>1);
	    $conexion = NULL;
	    $conexion = Pg::connectdb("dbname=$database host=localhost user=$userdb password=$pasaportedb");
	    
	    while(1)
		{
		$estado = $conexion->status;

		if($estado != PGRES_CONNECTION_OK)
		    {
		    $conexion->reset;
		    }
		
		if($estado==PGRES_CONNECTION_OK)
		    {
		    while(defined($linea=$file->read))
			{
			    chop($linea);
			    $pop3 = index($linea,"pop3");
			    $smtp = index($linea,"postfix/qmgr");

			    if($smtp>=0)
				{
		    		analiza_smtp($linea,$conexion,$logueo);
				}

			    if($pop3>=0)
				{
				analiza_pop3($linea,$conexion,$logueo);
				}
			}
		    }
		else
		    {
		    $mensaje = $conexion->errorMessage;
		    open(MENSAGES,">> $logueo");
		    print MENSAGES $mensaje;
		    close MENSAGES;
		    $conexion->reset;
		    }
		}

#fin del programa
	}
    
$end = 0;


###############################################################################################################################################################
