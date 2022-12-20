PROGRAMAS PREVIOS A INSTALAR:

-Se necesita la carpeta de PHPMailer-6.6.0 (En \\164.73.100.4\DGPLAN-publico\todos\Fede ya esta la carpeta, o se puede buscar en internet y descargar desde github)
-Se necesita PHP 7.4.26 (Buscar en web)(Viene con wamp tambien)
-Se necesita composer(Verificar si es necesario, si no es necesario se saca el require en el codigo del programa)
-Postgresql (que incluye pgadmin4)
-Se necesita instalar wamp64 server y configurarlo con el puerto 8080 por las dudas de que haga conflicto con apache, PHPMailer, el script de correo y el virtual host en enviarcorreo. (https://sourceforge.net/projects/wampserver/files/)
Ojo por si hace falta cambiar el php.ini del max_timout (Fede).
-Chrome, firefox u opera (Navegador)


----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


///////////////////////////////////////////////////////////////////////////////////////
//////////	CONFIGURACION NECESARIA PARA USO POR PRIMERA VEZ	//////////////
/////////////////////////////////////////////////////////////////////////////////////

1) Primero debemos configurar la base de datos a utilizar por lo tanto debemos ejecutar pgadmin4 una vez instalado.
	-A la hora de crear un usuario y contraseña para la administracion de pgadmin sugiero usar usuario admin y contraseña 123456 para mantenerlo generico.
	-Le damos a 'Add New Server' y creamos un server llamado 'correos_automatizado', cambiamos a la pestaña 'Connection', en Host ponemos 'localhost' y en password '123456' (Podemos darle a Save password si no se quiere ingresar constantemente la contraseña).
	-Tenemos que crear un tipo de variable para (H)ombre y (M)ujer en 'Types', entramos al server correos_automatizado -> Schemas -> le damos click derecho a 'public' -> create -> type,
		en la pestaña 'General' simplemente especificamos el name y ponemos 'tipo_sexo', en la pestaña 'Definition' cambiamos el Type a 'Enumeration' 
		y en Enumeration Type a la derecha nos aparece un +, tenemos que simplemente crear 2 Labels que sean H y M.
	
	-Creamos una tabla dandole click derecho Create -> Table (correos_automatizado -> databases -> postgres -> schemas -> public -> table) con el nombre que sea en minuscula y sin tildes (mas adelante de configurar pgadmin y wampserver64, en el codigo vamos a tener que especificar el nombre de la tabla en las primeras lineas de codigo junto a la cantidad de mails a mandar por tanda)
		En la creacion de la tabla debemos especificar los nombres de las columnas, los tipos y, si se quiere especificar, si pueden estar los campos vacios y si son unicos (Primary Key).
		Las columnas que debemos crear, en minuscula y sin tildes, son (esta primera linea sirve como ejemplo de la sintaxis): nombredecolumna(tipo de dato)
			-direccionelectronica (character varying, lenght:50)  (Esta columna se DEBE especificar como NOT NULL y Primary key)
			-sexo (tipo_sexo)
			-nombre (character varying, lenght:150)
			-fechanotificado (date)  (esta columna NO DEBE TENER EL NOT NULL activado ya que la fechanotificado vacia representa que no se notifico a la persona con el mail)
			-linkpersonal (character varying, lenght:300)   (Esta columna es opcional, si se deben enviar correos sin link personal o un link generico, se cambia el codigo directamente, mas adelante se especifica.)
	

	-Una vez creada la base de datos y la tabla con las respectivas columnas debemos ingresar los datos desde un csv (si no esta creado, podemos hacerlo desde un excel que le damos guardar como y tenemos que buscar en 'tipo' CSV (delimitado por comas)):
		El archivo csv debe tener en la primera fila los nombres exactos de las columnas de la base de datos y en el mismo orden, las siguientes filas contienen los datos, es decir, el archivo csv debe ser, por ejemplo:
		direccionelectronica	  |	sexo	   |	nombre	   |	fechanotificado	    |        linkpersonal*
		dgplan@udelar.edu.uy	  |	 H	   | DGPla UdelaR  |    		    |   https://planeamiento.udelar.edu.uy/
	
		*Recordar que linkpersonal se llena todos con el mismo link, link unico para cada persona o vacio segun el caso.

		Es importante que el archivo se guarde como csv(Delimitado por comas), es necesario abrir el archivo con bloc de notas, darle guardar como y guardar el archivo cambiando el formato a UTF-8
		Cuando abrimos el archivo con bloc de notas nos deberia mostrar la informacion de la siguiente forma:
		direccionelectronica;sexo;nombre;fechanotificado;linkpersonal
		...;...;...;...;...

		

	-Una vez tengamos la base de datos y la informacion en un csv tenemos que volver al pgadmin darle click derecho a la tabla creada recientemente -> Scripts -> CREATE Script y borramos el contenido que nos aparece e ingresar:
		COPY nombredelatabla FROM '\\164.73.100.4\DGPLAN-publico\todos\Fede\enviar-correo\CSVs\csv_ejemplo.csv' DELIMITER ';' CSV header ENCODING 'SQL_ASCII';
			#Como estandar se pueden dejar todos los CSV que se vayan a cargar/usar en la carpeta de la red '\\164.73.100.4\DGPLAN-publico\todos\Fede\enviar-correo\CSVs\' y simplemente agregar/modificar el nombre del archivo csv en el script que creamos recien.
			#Debemos cambiar donde dice 'nombredelatabla' por el nombre de la tabla que creamos (tabla creada recientemente) y despues del FROM asegurarnos que el directorio completo del archivo csv es el que queremos.
		Ejecutamos el script con el icono con forma de reaunudar o flecha a la derecha y deberiamos tener la tabla con los datos cargados del csv.
		Se puede verificar dandole click derecho a la tabla, View Data -> View All Rows

	#Nota Fede: este script en varios casos dio problemas de permisos para ejecutar
		-NOTA: Si a la hora de hacer exportacion de datos desde un csv a la tabla da problemas de permisos, usar el SQL Shell(psql), que se puede abrir en dando click en el boton de inicio de windows y escribiendo SQL Shell(psql). 
			Esto nos va a abrir una terminal negra en la nos va a pedir algunos datos pero simplemente le damos enter sin ingresar nada en: Server[localhost], Database[postgres], Port[5433] y Username[postgres], cuando nos pida contraseña ingresamos '123456'.
			Ahora escribimos: \copy nombredelatabla FROM '\\164.73.100.4\DGPLAN-publico\todos\Fede\enviar-correo\CSVs\csv_ejemplo.csv' DELIMITER ';' CSV header ENCODING 'SQL_ASCII';
			Y le damos enter.		



2) Luego debemos configurar el wamp64 server, debemos instalarlo en C:\wamp64, una vez instalado, creamos una carpeta enviar-correo en C:\wamp64\www, nos dejaria un directorio C:\wamp64\www\enviar-correo vacio donde colocaremos
	las carpetas PHPMailer y vendor, junto con los archivos ejecucionScript.bat, scriptCorreo.php, composer.json y composer.lock que se encuentran en \\164.73.100.4\DGPLAN-publico\todos\Fede.

	-Debemos ejecutar el wampserver64 desde el boton de inicio de windows y va a aparecer al lado del reloj de windows un icono con una W encuadrada, puede aparecer en color rojo, amarillo o verde.
		Le damos click izquierdo vamos a apache y clickeamos en el httpd.conf, esto nos va a abrir el archivo y debemos buscar una linea de codigo que diga:
			Listen 0.0.0.0:8080
			Listen [::0]:8080
		Si no tenemos los 8080 los cambiamos a 8080 y guardamos el archivo.
		Volvemos a dar click izquierdo en el icono de wamp y le damos a 'Restart All Services' y esperamos, si el icono se pone en verde esta todo bien. Si no es posible que debamos intentar reiniciar la computadora.
	-Luego debemos crear un virtualhost
		Para ello vamos al navegador y vamos a la pagina: http://localhost:8080 en la esquina inferior izquierda vamos a tener 'Tools' y debemos clickear en 'Add a Virtual Host'
		En el campo 'Name of the Virtual Host' debemos colocar: http://enviarcorreo.com  (Probar sin el http:// si no funciona)
		En el campo 'Complete absolute' debemos colocar: C:\wamp64\www\enviar-correo
		Y le damos a 'Start the creation of the Virtual Host' y esperamos a que nos diga que fue creado.
		Por ultimo vamos al icono de wamp de nuevo y le damos click derecho esta vez -> Tools -> Restart DNS y esperamos a que se ponga el icono de nuevo en verde.
	-Para probar que todo esto funciono podemos ir al navegador e ingresar a la siguiente direccion: http://enviarcorreo.com:8080 y nos deberian aparecer los archivos y carpetas que estan en nuestro directorio C:\wamp64\www\enviar-correo
	
#Nota informatica: Si no funciona hay que verificiar que el puerto de uso de apache sea 8080 y que esté disponible y no en uso por otro programa, desde el icono de wamp server se puede testear la conexion al puerto y se mostrará información.



3) Luego de tener instalados los programas con sus versiones mencionados arriba, hay que configurar el php.ini si hace falta para habilitar las extensiones pdo_pgsql y openssl. 
	Nuevamente le damos click izquierdo al icono de wamp server -> PHP -> php.ini. Buscamos en el texto las lineas extension=pdo_pgsql y extension=openssl y nos aseguramos que no tengan un # al principio de la linea.
(No estoy seguro si hace falta alguna otra, pero posiblemente de un error y avise cual falta)




----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------



///////////////////////////////////////////////////////////////////////////////////////
//////////	MODIFICACION DE CODIGO Y PREPARACION DE ENVIO		//////////////
/////////////////////////////////////////////////////////////////////////////////////


1) Cada vez que se vaya a usar el scriptCorreo.php (en C:\wamp64\www\) es probable que se tenga que cambiar parte del codigo (correo emisor, cantidad correos por tanda, mensaje, archivos adjuntos, asunto, tabla con datos de destinatarios, etc) para ajustarlo a las necesidades del momento.

	-En las primeras lineas de codigo vamos a ver las variables  $limit y $tabla
		En limit simplemente especificamos cuantos correos queremos que se envien en cada ejecucion/tanda del script (mas adelante se configura cada cuanto tiempo se ejecuta el script)
		#Es importante que el limit se cambie entre 5-10 aprox. segun si se envian archivos adjuntos, links o ambos.
	-Asegurarse que en esta la linea: require('C:\wamp64\www\enviar-correo\vendor\autoload.php');

	-En donde dice entre barras (/) CONEXION A LA BASE DE DATOS POSTGRE podemos modificar, si es necesario, los datos de ingreso a la base de datos como el nombre de la base de datos (dbname), 
	el usuario de acceso de postgre (user) y la contraseña de acceso de postgre (password). Si se ingresaron los datos sugeridos anteriormente, las variables deberian ser correctas sin tener que modificar nada.

	-Mas abajo vamos a tener entre barras (/) DATOS A LLENAR PARA EL ENVIO DE CORREO, tendremos que especificar en las variables:
		$mail->Username='';
		$mail->Password='';
		$email = '';
		$nombreMail = '';
		$subjectCorreo = '';
		
		#OPCIONAL SEGUN EL CASO:
		$urlLink = $row[3]; (Lo pueden comentar poniendo dos barras (/) antes de la linea de codigo para que el programa ignore esta funcion si no se va a utilizar link unico para cada destinatario.)

		-Y un poco mas abajo tenemos otro opcional segun el caso entre barras (/) SETEO DE DATOS DEL CORREO, tenemos:
			$mail->AddAttachment('');
				#Esta variable sirve para adjuntar archivos, simplemente debemos cambiar el directorio del archivo a enviar entre comillas simples ' (Lo pueden comentar poniendo dos barras (/) si no se quiere enviar adjuntos).

	-Lo ultimo que tendremos que ver es el cuerpo del correo que se va a enviar entre barras (/) CUERPO DEL CORREO A ENVIAR
		La primera linea $verso es el inicio del cuerpo del mail donde se consulta informacion del sexo y nombre de los datos de la base de datos, no es necesario modificar nada en el primer verso.
		En el segundo $verso se tendra que cambiar a la necesidad del momento.
		Algunos comandos basicos utiles son:
		<br> (hace un enter o cambio de linea)
		<b>TEXTO</b> (Hace que TEXTO este en negrita)
		<center>TEXTO</center> (Hace que TEXTO este centrado en la pantalla)
		<a href='https://planeamiento.udelar.edu.uy/proyectos/seguimiento-de-egresados/'>LINK</a> (Despues de href ponemos un link y donde dice LINK es el nombre que aparecera visualmente, pero al darle click nos redirecciona a la pagina que se pone despues de href='...')

		#El caso mas complejo es el siguiente <center><a href='" . $urlLink . "'>Link</a>.</center>
			Como $urlLink es una variable definida dentro del codigo es necesario poner " . $urlLink . " dentro de las tipicas comillas simples ' usados en un caso comun.
			(Los archivos en \\164.73.100.4\DGPLAN-publico\todos\Fede contienen ejemplos de estos casos)





2) ejecucionScript.bat

	El script .bat, que se encuentra en C:\wamp64\www\enviar-correo, debe solo tener el siguiente contenido (lo podemos ver dandole click derecho al archivo -> abrir con bloc de notas o notepad++): 
		start "" http://enviarcorreo.com:8080/scriptCorreo.php & timeout 30 & taskkill /im chrome.exe   (Deberia funcionar si se quiere cambiar chrome.exe por firefox.exe si se prefiere usar firefox, lo mismo con opera.exe)
			-Esto ejecuta "" que es el navegador predeterminado y abre la url especificada, luego de 30 segundos mata el proceso del navegador
			-taskkill es para matar el proceso (aunque verificando con el administrador de tareas creo que no cierra el proceso generado de chrome, aunque siempre hay generados 7 procesos y ocupan muy poca memoria)
			-timeout es para esperar 5 segundos despues de matar el proceso para volver a iniciar chrome en la url especificada y ejecutar el codigo del script.
			#Verificar que la URL del archivo .bat es el mencionado arriba.
	

	-Ahora vamos al inicio de windows y buscamos 'Programador de tareas' y lo abrimos, a la derecha tendremos una opcion llamada 'Crear tarea', donde le pondremos de nombre enviarCorreo,
		-En la pestaña 'Desencadenadores' le damos a Nuevo, en esta ventana se configura los intervalos de ejecucion, inicio y fin de ejecucion, etc
			En 'Iniciar la tarea' dejamos Segun una programacion.
			Y ponemos una vez o diariamente segun se necesite seleccionando fecha y hora de inicio (Hora de inicio intenten dejar un rango apropiado para terminar de configurar la tarea programada y no quede hora de inicio anterior a la hora actual.)
			En 'Repetir cada' lo dejamos cada 2 minutos durante X día/s (segun lo que se necesite)*
			En 'Expiracion' y 'Detener la tarea si se ejecuta durante mas de:' es apropiado que se elija una fecha/hora aproximada de finalizacion del envio de correos.
			Y finalmente le damos a aceptar.
		-En la pestaña 'Acciones' le damos a nueva y debemos en el campo 'Programa o script' examinar el archivo ejecucionScript.bat, seleccionarlo y darle 'Aceptar'.
	Le damos aceptar a Crear Tarea, le damos click a la izquierda donde dice 'Biblioteca del Programador de tareas' y nos deberia aparecer una lista de tareas programadas donde una de ellas es 'enviarCorreo' donde nos muestra la hora de la ultima ejecucion y hora de la proxima ejecucion. (Se puede chequear esta informacion para verificar que se esta ejecutando el programa.)
	
	#Cada vez que se ejecute el script nos deberia abrir una pestaña en el navegador que le especificamos en el archivo ejecucionScript.bat (chrome.exe, firefox.exe) que sea http://enviarcorreo.com:8080/scriptCorreo.php en donde nos muestre un monton de texto que es referente a la ejecucion del programa.
	#Se cierra la pestaña sola despues de 30 segundos o se puede cerrar manualmente si se prefiere.



POST USO) Programador de tareas

	Luego de haber terminado con el uso del programa es recomendable que borremos la tarea programada, para ello le damos click derecho -> deshabilitar y de nuevo click derecho -> Eliminar.

POST USO) pgAdmin 4 y programa
	
	Una vez usado el programa y no se quiera volver a usar los datos cargados desde el csv en pgAdmin 4 podemos limpiar la base de datos o crear otra, aunque para el caso de crear otra tendremos que modificar parte del codigo mencionado en el punto 1).
	Para limpiar la base de datos le damos click derecho a la tabla, Scripts -> DELETE SCRIPT, borramos el WHERE <condition> y dejamos solo 'SELECT * FROM nombredetabla' y ejecutamos el script, esto deberia limpiar el contenido de la tabla, se puede verificar dandole click derecho a la tabla, View Data -> View All Rows.
	Si hay filas/correos que no tienen una fecha asignada en fechaNotificado o tienen fecha 1-1-2000 es porque el correo no se envio o hubo un problema en el envio, puede deberse a que la tarea programada configurada anteriormente haya terminado antes de completar los envios, se puede hacer una nueva tarea para esto
	el programa simplemente intentara enviar de nuevo a los correos que no tienen una fechaNotificado cargada.
		



----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


NOTAS EXTRAS:


Expiracion: en las pruebas realizadas con el programador de tareas la expiracion simplemente saltea el resto de ejecuciones del mismo dia pero aparentemente continua ejecutando al siguiente dia. 
	(Deberia completar esta informacion ya que voy a probarlo mientras estoy escribiendo esto)

NOTA: Si sigue provocando problemas el script probar con abrir los puertos correspondientes del firewall.
