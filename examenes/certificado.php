<?php
session_start();
require_once('../config.php'); 
//require('../librerias/FPDF/fpdf.php');
require('../librerias/fpdf2/fpdf.php');

header('Content-Type: text/html; charset=utf-8');
setlocale(LC_TIME, 'es_ES');

if (isset($_SESSION['usuario'])) {
    if (isset($_POST['idExamen'])) {
        $id = $_POST['idExamen'];

       $url = URL_CERTIFICADO; 

        // Datos a enviar en el cuerpo de la solicitud POST
        $data = array(
            'id' => $id
        );

        //print($id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error en la solicitud cURL: ' . curl_error($ch);
        } 
        else 
        {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == 200) {
                // Decodificar la respuesta JSON
                $responseData = json_decode($response, true);

                ///echo var_dump($responseData['data']['array']);             

                if (is_array($responseData)) {

                    // Clase personalizada que hereda de FPDF                                

                    
                    class MyPDF extends FPDF
                    {                          
                            var $_strApobadoTeo = "";
                            var $_strApobadoPra = "";

                            public function __construct() {
                                parent::__construct();

                               // $this->SetTitle("Cert", true);
                            }                            

                            function obtenerMesEspanol($mes) {
                                $mesesIngles = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                                $mesesEspanol = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
                                
                                return str_replace($mesesIngles, $mesesEspanol, $mes);
                            }
                           // Función para la cabecera del PDF
                           public function Header() {

                            $this->marcaAgua();

                            // Ruta de la imagen de la cabecera
                            $headerImagePath = '../img/logoCea.jpg'; // Reemplaza con la ruta correcta de la imagen
                        
                            // Configuración de la posición y tamaño de la imagen
                            $headerImageWidth = 82; // Ancho de la imagen en la cabecera
                            $headerImageHeight = 35; // Alto de la imagen en la cabecera
                        
                            // Calcular la posición X centrada para la imagen
                            $headerImageX = ( $this->GetPageWidth() - $headerImageWidth ) / 2;
                        
                            // Calcular la posición Y de la imagen en la cabecera
                            $headerImageY = $this->GetY();
                        
                            // Insertar la imagen en la cabecera
                            $this->Image($headerImagePath, $headerImageX, $headerImageY, $headerImageWidth, $headerImageHeight);
                        }
                        function Footer() {

                           // Establecer la posición del pie de página a 15 mm desde el final de la página
                            $this->SetY(-15);

                            // Establecer el color de la línea a VERDE
                            $this->SetDrawColor(0, 99, 0);                            
                            // Dibujar una línea de 1 punto de grosor
                            $this->SetLineWidth(1);
                            $this->Line(3, $this->GetY(), $this->GetPageWidth() - 3, $this->GetY());
                            $this->SetFont('Arial', '', 10); 

                            $this->Ln(2);
                            $this->Cell(100, 4, 'Sede   Medellin:  Calle 55  No. 70 - 59  Barrio los Colores  -  Sede Bello: Carrera  50  No 29A - 39  Bodega  112 - Barrio La Florida ', 0, 0, 'J');
                            $this->Ln(5);
                            $this->Cell(124, 4, 'Tel: 4039903 Cel: 316 690 0608 | 317 539 9425 www.ceamasconduccion.com', 0, 0, 'L');
                            $this->Cell(40, 4, '    masconduccion', 0, 0, 'R');
                            $this->Cell(42, 4, '    cea.masconduccion', 0, 1, 'R');

                            $iconoFaceBook  = '../img/iconoFaceBook.jpg';
                            $iconotwitter   = '../img/iconoTwitter.jpg';
                            $iconoInstagram = '../img/iconoInstagram.jpg';
                           
             
                            //CARGAR IMAGENES ICONO EN PIE REDES SOCIALES.
                            //***************************** POSICIONES *********
                            //             Ruta              | X  | Y | ANCHO |ALTO                            
                              $this->Image( $iconoFaceBook,   130, 271, 4,   4);
                              $this->Image( $iconotwitter,    135, 271, 4,   4);
                              $this->Image( $iconoInstagram,  170, 271, 4,   4);    
 
                            //*********************************************** */                       
                       
                        }      
                        
                        function marcaAgua() {

                          $marcaAgua = '../img/marcaAgua.png';      
                          $this->Image( $marcaAgua,   45, 123, 138,   50);
                      }
                      function marcaAguaFirma() {

                        $marcaAgua = '../img/firmaJuan.png';      
                        $this->Image( $marcaAgua,   5, 233, 38, 28);
                    }
                      
                        
                        // Generar contenido del PDF
                        function GeneratePDF($data)
                        {
                                //quitar tildes ya que pasara a Mayuscula                            
                                $mapping = array(
                                    'á' => 'a',
                                    'é' => 'e',
                                    'í' => 'i',
                                    'ó' => 'o',
                                    'ú' => 'u',
                                    'Á' => 'A',
                                    'É' => 'E',
                                    'Í' => 'I',
                                    'Ó' => 'O',
                                    'Ú' => 'U',
                                );

                            $this->AliasNbPages(); // Definir el número total de páginas 
                            $this->SetMargins(5,5,5); 
                            $this->AddPage('P', 'letter');
                           

                            // Configurar fuente y tamaño
                              $this->SetFont('Arial', 'B', 8); 
                              $this->SetAutoPageBreak(false);                     


                              $this->Ln(30); 
                              $fecha = strtotime($data[0]['dteCiudadFecha']);
                              $mesEspanol = $this->obtenerMesEspanol(date('F', $fecha));
                              $fechaFormateada = date('d', $fecha) . ' de ' . $mesEspanol . ' de ' . date('Y', $fecha);

                              $this->Cell(25, 6, "Medellin, " . $fechaFormateada);
                              $this->Ln(8); 
                              $this->Cell(25, 6, mb_convert_encoding("Señores: ", 'ISO-8859-1', 'UTF-8'));
                              $this->Ln(5);
                              
                              $bolCopia = false;
                              if  ($data[0]['Nit'] != $_SESSION['usuario'])
                              {
                                   $this->Cell(25, 6, $_SESSION['nombreEmpresa']);
                                   $this->Ln(5);
                                   $this->Cell(25, 6,"Doctor(a): ". $_SESSION['strGerente'] );  
                                   $bolCopia = true;                    

                              }else
                              {
                                   $this->Cell(25, 6, $data[0]['strEmpresa']);
                                   $this->Ln(5);
                                   $this->Cell(25, 6,"Doctor(a): ". $data[0]['strGerente']);
                                   $bolCopia = false;
  
                              }                              
                              
                              
                              $this->Ln(5);
                              $this->Cell(25, 6,"Gerente General");
                              $this->Ln(5);
                              $this->Cell(25, 6,"Medellin");
                              $this->Ln(8);
                              $this->Cell(25, 6,"Asunto: CALIFICACION EXAMEN CONDUCCION");
                              $this->Ln(6);
                              $this->SetFont('Arial', '', 8); // Establecer fuente en negrita
                              $this->Cell(25, 6, mb_convert_encoding("Respetados Señores:     A continuacion se da a conocer el resultado de la evaluacion practicada al señor:", 'ISO-8859-1', 'UTF-8'));
                     

                              //********cargando foto convirtiendo ************ */
                              $imageData = $data[0]['objFoto']['data'];     // Campo base de datos                           
                              $stream = fopen('php://memory', 'r+');        // Crear un flujo temporal de memoria                                   
                              fwrite($stream, pack('C*', ...$imageData));   // Escribir los datos de la imagen en el flujo                           
                              rewind($stream);                              // Mover el puntero del flujo al inicio                             
                              $image = imagecreatefromstring(stream_get_contents($stream));   // Crear una imagen a partir del flujo                           
                              $originalWidth = imagesx($image);  // Obtener el ancho de la imagen original
                              $originalHeight = imagesy($image); // Obtener el alto de la imagen original                            
                              
                              // Definir el nuevo tamaño deseado
                              $newWidth = 70;
                              $newHeight = 80;      

                              // Crear una nueva imagen con el tamaño deseado
                              $newImage = imagecreatetruecolor($newWidth, $newHeight);                              
                              // Redimensionar la imagen original a la nueva imagen con el tamaño deseado
                              imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                              
                              // Guardar la imagen redimensionada en un archivo temporal en formato PNG
                              $tempFilename = tempnam(sys_get_temp_dir(), 'image') . '.png';
                              imagepng($newImage, $tempFilename);
                              
                              // Establecer las coordenadas de la posición de la imagen
                              $x = 10; // Coordenada X
                              $y = 83; // Coordenada Y                              
                              // Mostrar la imagen redimensionada en una celda
                              $this->Image($tempFilename, $x, $y);                              
                              // Eliminar el archivo temporal
                              unlink($tempFilename);                              
                              // Liberar los recursos de las imágenes
                              imagedestroy($image);
                              imagedestroy($newImage);
                              //*********************************************** */

                               //********cargando Huella convirtiendo ************ */
                               $imageData = $data[0]['objHuella']['data'];
                               // Crear un flujo temporal de memoria
                               $stream = fopen('php://memory', 'r+');                              
                               // Escribir los datos de la imagen en el flujo
                               fwrite($stream, pack('C*', ...$imageData));                              
                               // Mover el puntero del flujo al inicio
                               rewind($stream);                              
                               // Crear una imagen a partir del flujo
                               $image = imagecreatefromstring(stream_get_contents($stream));                              
                               // Obtener el ancho y alto de la imagen original
                               $originalWidth = imagesx($image);
                               $originalHeight = imagesy($image);                              
                               // Definir el nuevo tamaño deseado
                               $newWidth = 70;
                               $newHeight = 80;                              
                               // Crear una nueva imagen con el tamaño deseado
                               $newImage = imagecreatetruecolor($newWidth, $newHeight);                              
                               // Redimensionar la imagen original a la nueva imagen con el tamaño deseado
                               imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                               
                               // Guardar la imagen redimensionada en un archivo temporal en formato PNG
                               $tempFilename = tempnam(sys_get_temp_dir(), 'image') . '.png';
                               imagepng($newImage, $tempFilename);
                               
                               // Establecer las coordenadas de la posición de la imagen
                               $x = 35; // Coordenada X
                               $y = 83; // Coordenada Y                              
                               // Mostrar la imagen redimensionada en una celda
                               $this->Image($tempFilename, $x, $y);                              
                               // Eliminar el archivo temporal
                               unlink($tempFilename);                              
                               // Liberar los recursos de las imágenes
                               imagedestroy($image);
                               imagedestroy($newImage);
                               //*********************************************** */  
                               $this->SetFont('Arial', 'B', 8); // Establecer fuente en negrita 
                               $this->Ln(33);
                               $this->Cell(150, 4, "NOMBRES Y APELLIDOS: " . mb_convert_encoding(strtr(mb_strtoupper($data[0]["strPersonalizado"]),$mapping),'ISO-8859-1', 'UTF-8'), 1, 0);
                               $this->Cell(0, 4, "IDENTIFICACION: " . $data[0]["strCliente"], 1, 1);
                               $this->Cell(100, 4, "TIPO DE VEHICULO A CONDUCIR: " . $data[0]["strTipoVehiculo"], 1, 0);
                               $this->Cell(50, 4, "CAT. LICENCIA: " . $data[0]["strCategoriaLicencia"], 1, 0);
                               $this->Cell(0, 4, "REALIZO CURSO: " . $data[0]["strRealizoCurso"], 1, 1);
                               $this->Ln(2);

                               //-----------EVALUACION PRACTICA 
                               $this->Cell(100, 4, "EVALUACION TEORICA", 1, 0, 'C');
                               $this->Cell(0, 4, "VALOR: " . $data[0]["fltPorcentajeteorico"]."%", 1, 1, 'C');
                               $this->Cell(100, 4, "NUMERO DE PUNTOS: ", 1, 0, 'L');
                       
                               ///***** OPERACIONES PARA VALIDAR Y CALCULAR EN LA PARTE TEORICA: *********//     
                               $validarPuntajesTeo = [];  
                               $porFinalTeo = 0; 
                               $totalPuntoTeo = 0;  
                               $totalPuntoTeoCorrectos = 0;                          

                              //recoge todos los valores cuando el examen es teorico
                               foreach ($data as $item) {
                                   if ($item["strTipoExamen"] == "Teo" && $item["strTipoExamen"] != "") {
                                       $validarPuntajesTeo[] = $item;                    
                                   }
                               } 
                                
                               foreach ($validarPuntajesTeo as $item) {  

                                        $totalPuntoTeo          += intval($item["intNroItems"]);  
                                        $totalPuntoTeoCorrectos += intval($item["intNroItemsCorrectos"]);                   
                                        $porFinalTeo            += floatval($item["fltNota"]);                       

                               }  
                               /*****************************************************************************/

                                $this->SetFont('Arial', '',8);
                                $this->Cell(0, 4,  $totalPuntoTeo, 1, 1, 'C');

                                $this->Cell(100, 4, "RESPUESTAS CORRECTAS: ", 1, 0, 'L');
                                $this->Cell(0, 4,  $totalPuntoTeoCorrectos, 1, 1, 'C');

                                $respIncorrectas = $totalPuntoTeo - $totalPuntoTeoCorrectos;
                                $this->Cell(100, 4, "RESPUESTAS INCORRECTAS: ", 1, 0, 'L');                                
                                $this->Cell(0, 4,   $respIncorrectas, 1, 1, 'C');

                                $this->Cell(100, 4, "PORCENTAJE MINIMO QUE SE DEBE OBTENER: ", 1, 0, 'L');
                                $this->Cell(0, 4,  $data[0]["intPuntajeMinimoTeorico"]."%", 1, 1, 'C');

                                $this->Cell(100, 4, "PORCENTAJE PARA EL PUNTAJE FINAL: ", 1, 0, 'L'); 

                                // $parteDecimal = $porFinalTeo - floor($porFinalTeo);
                                // $redondeado = ($parteDecimal > 0.5) ? ceil($porFinalTeo) : floor($porFinalTeo);

                                $this->Cell(0, 4,  $porFinalTeo."%", 1, 1, 'C'); 

                                $this->SetFont('Arial', 'B',8);
                                $this->Cell(100, 4, "NIVEL DE CONOCIMIENTOS TEORICOS: ", 1, 0, 'L');                      
                            
                                  //VALIDAR ESTADO TEORICO 
                                  if ($porFinalTeo >= floatval($data[0]["intPuntajeMinimoTeorico"])) {   
                                              
                                      $_strApobadoTeo = "APROBADO";
                                      $this->Cell(0, 4, $_strApobadoTeo, 1, 1, 'C');
 
                                  }else{

                                        $_strApobadoTeo = "REPROBADO";
                                        $this->Cell(0, 4,$_strApobadoTeo, 1, 1, 'C');

                                  }
                                  //----FIN ESTADO TEORICO                               

                                //**************************-EVALUACION PRACTICA *************************************//
                                $this->Ln(2);
                                $this->Cell(100, 4, "EVALUACION PRACTICA", 1, 0, 'C');
                                $this->Cell(0, 4, "VALOR: " . $data[0]["fltPorcentajePractico"]."% Distribuido asi: ", 1, 1, 'C');


                               ///***** OPERACIONES PARA VALIDAR Y CALCULAR EN LA PARTE PRACTICA: *********//     
                               $validarPuntajesPra = [];  
                               $porFinalPra = 0; 
                               $totalPuntoPra = 0;  
                              

                              //recoge todos los valores cuando el examen es teorico
                               foreach ($data as $item) {
                                   if ($item["strTipoExamen"] == "Pra" && $item["strTipoExamen"] != "") {
                                       $validarPuntajesPra[] = $item;                    
                                   }
                               } 
                                
                               foreach ($validarPuntajesPra as $item) {  

                                        $totalPuntoPra          += intval($item["intNroItems"]);  
                                        $porFinalPra            += floatval($item["fltNota"]); 
                               }  

                                $this->Cell(100, 4, "NUMERO DE PUNTOS:", 1, 0, 'L');
                                $this->Cell(0, 4,  $totalPuntoPra, 1, 1, 'C');
                                $this->SetFont('Arial', 'B',8);

                            
                                   // mb_convert_encoding("Respetados Señores:     A continuacion se da a conocer el resultado de la evaluacion practicada al señor:", 'ISO-8859-1', 'UTF-8')
                                   // Procesar los elementos filtrados

                                foreach ($validarPuntajesPra as $item) {
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(100, 4,  mb_convert_encoding(strtr(mb_strtoupper($item["strNombreAspecto"]),$mapping),'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
                                    $this->Cell(40, 4, "Puntos   " . $item["intNroItems"], 1, 0, 'L');
                                    $this->Cell(40, 4, "Buenos   " . $item["intNroItemsCorrectos"], 1, 0, 'L');
                                    $this->Cell(0, 4, floatval($item["fltNota"]). "%", 1, 0, 'C');
                                    $this->Ln(4);
                                }

                                $this->Cell(100, 4, "PORCENTAJE MINIMO QUE SE DEBE OBTENER:", 1, 0, 'L');
                                $this->Cell(0, 4, $data[0]["intPuntajeMinimoPractico"]."%", 1, 1, 'C');
                                
                                $this->Cell(100, 4, "PORCENTAJE PARA EL PUNTAJE FINAL:", 1, 0, 'L');
                                $this->Cell(0, 4, ($porFinalPra)."%" , 1, 1, 'C');

                                $this->SetFont('Arial', 'B', 8);
                                $this->Cell(100, 4, "NIVEL DE CONOCIMIENTOS PRACTICOS:", 1, 0, 'L');

                                //ESTADO PRACTICO

                                    if ($porFinalPra >= floatval($data[0]["intPuntajeMinimoPractico"])) {  
                                        $_strApobadoPra = "APROBADO";                        
                                        $this->Cell(0, 4,$_strApobadoPra, 1, 1, 'C');
                                    }else{
                                        $_strApobadoPra = "REPROBADO";   
                                        $this->Cell(0, 4, $_strApobadoPra, 1, 1, 'C');
                                    }

                                //----FIN ESTADO PRACTICO
                                $this->Ln(2);
                                $this->Cell(100, 4, "PUNTAJE MINIMO RECOMENDADO:", 1, 0, 'L');
                                $this->Cell(0, 4, $data[0]['intPuntajeRequeridoCurso']."%" , 1, 1, 'C');

                                $this->Cell(100, 4, "PUNTAJE FINAL OBTENIDO:", 1, 0, 'L');
                                $totalExamenTeoPra = $porFinalTeo + $porFinalPra;
                                $this->Cell(0, 4, ($totalExamenTeoPra) ."%" , 1, 1, 'C');
                                $this->Ln(2);

                                //COMENTARIOS:
                                if ($_strApobadoTeo == "APROBADO" &&  $_strApobadoPra== "APROBADO" ) 
                                {
                                    $this->Cell(23, 4, "COMENTARIOS:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("El nivel general de conocimientos está por encima del límite recomendado.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');                               
                                }

                                else if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "APROBADO" &&  $totalExamenTeoPra > floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(23, 4, "COMENTARIOS:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("El nivel general de conocimientos está por encima del límite recomendado, pero debe mejorar sus conocimientos teóricos.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                
                                }

                                else if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "APROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(23, 4, "COMENTARIOS:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("El nivel general de conocimientos está por debajo del límite recomendado y debe mejorar sus conocimientos teóricos.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                
                                }

                                else if ($_strApobadoTeo == "APROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra > floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(23, 4, "COMENTARIOS:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("El nivel general de conocimientos está por encima del límite recomendado, pero debe mejorar en la conducción del vehículo.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                
                                }

                                else if ($_strApobadoTeo == "APROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(23, 4, "COMENTARIOS:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("El nivel general de conocimientos está por debajo del límite recomendado.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                
                                }                               

                                else if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(23, 4, "COMENTARIOS:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("El nivel general de conocimientos está por debajo del límite recomendado.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                
                                }

                                //OBSERVACIONES:                                                           
                                if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "APROBADO" &&  $totalExamenTeoPra > floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5);  
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(27, 4, "OBSERVACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Presenta deficiencias en la parte teórica.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');                                
                                }

                                else if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "APROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5);  
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(27, 4, "OBSERVACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Presenta deficiencias en la parte teórica.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                }

                                else if ($_strApobadoTeo == "APROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra > floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5);  
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(27, 4, "OBSERVACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Presenta impericia y/o malos hábitos en la conducción del vehículo.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                }

                                else if ($_strApobadoTeo == "APROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5);  
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(27, 4, "OBSERVACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Presenta impericia y/o malos hábitos en la conducción del vehículo.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                }
                               

                                else if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5);  
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(27, 4, "OBSERVACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Presenta pocos conocimientos en general.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                }

                                //RECOMENDACIONES:                               
                                if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "APROBADO" &&  $totalExamenTeoPra > floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5); 
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(32, 4, "RECOMENDACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Estudiar y repetir examen teórico.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');                                
                                }

                                else if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "APROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5); 
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(32, 4, "RECOMENDACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Estudiar y repetir examen teórico.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                }

                                else if ($_strApobadoTeo == "APROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra > floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5); 
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(32, 4, "RECOMENDACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Requiere capacitación y/o entrenamiento adecuado.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                }

                                else if ($_strApobadoTeo == "APROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5); 
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(32, 4, "RECOMENDACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Requiere practica y/o entrenamiento en la conducción del  vehículo antes de repetir examen.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                }
                               

                                else if ($_strApobadoTeo == "REPROBADO" && $_strApobadoPra == "REPROBADO" && $totalExamenTeoPra < floatval($data[0]['intPuntajeRequeridoCurso'])) 
                                {
                                    $this->Ln(5); 
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(32, 4, "RECOMENDACIONES:", 0, 0, 'L');
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(0, 4,mb_convert_encoding("Requiere capacitación teórica y práctica en general antes de repetir examen.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
                                }
                                    $this->marcaAguaFirma();

                                    $this->Ln(8); 
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(100, 4, mb_convert_encoding("Esperamos seguir contando con ustedes.",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');


                                    $this->Ln(8); 
                                    $this->SetFont('Arial', '', 8);
                                    $this->Cell(100, 4, mb_convert_encoding("Atentamente, ",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

                                    // Mensaje si es una copia  
                                    if ($bolCopia) 
                                    {                                        
                                        $this->Ln(40); 
                                        $this->SetFont('Arial', 'B', 8);
                                        $this->Cell(0, 0, mb_convert_encoding("COPIA DEL EXAMEN ORIGINAL ",'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
                                    }


                                    $this->SetY(-30); // Establecer la posición Fija de abjo hacia arriba
                                    $this->SetFont('Arial', 'B', 8);
                                    $this->Cell(100, 4, mb_convert_encoding("Director Centro de Enseñanza Automovilística",'ISO-8859-1', 'UTF-8'), 0, 0, 'L');                   
                            
                            }
                          
                    }

                        $pdf = new MyPDF(); 
                        $responseData = json_decode($response, true);
                        $pdf->GeneratePDF($responseData);
                        $pdf->Output("Cert","I");
                        exit(); 
                }
            }
        }
    } else {
        // Manejar el caso en el que no se reciba el ID
        echo 'Error: ID no proporcionado';
    }
}
?>
