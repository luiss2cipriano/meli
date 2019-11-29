<?php

try {
    $bd_connection = new PDO
('mysql:host=$ip_server;dbname=$nombre_bd', $usuario, $contraseña);
$consulta=$bd_connection->prepare ("select * from items where seller_id = 81644614  AND site_id = 'MLA'; ");

$show=$consulta->execute();

echo $show;

}

catch (Exception $e) { 	

    echo $e->getMessage(); 	

                                    }

?>
