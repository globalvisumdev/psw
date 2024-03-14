<?php


ini_set('display_errors', 1);

if (isset ($_REQUEST['cmd'])){
    $cmd = $_REQUEST['cmd'];

    include_once('conec_db.php');

    if($cmd = 'listar'){

        // como traer datos del js
        // $groupid = $_POST['id'];

        $Q = "SELECT cliente_nombre nombre
                FROM cliente";

        $stmt = $conn->prepare($Q);
        $stmt->execute();
    
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $result[] = $row;
        }
        echo json_encode($result);
        die();
    }


}
?>