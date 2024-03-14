<?php
include ("../config.php");
require_once "../psw/Security.class.php";
include('../include.php');
$mySecurity = new Security();

if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
    $mySecurity-> GotoThisPage( "../psw/login.php" );
}

if ($_GET['cmd'] == 'del') {
    $id = addslashes($_REQUEST['id']);
    
    $ban=0;
    $Q="select * from accountaction_eventual where aae_id=:key and aae_fecha_hasta > now()";
    
    $stmt = $ezMap['pdo_db']->prepare($Q);
    $datadb=array(':key'=>$id);
    $stmt->execute($datadb);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ban=1;        
    }
    $stmt=null;
        
    if ($ban==0) {
        echo json_encode(array('errorMsg'=>'No puede eliminar un permiso pasado.'));
        die();    
    }

    $Q = "delete from accountaction_eventual where aae_id=:key";

    try {
        $stmt = $ezMap['pdo_db']->prepare($Q);
        $datadb=array(':key'=>$id);
        $stmt->execute($datadb);
        echo json_encode(array('success'=>true));
    }catch(PDOException  $e ){
        echo json_encode(array('errorMsg'=>'Algun error ha ocurrido.'));
    }
    
    die();
}

if (isset($_POST[listar])) {

    $formato_ver = $_POST[formato_ver];

    if ($formato_ver == 'html') {
        // echo '<LINK type="text/css" rel="stylesheet" href="../map.css">';
        // echo '<LINK type="text/css" rel="stylesheet" href="../admin/rg/sylex.css">';
        ?> 
        <script type="text/javascript" src="../Classes/easyui/jquery.min.js"></script>
        <script>
        function myFunction(control) {
            var repuesta = window.confirm('Eliminando.. esta seguro?');
            if(repuesta==true){
                $.post('./accountaction_eventual.php?cmd=del',{id:control},function(result){
                    if (result.success){
                        location.reload();
                    } else {
                        alert(result.errorMsg);
                    }
                    
                },'json');
            }
            //location.reload();
        }
        </script>
        <?php 
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<link rel="stylesheet" href="../Classes/w34.css">';
    }

    if ($formato_ver == 'xls') {
        include '../admin/Classes/PHPExcel.php';
    }

        $sql = "select * from accountaction_eventual,accounts,actions
			where cliente_id =:cli
            and accountid=aae_accountid
            and actionid = aae_actionid 
            order by username           
                ";
        // echo '$query: '.$sql.'<br>';
         //die ($sql);

        try {
            $resultp2 = $ezMap['pdo_db']->prepare($sql);
            $datadb=array(':cli'=>$_SESSION[cliente_id]);
            $resultp2->execute($datadb);

            $primera_vez = true;
            $renglon = 0;
            $cant = 1;
            
            $titulo = 'Listado de Permisos Eventuales al '.date('Y-m-d H:i:s');
            $nombre_archivo = $titulo . '.' . $formato_ver;
            include '../admin/salidaWriter.php';
            $doc = new salidaWriter();
            $xlsRow = 1;
            
            if ($formato_ver == 'xls') {
                PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, array(
                    'memoryCacheSize' => '256MB'
                ));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Micronauta");
                $objPHPExcel->getDefaultStyle()
                ->getFont()
                ->setName('Calibri')
                ->setSize(10)
                ->setBold(false);
                
                $objPHPExcel->setActiveSheetIndex(0);
                
                $objPHPExcel->getActiveSheet()
                ->getDefaultStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()
                ->getDefaultStyle()
                ->getAlignment()
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                
                // $objPHPExcel->getActiveSheet ()->getStyle ( 'A1:A200' )->getAlignment ()->setHorizontal ( PHPExcel_Style_Alignment::HORIZONTAL_LEFT );
                
                $doc->salidaXls($nombre_archivo, $objPHPExcel);
            }
            if ($formato_ver == 'csv') {
                $doc->salidaCsv($nombre_archivo);
            }
            if ($formato_ver == 'html') {
                $doc->salidaHtml();
            }
            while($row=$resultp2->fetch(PDO::FETCH_ASSOC)) {
                
                if ($primera_vez) {
                    $primera_vez = false;
                    
              
                    $doc->setCell(0, $xlsRow, $titulo, array(
                    'style' => 'colspan="100" align="left"',
                    'format' => 'b'
                    ));
                    $xlsRow ++;
                    $doc->openTable('class="w3-table-all w3-hoverable w3-border " width="100%"');
                    $doc->setRowFormat('class="w3-teal"');
                    
                    $doc->setCell(0, $xlsRow, utf8_encode('Nombre'), array(
                        'tag' => 'th',
                        'style' => 'align="center"'
                    ));
                    $doc->setCell(1, $xlsRow, utf8_encode('Apellido'), array(
                        'tag' => 'th',
                        'style' => 'align="center"'
                    ));
                    $doc->setCell(2, $xlsRow, utf8_encode('Username'), array(
                        'tag' => 'th',
                        'style' => 'align="center"'
                    ));
                    $doc->setCell(3, $xlsRow, utf8_encode('Permiso'), array(
                        'tag' => 'th',
                        'style' => 'align="center"'
                    ));
                    $doc->setCell(4, $xlsRow, utf8_encode('Desde'), array(
                        'tag' => 'th',
                        'style' => 'align="center"'
                    ));
                    $doc->setCell(5, $xlsRow, utf8_encode('Hasta'), array(
                        'tag' => 'th',
                        'style' => 'align="center"'
                    ));
                    $doc->setCell(6, $xlsRow, utf8_encode('Creado'), array(
                        'tag' => 'th',
                        'style' => 'align="center"'
                    ));
                    $doc->setRowFormat('class=""');
                    // $doc->closeTable ();
                }
                
                $xlsRow += 1;
                
                $doc->setCell(0, $xlsRow, ($row[firstname]), array(
                    'style' => 'align="left"'
                ));
                $doc->setCell(1, $xlsRow, ($row[lastname]), array(
                    'style' => 'align="left"'
                ));
                $doc->setCell(2, $xlsRow, ($row[username]), array(
                    'style' => 'align="left"'
                ));
                $doc->setCell(3, $xlsRow, ($row[actionname]), array(
                    'style' => 'align="left"'
                ));
                $doc->setCell(4, $xlsRow, ($row[aae_fecha_desde]), array(
                    'style' => 'align="left"'
                ));
                $doc->setCell(5, $xlsRow, ($row[aae_fecha_hasta]), array(
                    'style' => 'align="left"'
                ));
                $doc->setCell(6, $xlsRow, ($row[aae_username_alta]." ".$row[aae_fecha_alta]), array(
                    'style' => 'align="left"'
                ));
                
                if ($formato_ver =='html') {
                    $doc->setCell(7,$xlsRow,'<span onclick="myFunction('.$row['aae_id'].')" class="w3-button ">&times;</span>');
                }
                
                
            }
            
        }catch(PDOException  $e ){
            $this-> SetErrorMessage('Error de lectura le','');
            if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura le','');
            return false;
        }
        
        $doc->closeTable();
        $doc->addHtml('</table>');
        if ($primera_vez) echo "No hay información para msotrar.";
        $doc->addHtml($LinkRetorno);
        $doc->addHtml('<br><br>');

        $doc->output();
        // HardFlush();
        die();
        
}
if (isset($_POST[grabar])) {
//     $q="insert into accountaction_eventual (aae_accountid,aae_actionid,aae_fecha_desde,aae_fecha_hasta,aae_username_alta,aae_fecha_alta) 
// values(".intval($usuario).",".intval($permiso).",'".substr($fdesde,0,10).' '.substr($hdesde,0,5)."','".substr($fhasta,0,10).' '.substr($hhasta,0,5)."','".$_SESSION['username']."',now())";

    $q="insert into accountaction_eventual (aae_accountid,aae_actionid,aae_fecha_desde,aae_fecha_hasta,aae_username_alta,aae_fecha_alta)
values(:acc,:permiso,:fdesde,:fhasta,:user,now())";
    try {
        $resultp2 = $ezMap['pdo_db']->prepare($q);
        $datadb=array(':acc'=>$usuario,':permiso'=> $permiso,':fdesde'=>substr($fdesde,0,10).' '.substr($hdesde,0,5),':fhasta'=>substr($fhasta,0,10).' '.substr($hhasta,0,5),':user'=>$_SESSION['username']);
        $resultp2->execute($datadb);
    }catch(PDOException  $e ){
        echo "No pude grabar el permiso, reintente.";
    }
    
}
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../Classes/w34.css">

<div class="w3-container w3-card-4 w3-margin">
	<form method="post">
		<div class="w3-panel w3-teal">
			<h3>Permisos Eventuales</h3>
		</div>
		<p>
		<select class="w3-select" name="permiso" required >
		<option value="" disabled selected>Seleccione un permiso</option>
		<?php
		$q="SELECT a.actionid,actionname FROM actions a
        left join actionclientedenied acd on acd.actionid=a.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
,groupaccounts ag
		LEFT JOIN groupactions ga ON ag.groupid=ga.groupid
		WHERE ag.accountid=:acc
		and acd.actionid is null and (a.actionid = ga.actionid
		    or ga.actionid = floor(a.actionid/100)*100 )
		    and actionclase = 0
		    union
		    
		    SELECT a.actionid,actionname FROM accountaction ag ,actions a
            left join actionclientedenied acd on acd.actionid=a.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
		    WHERE ag.accountid=:acc
		    and acd.actionid is null 
            and (a.actionid = ag.actionid
		        or ag.actionid = floor(a.actionid/100)*100 )
		        and actionclase = 0
		        order by 2";
		
		try {
		    $resultp2 = $ezMap['pdo_db']->prepare($q);
		    $datadb=array(':acc'=>$_SESSION['myAccount']);
		    $resultp2->execute($datadb);
		    while($row=$resultp2->fetch(PDO::FETCH_ASSOC)) {
		        echo '<option value="'.$row['actionid'].'">'.$row['actionname'].'</option>';
		    }
		}catch(PDOException  $e ){}
		?>
  </select>
  </p><p>
		<select class="w3-select" name="usuario" required >
		<option value="" disabled selected>Seleccione un usuario</option>
		<?php
		$q="SELECT
		a.*
		FROM
		megacontrol.accounts a 
		LEFT JOIN megacontrol.groupaccounts ga ON a.accountid = ga.accountid 
		LEFT JOIN megacontrol.groups g ON g.groupid = ga.groupid 
		WHERE
		(
		    hierarchy >= ".$_SESSION [ 'myHierarchy' ]."
		    OR hierarchy IS NULL
		    )
		    AND a.accountid > 1 
		    AND a.cliente_id = '".$_SESSION ['cliente_id']."'
		    GROUP BY
		    accountid 
		    ORDER BY
		    lastname,
		    firstname ";

		try {
		    $resultp2 = $ezMap['pdo_db']->prepare($q);
		    $datadb=array(':hier'=>$_SESSION['myHierarchy'],':cli'=>$_SESSION ['cliente_id']);
		    $resultp2->execute($datadb);
		    while($row=$resultp2->fetch(PDO::FETCH_ASSOC)) {
		        echo '<option value="'.$row['accountid'].'">'.$row['lastname'].",".$row['firstname'].'</option>';
		    }
		}catch(PDOException  $e ){}
		
		?>
  </select>
  </p>
		<p><label>Desde </label>
			<input class="w3-input" type="date" required name="fdesde" > 
			<input class="w3-input" type="time" required name="hdesde" > 
		</p>
		<p><label>Hasta </label>
			<input class="w3-input" type="date" required name="fhasta" > 
			<input class="w3-input" type="time" required name="hhasta" > 
		</p>
		<p>
			<input class="w3-btn w3-red" type="submit" name="grabar" value="Grabar">
			<input class="w3-btn w3-red" type="reset" name="cancelar" value = "Cancelar">
		</p>
</form>
</div>
<div class="w3-container w3-card-4 w3-margin">
	<form method="post" target="_blank">
		<select class="w3-select w3-border" name="formato_ver" id="formato_ver">
		<?php salidaOptions ( '',array('html','xls','csv') );?>
		</select>
		<p>
			<input class="w3-btn w3-red" type="submit" name="listar" value="Mostrar">
		</p>
	</form>
</div>
      