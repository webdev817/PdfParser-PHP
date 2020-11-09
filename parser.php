<?php
 
// Include 'Composer' autoloader.
include 'vendor/autoload.php';

$parser = new \Smalot\PdfParser\Parser();


$tabelData = array();
$_LDV = array();
$_DATA = array();
$_DESTINATARIO = array();
$_INDIRIZZO = array();
$_CAP = array();
$_COD = array();
$_OP = array();
$_COL_PESO = array();

$cnt = 0;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PDFPARSER";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
// $sql = "DELETE  FROM giros;";
// $sql = 'DELETE  FROM parsed_results';
// if($conn->query($sql) === FALSE){
//         echo "Error: " . $sql . "<br>" . $conn->error;
//     }

// exit;
session_start();
if($_POST['flag1'] == true){

    $giros = $_SESSION['Giro'];
    $id = $_POST['ID'];
    $giroName = $_POST['name'];
    $giros[$id] = $giroName;
    $_SESSION['Giro'] = $giros;
    print_r($_SESSION['Giro']);
    // echo "Successfully Renamed";
}
if($_POST['flag'] == true){
    
    $giros = $_SESSION['Giro'];
    $id = $_POST['ID'];
    if($id >0){
        $giroName = $_POST['name'];
        $giros[$id] = $giroName;
    }
    $filename = $_SESSION["fileName"];
    $pdf    = $parser->parseFile($filename);
    $pages  = $pdf->getPages();

    foreach ($pages as $key=>$page) {
        $items = $page->getTextArray();
        $cntText = count($items);
        $i = 0;
        $table_Start = 0;
        $rowCnt = 0;
        $colCnt = 0;
        $rowCnt = (int)$items[31];
        if($rowCnt === 1){
            $cnt++;
        }
        for ($i = 31; $i < $cntText; $i++)
        {   
            if((int)$items[$i] === $rowCnt + 1 && strlen($items[$i]) < 5){
                
                $rowCnt++;
                $colCnt = 0;
            }
            if($items[$i] != "Data e ora del ritiro:"){
                $tabelData[$cnt-1][$rowCnt-1][$colCnt] = $items[$i];
                $colCnt++;
            }
            else{
                break;
            }
        }    
    }
    for($i = 0; $i < $cnt; $i++){
        for($k = 0; $k < count($tabelData[$i]); $k++){
            $lenTemp = count($tabelData[$i][$k]);
            $_LDV[$i][$k] = $tabelData[$i][$k][1];
            $_DATA[$i][$k] = $tabelData[$i][$k][2];
    
            
            if($lenTemp < 17){

                $_DATA[$i][$k] = "";
                $_DESTINATARIO[$i][$k] = "Lorenzo Palmieri";
                $_INDIRIZZO[$i][$k] = "Via Dottor Michele Carbone 29 | 70032 Bitonto"."\n"."Note:";
                $_CAP[$i][$k] = "70017";
                $_COD[$i][$k] = "n.a.";
                $_OP[$i][$k] = "n.a n.a n.a";
                $_COL_PESO[$i][$k] = "1/1kg";
            }
            else if($tabelData[$i][$k][16] != "Note:" && $lenTemp > 17){
                $_DESTINATARIO[$i][$k] =str_ireplace("'", "`", $tabelData[$i][$k][3]."\n ".$tabelData[$i][$k][4]);
                $_INDIRIZZO[$i][$k] = str_ireplace("'", "`", $tabelData[$i][$k][5]."\n"."Note: ");
                $_CAP[$i][$k] = $tabelData[$i][$k][8];
                $_COD[$i][$k] = $tabelData[$i][$k][6];
                $_OP[$i][$k] = $tabelData[$i][$k][12]." ".$tabelData[$i][$k][13]." ".$tabelData[$i][$k][14];
                $_COL_PESO[$i][$k] = $tabelData[$i][$k][7];
            }
            else if($tabelData[$i][$k][16] === "Note:" && $lenTemp > 17){
                $_DESTINATARIO[$i][$k] = str_ireplace("'", "`", $tabelData[$i][$k][3]);
                $_INDIRIZZO[$i][$k] = str_ireplace("'", "`", $tabelData[$i][$k][4]. "\n"."Note: ".$tabelData[$i][$k][$lenTemp-1]);
                $_CAP[$i][$k] = $tabelData[$i][$k][7];
                $_COD[$i][$k] = $tabelData[$i][$k][5];
                $_OP[$i][$k] = $tabelData[$i][$k][11]." ".$tabelData[$i][$k][12]." ".$tabelData[$i][$k][13];
                $_COL_PESO[$i][$k] = $tabelData[$i][$k][6];
    
            }
            else{
                $_DESTINATARIO[$i][$k] = str_ireplace("'", "`", $tabelData[$i][$k][3]);
                $_INDIRIZZO[$i][$k] = str_ireplace("'", "`", $tabelData[$i][$k][4]. "\n Note: ");
                $_CAP[$i][$k] = $tabelData[$i][$k][7];
                $_COD[$i][$k] = $tabelData[$i][$k][5];
                $_OP[$i][$k] = $tabelData[$i][$k][11]." ".$tabelData[$i][$k][12]." ".$tabelData[$i][$k][13];
                $_COL_PESO[$i][$k] = $tabelData[$i][$k][6];
                
            }
            
        }
    }

    // parsed results insert in database
    $pickups = $_SESSION["Pickup"];
    $giro1 = $giros[0];
    $pickup1 = $pickups[0];
    $firstId = 0;
    $sql = "INSERT INTO giros(gname, pickup)
        VALUE ('$giro1', '$pickup1');";
        if($conn->query($sql) === FALSE){
            echo "Error: " . $sql . "<br>" . $conn->error;
        } 
    foreach($giros as $key=>$giro){
        $cnt = 0;
        $pickup = $pickups[$key];

        if($key === 0){
            $sql = "SELECT id FROM giros WHERE gname = '$giro1';";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $firstId = $row["id"];
                }
                } else {
                echo "0 results";
                }
        }
       else{
            $sql = "INSERT INTO giros(gname, pickup)
            VALUE ('$giro', '$pickup');";
            if($conn->query($sql) === FALSE){
                echo "Error: " . $sql . "<br>" . $conn->error;
            }  
       } 
      
        foreach($_LDV[$key] as  $LDV){
            $data = $_DATA[$key][$cnt];
            $dest = $_DESTINATARIO[$key][$cnt];
            $ind = $_INDIRIZZO[$key][$cnt];
            $cap = $_CAP[$key][$cnt];
            $cop = $_COD[$key][$cnt];
            $op = $_OP[$key][$cnt];
            $col = $_COL_PESO[$key][$cnt];
            $sqll = "INSERT INTO parsed_results(LDV,DATA_,DESTINATARIO,INDIRIZZO,CAP,COD,op, COL, giroId)
            VALUE ('$LDV','$data','$dest', '$ind','$cap','$cop','$op','$col','$firstId');";
            
            if($conn->query($sqll) === FALSE){
                echo "Error: " . $sqll . "<br>" . $conn->error;
            }
            $cnt++;
        }
        $firstId++;

    }
    echo "Successfully saved";
}


//check with your logic
if (!empty($_FILES)) {
    print_r($_FILES);
    $_Giro = array();
    $_Pickup = array();
    $error = false;
    $files = array();

    $uploaddir = __DIR__.'/';
    foreach ($_FILES as $file) {

        $name = $file['name']; 
        $_SESSION["fileName"] = $name;
        if (move_uploaded_file($file['tmp_name'], $uploaddir . basename( $file['name']))) {
            $files[] = $uploaddir . $file['name'];
        } else {
            $error = true;
        }
    
        $pdf    = $parser->parseFile($name);
        $pages  = $pdf->getPages();
        $cnt1 = 0;
        foreach ($pages as $key=>$page) {
            $items = $page->getTextArray();
            $rowCnt = 0;
            $rowCnt = (int)$items[31];
            if($rowCnt === 1){
                $_Giro[$cnt1] = $items[13];
                $_Pickup[$cnt1] = $items[16];
                $cnt1++;
            }
           
        }
        
    }

    foreach($_Giro as $key=>$giro){            
        echo '<option value="' . $key . '" ' . 'data-type="int">' . $giro . '</option>';
    }
   $_SESSION['Giro'] = $_Giro;
   $_SESSION['Pickup'] = $_Pickup;
    $data = ($error) ? array('error' => 'There was an error uploading your files') : array('files' => $files);
} else {
    $data = array('success' => 'NO FILES ARE SENT','formData' => $_REQUEST);
}
 
?>