<?php
/*
archivo: scriptCorreo.php
función: Envío masivo de correos  
autor:   Federico Greco 
creado:  12/04/2022  
versión: 1.6  -  12/07/2022 11:46   (Version ajustada a uso general)
*/
?>
<?php

$limit = 7; //Variable para modificar la cantidad de mails a mandar por tanda (Cada x minutos configurado en schtasks)
$tabla = '';   //Nombre de la tabla en el pgadmin4


//(Ajustado el archivo require segun la pc en uso)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require('C:\Users\fgreco\Desktop\vendor\autoload.php'); //Biblioteca necesaria de composer para phpmailer

require('PHPMailer/PHPMailer.php'); // biblioteca php que se comunica directamente con el smtp
require('PHPMailer/SMTP.php');
require('PHPMailer/Exception.php');



////////////////////////////////////////////////////////////////
//          CONEXION A LA BASE DE DATOS POSTGRE              //
//////////////////////////////////////////////////////////////
$dbpostgre = pg_connect("host=localhost port=5432 dbname=correos_automatizado user= password=")
	or die('\nCould not connect: ' . pg_last_error());
$query="SELECT direccionElectronica,
			CASE
				WHEN sexo='H' THEN 'o'
				ELSE 'a'
			END
		AS sufijo, nombre, linkPersonal
		FROM " . $tabla . "
		WHERE fechaNotificado IS NULL
        LIMIT " . $limit . ";";

$result = pg_query($query) or die('\nQuery failed: ' . pg_last_error());


function debug_to_console($data) {
	$output = $data;
	if (is_array($output))
		$output = implode(',', $output);

	echo "\n<script>console.log('Debug Objects: " . $output . "' );</script>";
}

if ($result==NULL) {
    echo "\n\nNo hay nada que ejecutar\n\n";
} else {
    try {
        while ($row=pg_fetch_row($result)) { 
            $mail = new PHPMailer(); 
            
            
            
            
            
            ////////////////////////////////////////////////////////////////
            //          DATOS A LLENAR PARA EL ENVIO DE CORREO           //
            //////////////////////////////////////////////////////////////
            $mail->Username=''; // SE PONE EL CORREO DESDE EL CUAL SE QUIEREN ENVIAR LOS MENSAJES
            $mail->Password=''; // CONTRASEÑA DEL CORREO DESDE EL QUE SE MANDAN LOS MENSAJES

            $email = ""; // CORREO DESDE EL QUE SE MANDAN LOS MENSAJES
            $nombreMail = ""; // Nombre del emisor del correo
            $subjectCorreo = ''; // ASUNTO DEL CORREO

            $urlLink = $row[3]; // Esto carga el urlLink que especificamos en la columna linkpersonal en la tabla de la base de datos, alli se puede ingresar el mismo link para todos o uno unico para cada persona, funciona igual.
            
            
            
            
            
            ////////////////////////////////////////////////////////////////
            //          CONFIGURACION DEL SERVIDOR DE CORREO             //
            //////////////////////////////////////////////////////////////
            $mail->IsSMTP();
            $mail->SMTPOptions = array ('ssl' => array('verify_peer'  => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true));	
            $mail->Mailer="smtp"; // smtp
            $mail->Host=''; // 
            $mail->Port=587; //   587 tls  465 ssl
            $mail->SMTPSecure = 'tls'; //  'tls' - (seciu)  'ssl'  PHPMailer::ENCRYPTION_SMTPS, false
            $mail->SMTPAuth=true; //true
            $mail->CharSet = "utf-8";



            
            
            ////////////////////////////////////////////////////////////////
            //                  SETEO DE DATOS DEL CORREO                //
            //////////////////////////////////////////////////////////////
            $mail->setFrom($email, $nombreMail); // "CORREO DESDE EL QUE SE MANDAN LOS MENSAJES", "NOMBRE DEL EMISOR DE LOS CORREOS
            $mail->AddReplyTo($email);       //   correo para filtrar/agrupar respuestas, es decir si un destinatario responde el correo le llegara a la casilla al correo seleccionada aqui.   
            $mail->Subject=$subjectCorreo;  // ASUNTO DEL CORREO
            $mail->addAddress($row[0],$row[2]);          // SE ENVIA EL CORREO A TODAS LAS DIRECCIONES RECOPILADAS AQUI DESDE LA BASE DE DATOS CON FORMATO <correo, nombre>
            //$mail->AddBCC($row[0]);                    // SE ENVIA EL CORREO A TODAS LAS DIRECCIONES RECOPILADAS AQUI DESDE LA BASE DE DATOS CON FORMATO <correo> en forma copia oculta.
            //$mail->addAttachment($urlArchivo);  // $mail->addAttachment($urlArchivo, 'text/plain');
            $mail->addAttachment('C:\Users\fgreco\Desktop\Carta Rector Mayo 2022.pdf'); // Se puede utilizar tanto un archivo en pc como una url web pero no fue probado este caso.
            
            
            
            
            
            
            ////////////////////////////////////////////////////////////////
            //                  CUERPO DEL CORREO A ENVIAR               //
            //////////////////////////////////////////////////////////////
            $verso="<html><head></head><body><p>Estimad".$row[1]." ".$row[2].",<br><br>";
            $verso.="La Universidad de la República se encuentra desarrollando un Programa de Seguimiento de Egresados, que tiene como propósito conocer la trayectoria y el desempeño laboral/profesional de sus egresados, así como sus opiniones y evaluación respecto a la carrera de la cual egresaron.
            <br><br>
            En el marco de este Programa se está realizando un relevamiento estadístico, destinado a la generación de egreso del año 2018 de la Udelar, la cual Ud. integra. 
            <br><br>
            Su participación en este estudio es fundamental para nuestra institución, por lo cual, desde ya le agradecemos su colaboración. A partir de este momento Ud. podrá acceder al formulario vía web, el cual no le insumirá más de 10 minutos para completarlo.
            <br><br>
            El link para poder acceder al formulario es el siguiente: 
            <br>
            <center><a href='" . $urlLink . "'>Link</a>.</center>
            <br><br>
            Todo el procedimiento está amparado por la Ley 16.616 de Secreto Estadístico, y por la Ley 18.331 de Protección de Datos Personales. Los datos que Ud. proporcione serán confidenciales y se utilizarán exclusivamente con fines estadísticos. 
            <br><br>
            En adjunto le enviamos la carta del Rector de la Udelar Rodrigo Arim solicitando y agradeciendo desde ya su participación y colaboración.
            <br><br><br>
            Saluda muy atentamente,
            <br><br><br>
            -- <br>
            Programa de Seguimiento de Egresados <br>
            División Estadística - Dirección General de Planeamiento <br>
            Universidad de la República <br><br>
            
            Teléfono: 99999999 <br>
            Celular (whatsapp): 099999999 <br>
            Mail: seguimiento_egresados@udelar.edu.uy <br>
            Web: <a href='https://planeamiento.udelar.edu.uy/proyectos/seguimiento-de-egresados/'>https://planeamiento.udelar.edu.uy/proyectos/seguimiento-de-egresados</a> <br>
            <br><br><br><br></p></body></html>";   
            
            $mail->WordWrap=50;  
            $mail->isHTML(true); //Habilitado 
            $mail->Body=$verso;
            $mail->MsgHTML($verso);
            
            $mail->SMTPDebug = 3;

            
            
                    
            
            ////////////////////////////////////////////////////////////////
            //              ACCIONES AL ENVIAR O NO CORREO               //
            //////////////////////////////////////////////////////////////
            if ($mail->send()) {
                echo "\nMessage sent!\n\n";
                echo "\n<tr><td>".$row[0]."</td><td>".$row[2]."</td></tr>\n\n";
                $queryU="update ".$tabla." set fechaNotificado=now() where direccionElectronica='".$row[0]."'";
                $resultU = pg_query($queryU) or die('\n\nQuery failed: ' . pg_last_error());

                pg_free_result($resultU);
            } else {
                //Para tener en cuenta sobre la implementacion del else de mail->send es que este efecto se ejecuta si encuentra algun tipo de error en el envio del correo desde el punto de vista basico de la formacion del correo
                //Ejemplo: holamundo@gmail.com, no va a ejecutar este else, en todo caso llegara a la casilla de correo emisora un correo de aviso de que reboto el correo, esto se debe a que el correo recipiente no existe.
                //Para que se ejecute este else es necesario intentar enviar un correo a un receptor no valido, EJEMPLO: holamundo@@gmail.com, este correo hara que se ejecute la parte del else del codigo.
                echo "\n\nError: " . $mail->ErrorInfo;
                
                $queryR="update ".$tabla." set fechaNotificado='2000-01-01' where direccionElectronica='".$row[0]."'";
                $resultR = pg_query($queryR) or die('\n\nQuery failed: ' . pg_last_error());

                /*
                $mailRebote = new PHPMailer();
                
                $mailRebote->setFrom($email, $nombreMail);
                //$mailRebote->addReplyTo($email);   // Se puede sacar para que el correo no de la posibilidad de respuesta
                $mailRebote->Subject='Error en envio';
                $mailRebote->addAddress("");  // Se envia el mensaje de aviso al mail elegido.
                $mailRebote->Body="Hubo un error al intentar enviar un mail al correo " . $row[0] . "";

                $mailRebote->send();
                */

                pg_free_result($resultR);
            }
        }
    } catch (Exception $e) {
        exit();
    }
}

pg_close($dbpostgre);

?>