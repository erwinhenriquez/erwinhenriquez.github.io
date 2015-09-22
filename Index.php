<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title> Proyecto Running Log 1</title>
    </head>
    <body>
        <?php
		//Pone la timezone predeterminada.
date_default_timezone_set ('UTC');

		//Conecta con el servidor
$server = "localhost";
$root = "root";
$password = "goldensun2591";
$database = "Runlog";
$conn = mysqli_connect($server, $root, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . $conn->connect_error);
} 
mysqli_close($conn);
?>
			
<h1>Running Log</h1>
				
<!--codigo para el formulario html usa el codigo php en el mismo archivo-->    
			<div class="log_input">
			<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]);?> "method="post">
	
<!--campos del formulario-->
  <!--La fech utilizara una etiqueta de bootstrap para colocar la fecha #datepicker-->
Date<br>
	<input type="text" name="fecha" required placeholder="mm/dd/yy"><br>
Distance<br>
	<input type="number" step="0.1" name="distancia" required> in km<br>
Tiempo<br>
<!--  IMPORTANTE aunque el campo este separado para minutos y segundos el tiempo se guarda comu una sol variable en SEGUNDOS-->
	Minutes
		<input type="text" name="minutos" required>
	Seconds
		<input type="text" max="59" min="00" required name="segundos"><br>
Pace<br>
	<input type="text" name="ritmo" readonly> Pace is calculated with the time and distance.<br>
BPM<br>
	<input type="text" name="ppm"><br>
Tipo de entrenamineto<br>
	<input type="text" name="entrenamiento"><br>
    <input type="submit">
    </form>
			</div>
			<div class="run_log">
			<?php

$conn = mysqli_connect($server, $root, $password, $database);
		//asegura el codigo se ejecute solo cuando este el POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    //prueba la fecha
  $testdate = $_POST["fecha"];
  $test_arr = explode('/', $testdate);
  if (checkdate($test_arr[0], $test_arr[1], $test_arr[2])){
    //para colocar la distancia obligtoria, usa exit para terminar con el codigo, almacena el error como variable para poder mostrarla en otra parte
if (empty($_POST["distancia"])){
    echo $distanciaErr = "distancia es obligatorio";
    exit;
}
    //para colocar el tiempo obligtorio, usa exit para terminar con el codigo, almacena el error como variable para poder mostrarla en otra parte
elseif(empty($_POST["minutos"])){
    echo $minutosErr = "minutos es requerido";
    exit;
}   
    //si el campo 'ppm' esta vacio le asigna el valor NULL. todos los demas valores son los del formulario
elseif(empty($_POST["ppm"])){
    $ppm = "NULL";
    $fecha = $_POST["fecha"];
    $distanciakm = $_POST["distancia"];
        $distancia = $distanciakm * 1000;
    $tiempo = $_POST["segundos"] + ($_POST["minutos"] * 60);
    $ritmo = $tiempo * 1000 / $distancia;
	if (empty($_POST["entrenamiento"])){
		$entr = "NULL";
	}
	else {
		$entr = $_POST["entrenamiento"];
	}
}
    //si todos los campos estan llenos registra todas las variables.
else{
  $fecha = $_POST["fecha"];
  $distanciakm = $_POST["distancia"];
    $distancia = $distanciakm * 1000;
  $tiempo = $_POST["segundos"] + ($_POST["minutos"] * 60);
  $ritmo = $tiempo * 1000 / $distancia;
  $ppm = $_POST["ppm"];
	if (empty($_POST["entrenamiento"])){
		$entr = "NULL";
	}
	else {
		$entr = $_POST["entrenamiento"];
	}
}
  }
      else {
        echo "<h1>fecha en formato incorrecto</h1>";
      }
    
    //codigo para insertar las variables a la base de datos. el campo de 'id' siempre se pone NULL porque esta en A_I. la fecha se cambia del formato de input al formato de mysql. si el formulario los permite los valores vacios se agregan como NULL.
$input = "INSERT INTO running (id, fecha, distancia, tiempo, ritmo, ppm, entr)
VALUES (NULL, STR_TO_DATE('$fecha', '%m/%d/%Y'), '$distancia', '$tiempo', '$ritmo', '$ppm', '$entr')";
  
if(mysqli_query($conn, $input)){
    echo "registro con exito";
}
    else{
        echo "registro fallo";
  }
}

		//seleccional los datos de toda la tabla guarda los resultados en la variable $query_select
$select = "SELECT * FROM running ORDER BY fecha DESC LIMIT 7";
  $query_select = mysqli_query($conn, $select);

    //cuenta el numero de rows
$num_rows = mysqli_num_rows($query_select);
?>
				<table>
					<?php
		//si existen rows
if( $num_rows > 0){
  
		//hace los titulos de la tabala
  print "<tr><th>Date</th><th>Distance</th><th>Time</th><th>Pace</th><th>BPM</th><th>Tipo de entrenamineto</th></tr>";
  
  	//muestra cada valor en la tabla
  while ($row = mysqli_fetch_assoc($query_select)){
    
		//transforma las variables tiempo y ritmo a minutos y segundos
    $minutos = floor($row["tiempo"] / 60);
    $segundos = $row["tiempo"] % 60;
    $ritmominutos = floor ($row["ritmo"] / 60);
    $ritmosegundos = $row["ritmo"] % 60;
		
		//transforma la distancia de m a km para mostrarla en la tabala la guarda en un avariable
     $distancia_km = $row["distancia"] / 1000;
		
		//convierte la fecha de mysql a "m/d/y" y la almacena la fecha en una variable
    $strtotime = strtotime($row["fecha"]);
		$fechadisplayformat = date ("m/d/y", $strtotime);
		
    //muestra la fecha y distancia
    print "<tr><td>" .$fechadisplayformat. "</td><td>" .$distancia_km. " km";
    
  //si segundos es < 10 agrega un 0 y la muestra si segundos > 10 lo deja asi y lo muestra
    if ($segundos < 10){
      print "</td><td>". $minutos.":0".$segundos. "</td><td>";
    }
    else {
      print "</td><td>". $minutos.":".$segundos. "</td><td>";
    }
  
    //si rito en segundos es < 10 acomoda el formato para mostrarlo
    if($ritmosegundos < 10){
    echo $ritmominutos.":0".$ritmosegundos;
    }
    else {
      print $ritmominutos.":".$ritmosegundos;
    }
    
    //muestra las ppm
    print " /km</td><td>" .$row["ppm"]. "</td><td>" .$row["entr"]. "</td></tr>";
  }
}
else {
  echo "0 results";
}

  mysqli_close($conn);

?>
				</table>
			</div>
	</body>
</html>