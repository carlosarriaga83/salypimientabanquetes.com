<?php
// File: c:\Users\MX\GitHub\salypimientabanquetes.com\IO3\image_reduction.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db_config.php'; // For $conn

// --- Configuration ---
define('MAX_IMAGE_SIZE_BYTES', 64 * 1024); // 100 KB Target for output JPEG
define('JPEG_INITIAL_QUALITY', 85); // Initial quality for JPEG conversion
define('JPEG_MIN_QUALITY', 10);     // Minimum quality to try for JPEG
define('JPEG_QUALITY_STEP', 5);    // Step to reduce quality by
define('PNG_COMPRESSION_LEVEL', 9); // 0 (no compression) to 9 (max compression)
define('MAX_IMAGE_WIDTH', 800); // Max width in pixels
define('MAX_IMAGE_HEIGHT', 600); // Max height in pixels


/**
 * Reduces file size of all dish images (DISH_PIC in Datos JSON) found in the Dishes table
 * to be under MAX_IMAGE_SIZE_BYTES and updates their DISH_PIC data URLs.
 *
 * @param mysqli $conn The database connection object.
 * @return array An array containing 'processed', 'skipped', and 'errors' messages.
 */
function reduce_dish_image_sizes(&$conn) {
    $report = [
        'processed' => [],
        'skipped' => [],
        'errors' => []
    ];

    $sql = "SELECT ID, Datos FROM Dishes";
    $result = $conn->query($sql);

    if (!$result) {
        $report['errors'][] = "Failed to query Dishes table: " . $conn->error;
        return $report;
    }

    if ($result->num_rows === 0) {
        $report['skipped'][] = "No dishes found in the Dishes table.";
        return $report;
    }

    while ($row = $result->fetch_assoc()) {
        $dish_id = $row['ID'];
        $datos_json = $row['Datos'];
        $datos = json_decode($datos_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $report['errors'][] = "Dish ID {$dish_id}: Failed to decode Datos JSON - " . json_last_error_msg();
            continue;
        }

        if (!isset($datos['DISH_PIC']) || empty(trim($datos['DISH_PIC']))) {
            $report['skipped'][] = "Dish ID {$dish_id}: No DISH_PIC found or is empty.";
            continue;
        }

        $original_data_url = trim($datos['DISH_PIC']);

        // Basic check for data URL format
        if (strpos($original_data_url, 'data:image/') !== 0 || strpos($original_data_url, ';base64,') === false) {
            $report['skipped'][] = "Dish ID {$dish_id}: DISH_PIC is not a valid base64 data URL. Value: " . substr($original_data_url, 0, 50) . "...";
            continue;
        }

        // Extract image type and base64 data
        list($type_header, $base64_data) = explode(';base64,', $original_data_url);
        $image_type_mime = str_replace('data:', '', $type_header); // e.g., "image/png"
        // $image_format = str_replace('image/', '', $image_type_mime); // e.g., "png", "jpeg"
        // Since we know all images are PNG, we can assume the format.

        if (empty($base64_data)) {
            $report['errors'][] = "Dish ID {$dish_id}: Empty base64 data in DISH_PIC.";
            continue;
        }

        $image_binary_data = base64_decode($base64_data);
        if ($image_binary_data === false) {
            $report['errors'][] = "Dish ID {$dish_id}: Failed to decode base64 data.";
            continue;
        }

        $source_image = @imagecreatefromstring($image_binary_data);

        if (!$source_image) {
            $report['errors'][] = "Dish ID {$dish_id}: Failed to create image resource from base64 data. Mime type: {$image_type_mime}.";
            continue;
        }

        // Attempt to set resolution to 72 DPI
        if (function_exists('imageresolution')) {
            if (!@imageresolution($source_image, 72, 72)) {
                // Optionally log this, but don't stop processing if it fails for some formats
                // error_log("Dish ID {$dish_id}: Notice - Failed to set resolution to 72 DPI for PNG image.");
            }
        }
        
        // Resize image if dimensions exceed MAX_IMAGE_WIDTH or MAX_IMAGE_HEIGHT
        $current_width = imagesx($source_image);
        $current_height = imagesy($source_image);

        if ($current_width > MAX_IMAGE_WIDTH || $current_height > MAX_IMAGE_HEIGHT) {
            $ratio = $current_width / $current_height;
            $new_width = MAX_IMAGE_WIDTH;
            $new_height = MAX_IMAGE_WIDTH / $ratio;

            if ($new_height > MAX_IMAGE_HEIGHT) {
                $new_height = MAX_IMAGE_HEIGHT;
                $new_width = MAX_IMAGE_HEIGHT * $ratio;
            }

            $resized_image = imagecreatetruecolor((int)$new_width, (int)$new_height);
            if ($image_type_mime === 'image/png') { // Preserve transparency for PNGs
                imagealphablending($resized_image, false);
                imagesavealpha($resized_image, true);
                $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
                imagefilledrectangle($resized_image, 0, 0, (int)$new_width, (int)$new_height, $transparent);
            }

            if (imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, (int)$new_width, (int)$new_height, $current_width, $current_height)) {
                imagedestroy($source_image);
                $source_image = $resized_image;
                $report['processed'][] = "Dish ID {$dish_id}: Image resized from {$current_width}x{$current_height} to ".(int)$new_width."x".(int)$new_height.".";
            } else {
                $report['errors'][] = "Dish ID {$dish_id}: Failed to resize image. Proceeding with original dimensions.";
                // No need to destroy $resized_image here as it wasn't assigned to $source_image
            }
        }

        $processed_image_binary_data = null;
        $final_image_size_message_part = ""; // To store how the image was processed for the report

        // Convert original PNG to JPG, iterating quality to meet size target.
        // The $source_image is the (potentially resized) PNG.
        // We need to draw it onto a new truecolor canvas with a white background for JPG conversion.
        
        $canvas_for_jpeg = imagecreatetruecolor(imagesx($source_image), imagesy($source_image));
        if (!$canvas_for_jpeg) {
            $report['errors'][] = "Dish ID {$dish_id}: Failed to create canvas for JPEG conversion.";
        } else {
            $white = imagecolorallocate($canvas_for_jpeg, 255, 255, 255);
            imagefill($canvas_for_jpeg, 0, 0, $white);
            imagecopy($canvas_for_jpeg, $source_image, 0, 0, 0, 0, imagesx($source_image), imagesy($source_image));

            $current_quality = JPEG_INITIAL_QUALITY;
            $smallest_jpeg_data_if_oversized = null;
            $smallest_jpeg_size_if_oversized = PHP_INT_MAX;

            while ($current_quality >= JPEG_MIN_QUALITY) {
                ob_start();
                $output_ok = @imagejpeg($canvas_for_jpeg, null, $current_quality);
                $temp_jpg_data = ob_get_clean();

                if ($output_ok && !empty($temp_jpg_data)) {
                    $current_jpg_size = strlen($temp_jpg_data);
                    if ($current_jpg_size <= MAX_IMAGE_SIZE_BYTES) {
                        $processed_image_binary_data = $temp_jpg_data;
                        $final_image_size_message_part = "Converted to JPEG ({$current_jpg_size} bytes) with quality {$current_quality}.";
                        break; // Target met
                    }
                    // Keep track of the smallest oversized version, in case we can't meet the target
                    if ($current_jpg_size < $smallest_jpeg_size_if_oversized) {
                        $smallest_jpeg_data_if_oversized = $temp_jpg_data;
                        $smallest_jpeg_size_if_oversized = $current_jpg_size;
                    }
                }
                $current_quality -= JPEG_QUALITY_STEP;
            }
            imagedestroy($canvas_for_jpeg);

            if (!$processed_image_binary_data) { // Target not met
                if ($smallest_jpeg_data_if_oversized) {
                     $report['errors'][] = "Dish ID {$dish_id}: Could not reduce JPEG to target size. Smallest was {$smallest_jpeg_size_if_oversized} bytes at quality " . ($current_quality + JPEG_QUALITY_STEP) . ". Original not changed.";
                } else {
                    $report['errors'][] = "Dish ID {$dish_id}: Failed to process image as JPEG (no valid output generated).";
                }
            }
        }

        imagedestroy($source_image);

        if ($processed_image_binary_data) {
            $output_image_mime = 'image/jpeg'; // The output is now JPEG

            // Add the main success message to the report using $final_image_size_message_part
            if (!empty($final_image_size_message_part)) {
                 // Check if a resize message was already added, if so, append.
                $existing_processed_idx = -1;
                foreach($report['processed'] as $idx => $msg) {
                    if (strpos($msg, "Dish ID {$dish_id}: Image resized from") === 0) $existing_processed_idx = $idx;
                }
                if ($existing_processed_idx !== -1) $report['processed'][$existing_processed_idx] .= " Then, " . lcfirst($final_image_size_message_part);
                else $report['processed'][] = "Dish ID {$dish_id}: " . $final_image_size_message_part;
            }
            $new_base64_data = base64_encode($processed_image_binary_data);
            $new_data_url = 'data:' . $output_image_mime . ';base64,' . $new_base64_data;

            // Update Datos JSON with the new image data URL
            $datos['DISH_PIC'] = $new_data_url;
            // Optionally add a flag: $datos['DISH_PIC_REDUCED_V1'] = true; // To avoid reprocessing
            $new_datos_json = json_encode($datos);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $report['errors'][] = "Dish ID {$dish_id}: Failed to re-encode Datos JSON after data URL update - " . json_last_error_msg();
                // Note: Image was processed but DB update will be skipped.
            } else {
                // Update database
                $stmt_update = $conn->prepare("UPDATE Dishes SET Datos = ? WHERE ID = ?");
                if (!$stmt_update) {
                    $report['errors'][] = "Dish ID {$dish_id}: Failed to prepare update statement: " . $conn->error;
                } else {
                    $stmt_update->bind_param("ss", $new_datos_json, $dish_id);
                    if ($stmt_update->execute()) {
                        if ($stmt_update->affected_rows > 0) {
                            // DB update confirmed. Message about image processing is already in $report['processed']
                            // $report['processed'][] = "Dish ID {$dish_id}: DB record updated with reduced image."; // Could be redundant
                        } else {
                            $report['skipped'][] = "Dish ID {$dish_id}: DB record not updated (no change or ID not found, though image data URL was processed and met size criteria).";
                        }
                    } else {
                        $report['errors'][] = "Dish ID {$dish_id}: Failed to execute DB update for new data URL: " . $stmt_update->error;
                    }
                    $stmt_update->close();
                }
            }
        } else {
             // Image was not processed to meet size criteria or an error occurred.
             // Error messages for JPEG processing failure or not meeting size target were added above.
             // No additional error message needed here unless there's a specific case not covered.
        }
    }
    $result->free();
    return $report;
}

// --- Main script execution (if run directly) ---

// Ensure script is not accidentally run via web if it's very resource intensive
// For a real production tool, you might add IP restriction or a secret key parameter.
/*
if (php_sapi_name() !== 'cli' && !isset($_GET['confirm_run_via_web_123'])) {
    die("This script is intended for CLI execution or requires explicit confirmation to run via web.");
}
*/

if (!$conn) {
    // Attempt to reconnect if $conn from db_config.php was somehow null or closed.
    // This is a basic fallback; db_config.php should ideally handle robust connection.
    $dbHost = 'localhost'; // Replace with your actual host
    $dbUser = 'u124132715_salyPimienta'; // Replace with your actual username
    $dbPass = 'Pellu8aa1!'; // Replace with your actual password
    $dbName = 'u124132715_salyPimienta'; // Replace with your actual database name
    
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error . ". Check db_config.php and database server credentials.");
    }
    $conn->set_charset("utf8mb4");
}


// Execute the main function
$results = reduce_dish_image_sizes($conn);

// Output the report
// If run via browser, make it a bit more readable. If CLI, plain text is fine.
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Image Size Reduction Report</title>";
    echo "<style>body { font-family: sans-serif; line-height: 1.6; padding: 20px; } 
           h1, h2 { color: #333; } 
           ul { list-style-type: none; padding-left: 0; } 
           li.processed { color: green; } 
           li.skipped { color: orange; } 
           li.error { color: red; font-weight: bold; }
           .report-section { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 5px; background-color: #f9f9f9;}
    </style></head><body>";
    echo "<h1>Image Size Reduction Process Report</h1>";

    if (empty($results['processed']) && empty($results['skipped']) && empty($results['errors'])) {
        echo "<div class='report-section'><h2>Status</h2><p>No dishes found or no images to process.</p></div>";
    } else {
        if (!empty($results['processed'])) {
            echo "<div class='report-section'><h2>Successfully Processed and Updated:</h2><ul>";
            foreach ($results['processed'] as $msg) {
                echo "<li class='processed'>" . htmlspecialchars($msg) . "</li>";
            }
            echo "</ul></div>";
        }
        if (!empty($results['skipped'])) {
            echo "<div class='report-section'><h2>Skipped:</h2><ul>";
            foreach ($results['skipped'] as $msg) {
                echo "<li class='skipped'>" . htmlspecialchars($msg) . "</li>";
            }
            echo "</ul></div>";
        }
        if (!empty($results['errors'])) {
            echo "<div class_section='report-section'><h2>Errors Encountered:</h2><ul>";
            foreach ($results['errors'] as $msg) {
                echo "<li class='error'>" . htmlspecialchars($msg) . "</li>";
            }
            echo "</ul></div>";
        }
    }
    echo "</body></html>";
} else {
    // CLI output
    echo "Image Size Reduction Process Report:\n";
    echo "================================\n";
    if (empty($results['processed']) && empty($results['skipped']) && empty($results['errors'])) {
        echo "No dishes found or no images to process.\n";
    } else {
        if (!empty($results['processed'])) {
            echo "\nSuccessfully Processed and Updated:\n";
            foreach ($results['processed'] as $msg) {
                echo "- " . $msg . "\n";
            }
        }
        if (!empty($results['skipped'])) {
            echo "\nSkipped:\n";
            foreach ($results['skipped'] as $msg) {
                echo "- " . $msg . "\n";
            }
        }
        if (!empty($results['errors'])) {
            echo "\nErrors Encountered:\n";
            foreach ($results['errors'] as $msg) {
                echo "- " . $msg . "\n";
            }
        }
    }
}

if ($conn) {
    $conn->close();
}

?>
