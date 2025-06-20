<?php
	
	

	
setlocale(LC_ALL, 'en_US.UTF-8');
header('Content-type: text/javascript; charset=utf-8');
	
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
 
 
 

include_once($_SERVER['DOCUMENT_ROOT'] . '/PHP/MYF1.php');


$entityBody = file_get_contents('php://input');
$BODY_OB = json_decode($entityBody, true);		//$BODY_OB = json_decode($BODY_EN, true);
$DATA = $BODY_OB;   
$DATA_STR = json_encode($DATA , true);

function reduceImageSize($base64Image, $maxSize = 100000) {
    // Extract the image type from the base64 string
    $imageParts = explode(";base64,", $base64Image);
    $imageType = str_replace('data:image/', '', $imageParts[0]);
    $imageType = str_replace(';', '', $imageType);
    
    // Decode the base64 string
    $imageData = base64_decode($imageParts[1]);
    
    // Create an image resource from the decoded data
    $image = imagecreatefromstring($imageData);
    
    if ($image === false) {
        return false; // Return false if image creation fails
    }

    $quality = 100; // Start with the highest quality
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Loop to reduce the image size
    do {
        // Create a new true color image with reduced dimensions
        $newWidth = (int) ($width * 0.9); // Reduce width by 10%
        $newHeight = (int) ($height * 0.9); // Reduce height by 10%
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Resample the image
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save the image to a temporary variable
        ob_start();
        imagejpeg($newImage, null, $quality); // Save as JPEG with current quality
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Calculate the size of the image data
        $imageSize = strlen($imageData);
        
        // Update width and height for the next iteration
        $width = $newWidth;
        $height = $newHeight;

        // Reduce quality for the next iteration if necessary
        if ($imageSize > $maxSize) {
            $quality -= 5; // Decrease quality by 5
        }
        
        // Destroy the new image resource to free memory
        imagedestroy($newImage);
        
    } while ($imageSize > $maxSize && $quality > 0);

    // Free the original image resource
    imagedestroy($image);

    // Encode the final image data back to base64
    $finalBase64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);
    
    return $finalBase64Image;
}


		
function FUNCION($DATA){
	

	/// CURRENT DATA 
	
			$currentQuery = sprintf("SELECT * FROM u124132715_SYP.%s WHERE id = '%s'", $DATA['TABLA'], $DATA['ID']);
			$currentData = SQL_2_OBJ_V2($currentQuery);
			$currentData = $currentData['PL'][0];


	/// NEW DATA 
	
	
			$newData = json_decode($DATA['Datos'], true); // Assuming $DATA['Datos'] is a valid JSON string

			// Update the current array with new values where keys match
			
			foreach ($newData as $key => $value) {
			

					
					$inicia_con = substr($value, 0, 10);
					
					switch($inicia_con){
						
						case 'data:image':
							
							$currentData[$key] = $value; 
							
							$value = reduceImageSize($value);
							
							$METADATA = json_encode([ 
								"TABLE"		=>	$DATA['TABLE'],
								"USER_ID"		=>	$DATA['ID'],
								"ELEMENT_NAME"	=>	$key
								
							] , true );
							
							
							$q = sprintf("SELECT id, TS, Datos FROM u124132715_SYP.%s WHERE JSON_EXTRACT(Datos, '$.USER_ID') = '%s' ", 'FILES', $DATA['ID']);
							$R1 = SQL_2_OBJ_V2($q); // Execute the select query
							$ENCONTRADOS = $R1['QRY']['ROWS'];

							if ($ENCONTRADOS == 0) {
								// Ensure $value is defined before using it
								$q = sprintf("INSERT INTO u124132715_SYP.%s (Datos, file_blob) VALUES ('%s', '%s') ", 'FILES', $METADATA, $value);
							} else {
								// Corrected SQL query without the extra parenthesis
								$q = sprintf("UPDATE u124132715_SYP.%s SET Datos = '%s', file_blob = '%s' WHERE JSON_EXTRACT(Datos, '$.USER_ID') = '%s' ", 'FILES', $METADATA, $value, $DATA['ID']);
							}

							// Execute the query and check for errors
							$R1 = SQL_2_OBJ_V2($q); // Execute the update
							
							if (!$R1) {
								// Handle error here, e.g., log it or throw an exception
								error_log("SQL Error: " . mysqli_error($connection)); // Assuming $connection is your DB connection
							}

							//$RESP['IMG'] = $R1;
							
							
						break;
						
						
						default:
						
							$currentData[$key] = $value; 
						
						
					}
					


				
			}
			
			//print_r($currentData);die;
			
		

	/// UPDATED DATA 
	
			// Encode the updated array back to JSON
			$updatedJson = json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			



	/// UPDATE THE DATABASE 
	
	
			// Update the database with the new JSON string
			$updateQuery = sprintf("UPDATE u124132715_SYP.%s SET Datos = '%s' WHERE id = '%s'", $DATA['TABLA'], $updatedJson, $DATA['ID']);
			$R1 = SQL_2_OBJ_V2($updateQuery); // Execute the update

			$RESP['R1'] = $R1; // Store the result of the update
			$RESP['DATOS'] = $R1; // Optionally return the updated data
	
	return $RESP;



}


$RESP = FUNCION($DATA);

//return $RESP;

echo json_encode($RESP, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 
return;


?>