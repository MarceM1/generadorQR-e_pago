<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/style.css">
	<title>Document</title>
</head>

<body>
	<section>
		<fieldset>
			<legend>Generador de Orden QR</legend>
			<form action="" method="post">
				<label for="monto">Ingresa un monto: </label>
				<input type="text" name="monto_txt" id="monto" title="Ingresa un monto entre 650 y 999999999999.99" required pattern="\d+(\.\d{1,2})?">
				<button type="submit" name="qr_btn">Generar orden QR</button>
			</form>
		</fieldset>
		<article>

			<?php
			require 'lib/epagos_api.class.php';

			const ID_ORGANISMO = "111";
			const ID_USUARIO   = "1468";
			const PASSWORD     = "da443c4ddd73f43d4eef608d1427d3e7";
			const HASH         = "05ab5ab3605189ee5c688a0a147d8fed";

			if (isset($_POST['qr_btn'])) {
				//obtengo Token
				try {
					$epagos = new epagos_api(ID_ORGANISMO, ID_USUARIO);
					$epagos->set_entorno(EPAGOS_ENTORNO_SANDBOX);
					$ret = $epagos->obtener_token(PASSWORD, HASH);

					if (!$ret["token"]) {
						echo 'Error: <b>generar_token</b>: ';
						echo "<pre>";
						print_r($ret);
						echo "</pre>";
						exit;
					}

					// echo "Token: " . $ret["token"] . "<br>";

					$monto = $_POST['monto_txt'];

					if (is_numeric($monto))
						$monto = (float) $monto;

						if ($monto >= 650 && $monto <= 999999999999.99) {
							// Redondear a dos decimales
							$monto = round($monto, 2);
							echo "Monto válido: $monto<br>";
					} else {
						echo "Error: El monto debe estar entre 650 y 999999999999.99<br>";
					}


					//Variables para obtener caja y orden qr
					$version = '2.0';
					$credenciales = array(
						'id_organismo' => ID_ORGANISMO,
						'token' => $ret["token"]
					);



					//Chequeo contenido de variables
					// echo "Contenido de \$version: " . $version . "<br>";
					// echo "Contenido de \$credenciales: <pre>";
					// print_r($credenciales);
					// echo "</pre>";


					//configuro SOAP
					$options = array(
						'location' => 'http://sandbox.epagos.com.ar/wsdl/2.1/index.php',
						'uri'      => 'https://sandbox.epagos.com.ar',
						'trace'    => 1
					);

					$client = new SoapClient(null, $options);


					$result = $client->obtener_cajas_qr($version, $credenciales);

					// echo "Respuesta obtener_cajas_qr: <pre>";
					// print_r($result);
					// echo "</pre>";


					$orden = array(
						'id_caja' => $result['cajas'][0]->id_caja,
						'importe' => $monto,
						'concepto' => "Pago de prueba",
						'vencimiento' => date("Y-m-d")
					);
					//echo "La fecha de vencimiento es: ".$orden["vencimiento"]."<br/>";
					// echo "Contenido de \$orden: <pre>";
					// print_r($orden);
					// echo "</pre>";

					try {
						$result = $client->generar_orden_qr($version, $credenciales, $orden);
						// echo "Respuesta generar_orden_qr: <pre>";
						// print_r($result);
						// echo "</pre>";
						print_r($result["respuesta"]);
					} catch (SoapFault $e) {
						echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					}
				} catch (Exception $e) {
					echo 'Excepción capturada: ',  $e->getMessage(), "\n";
				}
			}
			?>
		</article>
	</section>
</body>

</html>