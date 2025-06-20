<?php
require_once 'db_config.php'; // $conn is now available

header('Content-Type: application/json');
// For debugging email or other issues, uncomment these:
 //ini_set('display_errors', 1);
 //error_reporting(E_ALL);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null; // Use $_REQUEST to catch GET or POST for action

// Only try to connect to DB if the action requires it.
// The 'send_guest_confirmation_email' might not strictly need DB if all info is in payload.
$db_actions = ['get_event_data', 'check_guest', 'get_coordinator_selections', 'save_single_guest_selection', 'delete_guest_selection'];
if (in_array($action, $db_actions) && !$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection not established.']);
    exit;
}

if ($conn) { // Set charset if connection exists
    $conn->set_charset("utf8mb4");
}


switch ($action) {
    case 'get_event_data':
        get_event_data($conn);
        break;
    case 'check_guest': // Called by JS as 'check_guest'
        check_guest_exists_in_json($conn);
        break;
    case 'get_coordinator_selections': // Called by JS as 'get_coordinator_selections'
        get_coordinator_selections_from_json($conn);
        break;
    case 'save_single_guest_selection': // Called by JS as 'save_single_guest_selection'
        save_single_guest_selection_in_json($conn);
        break;
    case 'send_guest_confirmation_email':
        send_guest_confirmation_email($conn); // $conn might be used if fetching event name from DB
        break;
    case 'delete_guest_selection':
        delete_guest_selection($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action provided. Action: ' . $action]);
}

if ($conn) {
    $conn->close();
}

// --- FUNCTION DEFINITIONS ---

function get_event_data($conn) {
    $e_id = isset($_GET['e_id']) ? $conn->real_escape_string($_GET['e_id']) : null;
    if (!$e_id) {
        echo json_encode(['success' => false, 'error' => 'Event ID (e_id) is required.']);
        return;
    }

    $event_data = null;
    $dishes_data = [];

    $stmt_event = $conn->prepare("SELECT Datos FROM Events WHERE ID = ?");
    if (!$stmt_event) {
        echo json_encode(['success' => false, 'error' => 'Prepare statement failed (event): ' . $conn->error]);
        return;
    }
    $stmt_event->bind_param("s", $e_id);
    $stmt_event->execute();
    $result_event = $stmt_event->get_result();

    if ($row_event = $result_event->fetch_assoc()) {
        $event_data = json_decode($row_event['Datos'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'error' => 'Failed to parse event JSON: ' . json_last_error_msg()]);
            $stmt_event->close();
            return;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Event not found for ID: ' . htmlspecialchars($e_id)]);
        $stmt_event->close();
        return;
    }
    $stmt_event->close();

    $dish_ids = [];
    if ($event_data) {
        foreach ($event_data as $key => $value) {
            if (preg_match('/^T\d+_O[a-zA-Z0-9]+$/', $key) && !empty($value)) {
                $dish_ids[] = $value;
            }
        }
    }
    $dish_ids = array_unique(array_filter($dish_ids));

    if (!empty($dish_ids)) {
        $placeholders = implode(',', array_fill(0, count($dish_ids), '?'));
        $types = str_repeat('s', count($dish_ids));

        $stmt_dishes = $conn->prepare("SELECT ID, Datos FROM Dishes WHERE ID IN ($placeholders)");
        if (!$stmt_dishes) {
            echo json_encode(['success' => false, 'error' => 'Prepare statement failed (dishes): ' . $conn->error]);
            return;
        }
        $stmt_dishes->bind_param($types, ...$dish_ids);
        $stmt_dishes->execute();
        $result_dishes = $stmt_dishes->get_result();

        while ($row_dish = $result_dishes->fetch_assoc()) {
            $dish_json_data = json_decode($row_dish['Datos'], true);
             if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Failed to parse dish JSON for ID ' . $row_dish['ID'] . ': ' . json_last_error_msg());
                 $dishes_data[$row_dish['ID']] = ['ID' => $row_dish['ID'], 'NAME' => 'Error Parsing Dish Data', 'DESCRIPCION' => ''];
            } else {
                $dishes_data[$row_dish['ID']] = $dish_json_data;
            }
        }
        $stmt_dishes->close();
    }
    echo json_encode(['success' => true, 'event' => $event_data, 'dishes' => $dishes_data]);
}


function check_guest_exists_in_json($conn) {
    $event_id_param = isset($_GET['e_id']) ? $conn->real_escape_string($_GET['e_id']) : null;
    $guest_name_param = isset($_GET['name']) ? $conn->real_escape_string($_GET['name']) : null;

    if (!$event_id_param || !$guest_name_param) {
        echo json_encode(['success' => false, 'error' => 'Event ID and Guest Name parameters are required for check.']);
        return;
    }

    $stmt = $conn->prepare(
        "SELECT COUNT(*) as count FROM SELECTIONS WHERE " .
        "JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.E_ID')) = ? AND " .
        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.NAME'))) = LOWER(?)"
    );
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare statement failed (check_guest_in_json): ' . $conn->error]);
        return;
    }
    $stmt->bind_param("ss", $event_id_param, $guest_name_param); // guest_name_param will be lowercased by SQL
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    echo json_encode(['success' => true, 'exists' => ($row && $row['count'] > 0)]);
}

function get_coordinator_selections_from_json($conn) {
    $event_id_param = isset($_GET['e_id']) ? $conn->real_escape_string($_GET['e_id']) : null;
    $coordinator_email_param = isset($_GET['coordinator_email']) ? $conn->real_escape_string($_GET['coordinator_email']) : null;

    if (!$event_id_param || !$coordinator_email_param) {
        echo json_encode(['success' => false, 'error' => 'Event ID and Coordinator Email parameters are required to fetch selections.']);
        return;
    }

    $stmt = $conn->prepare("SELECT Datos FROM SELECTIONS WHERE JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.E_ID')) = ? AND JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.USER')) = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare statement failed (get_coordinator_selections_from_json): ' . $conn->error]);
        return;
    }
    $stmt->bind_param("ss", $event_id_param, $coordinator_email_param);
    $stmt->execute();
    $result = $stmt->get_result();

    $selections_array = [];
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $decoded_datos = json_decode($row['Datos'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $selections_array[] = $decoded_datos;
            } else {
                error_log("Malformed JSON in SELECTIONS table. E_ID from JSON: $event_id_param, USER from JSON: $coordinator_email_param. Data: " . $row['Datos']);
            }
        }
    } else {
         echo json_encode(['success' => false, 'error' => 'Query execution failed (get_coordinator_selections_from_json): ' . $conn->error]);
         $stmt->close();
         return;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'selections' => $selections_array]);
}

function save_single_guest_selection_in_json($conn) {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    if (json_last_error() !== JSON_ERROR_NONE || !$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON payload received: ' . json_last_error_msg()]);
        return;
    }

    $event_id_from_json = $input['E_ID'];
    //$event_id_from_json = isset($input['E_ID']) ? $conn->real_escape_string($input['E_ID']) : null;
    $guest_name_from_json = $input['NAME'];
    //$guest_name_from_json = isset($input['NAME']) ? $conn->real_escape_string($input['NAME']) : null;

    if (!$event_id_from_json || !$guest_name_from_json  ) {
        echo json_encode(['success' => false, 'message' => 'Required fields (E_ID, NAME, USER, GUEST_EMAIL, SELECTED) are missing in the JSON payload.']);
        return;
    }

    $datos_json_to_store = json_encode($input);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Failed to re-encode JSON for database storage: ' . json_last_error_msg()]);
        return;
    }

    $stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM SELECTIONS WHERE JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.E_ID')) = ? AND JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.NAME')) = ?");
    if (!$stmt_check) {
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed (check for save in json): ' . $conn->error]);
        return;
    }
    $stmt_check->bind_param("ss", $event_id_from_json, $guest_name_from_json);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $guest_exists = ($row_check && $row_check['count'] > 0);
    $stmt_check->close();

    // Determine if SELECTIONS table has a separate E_ID column.
    // For this example, we assume IT DOES NOT, and E_ID is only in the JSON.
    // If it *did* have a separate E_ID column, you'd add it to INSERT and WHERE clauses.

    if ($guest_exists) {
        // If your SELECTIONS table has a TS column:
        $stmt_update = $conn->prepare(
            "UPDATE SELECTIONS SET Datos = ?, TS = CURRENT_TIMESTAMP WHERE " .
            "JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.E_ID')) = ? AND " .
            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.NAME'))) = LOWER(?)"
        );
        // If no TS column:
        // $stmt_update = $conn->prepare("UPDATE SELECTIONS SET Datos = ? WHERE JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.E_ID')) = ? AND JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.NAME')) = ?");
        if (!$stmt_update) {
            echo json_encode(['success' => false, 'message' => 'Prepare statement failed (update in json): ' . $conn->error]);
            return;
        }
        $stmt_update->bind_param("sss", $datos_json_to_store, $event_id_from_json, $guest_name_from_json);
        if ($stmt_update->execute()) {
            if ($stmt_update->affected_rows > 0) {
                 echo json_encode(['success' => true, 'message' => 'Selection updated successfully for ' . htmlspecialchars($guest_name_from_json)]);
            } else {
                 echo json_encode(['success' => true, 'message' => 'Selection processed for ' . htmlspecialchars($guest_name_from_json) . '. No changes detected or record not found by exact JSON match for update.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update selection: ' . $stmt_update->error]);
        }
        $stmt_update->close();
    } else {
        // If your SELECTIONS table has a TS column:
        $stmt_insert = $conn->prepare("INSERT INTO SELECTIONS (Datos, TS) VALUES (?, CURRENT_TIMESTAMP)");
        // If no TS column:
        // $stmt_insert = $conn->prepare("INSERT INTO SELECTIONS (Datos) VALUES (?)");

        if (!$stmt_insert) {
            echo json_encode(['success' => false, 'message' => 'Prepare statement failed (insert in json): ' . $conn->error]);
            return;
        }
        $stmt_insert->bind_param("s", $datos_json_to_store);
        if ($stmt_insert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Selection saved successfully for ' . htmlspecialchars($guest_name_from_json)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save selection: ' . $stmt_insert->error]);
        }
        $stmt_insert->close();
    }
}

function delete_guest_selection($conn) {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    if (json_last_error() !== JSON_ERROR_NONE || !$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON payload for delete operation: ' . json_last_error_msg()]);
        return;
    }

    // Use direct values from JSON payload, as they will be used in a prepared statement.
    // real_escape_string is not necessary here and was inconsistent with save_single_guest_selection.
    $event_id_from_json = isset($input['e_id']) ? $input['e_id'] : null;
    $guest_name_from_json = isset($input['name']) ? $input['name'] : null;

    if (!$event_id_from_json || !$guest_name_from_json) {
        echo json_encode(['success' => false, 'message' => 'Required fields (e_id, name) are missing in the JSON payload for delete.']);
        return;
    }

    // Delete from SELECTIONS table where E_ID and NAME in JSON Datos match
    $sql_query = "DELETE FROM SELECTIONS WHERE " .
        "JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.E_ID')) = ? AND " .
        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.NAME'))) = LOWER(?)";

    $stmt_delete = $conn->prepare($sql_query);

    if (!$stmt_delete) {
        $error_message = 'Prepare statement failed (delete selection): ' . $conn->error . '. Query: ' . $sql_query;
        error_log($error_message); // Log to server error log
        echo json_encode(['success' => false, 'message' => $error_message]);
        return;
    }

    $stmt_delete->bind_param("ss", $event_id_from_json, $guest_name_from_json); // guest_name_from_json will be lowercased by SQL
    
    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Guest selection deleted successfully from database.']);
        } else {
            // It's useful to know what was attempted even if no rows were affected
            $debug_message = 'No matching guest selection found in database to delete, or already deleted. Attempted for E_ID: ' . htmlspecialchars($event_id_from_json) . ', Name: ' . htmlspecialchars($guest_name_from_json);
            echo json_encode(['success' => true, 'message' => $debug_message]);
        }
    } else {
        $error_message = 'Failed to delete guest selection from database: ' . $stmt_delete->error . '. Query: ' . $sql_query . '. Params: [E_ID: ' . htmlspecialchars($event_id_from_json) . ', Name: ' . htmlspecialchars($guest_name_from_json) . ']';
        error_log($error_message); // Log to server error log
        echo json_encode(['success' => false, 'message' => $error_message]);
    }
    $stmt_delete->close();
}

function send_guest_confirmation_email($conn_optional) { // $conn can be null if not needed
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, TRUE);

    if (json_last_error() !== JSON_ERROR_NONE || !$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON payload for email.']);
        return;
    }

    $guest_email = isset($data['guestEmail']) ? filter_var($data['guestEmail'], FILTER_VALIDATE_EMAIL) : null;
    $guest_name = isset($data['guestName']) ? htmlspecialchars($data['guestName']) : 'Guest';
    $event_id = isset($data['eventId']) ? htmlspecialchars($data['eventId']) : null;
    $event_name_from_payload = isset($data['eventName']) ? htmlspecialchars($data['eventName']) : 'the Event';
    $language = isset($data['language']) && in_array($data['language'], ['en', 'es']) ? $data['language'] : 'en';
    $selections_summary_html = isset($data['summaryHtml']) ? $data['summaryHtml'] : '<p>No selections provided.</p>';

    if (!$guest_email || !$event_id) {
        echo json_encode(['success' => false, 'message' => 'Guest email and Event ID are required for sending confirmation.']);
        return;
    }

    // Optionally, fetch fresh event name from DB if $conn_optional is available and $event_name_from_payload seems generic
    // This part is optional and depends on if you trust eventName from payload or want to ensure it's from DB
    $event_name_to_use = $event_name_from_payload;
    // if ($conn_optional && $event_id && $event_name_from_payload === 'the Event') {
    //     $stmt_event_name = $conn_optional->prepare("SELECT Datos FROM Events WHERE ID = ?");
    //     if ($stmt_event_name) {
    //         $stmt_event_name->bind_param("s", $event_id);
    //         $stmt_event_name->execute();
    //         $result_event_name = $stmt_event_name->get_result();
    //         if ($row_event_name = $result_event_name->fetch_assoc()) {
    //             $event_details = json_decode($row_event_name['Datos'], true);
    //             if ($event_details && isset($event_details['NAME'])) {
    //                 $event_name_to_use = htmlspecialchars($event_details['NAME']);
    //             }
    //         }
    //         $stmt_event_name->close();
    //     }
    // }


    $your_base_url = "https://salypimientabanquetes.com/IO3/"; // ** CHANGE THIS **
    $edit_link = $your_base_url . "?e_id=" . urlencode($event_id);
    // For a more direct edit link, you might pass guest identifiers (name/email) or a unique selection token
    // $edit_link .= "&guest_to_edit=" . urlencode($guest_name); // Simple example

    $email_content_texts = [
        'en' => [
            'subject' => "Your Menu Selections for " . $event_name_to_use,
            'greeting' => "Dear " . $guest_name . ",",
            'intro' => "Thank you for submitting your menu selections for " . $event_name_to_use . ". Here is a summary of your choices:",
            'edit_prompt' => "If you need to make changes, please visit the link below:",
            'edit_button_text' => "Edit My Selections",
            'closing' => "Sincerely,",
            'team_name' => "The Event Team"
        ],
        'es' => [
            'subject' => "Tus Selecciones de Menú para " . $event_name_to_use,
            'greeting' => "Estimado/a " . $guest_name . ",",
            'intro' => "Gracias por enviar tus selecciones de menú para " . $event_name_to_use . ". Aquí tienes un resumen de tus elecciones:",
            'edit_prompt' => "Si necesitas realizar cambios, por favor visita el siguiente enlace:",
            'edit_button_text' => "Editar Mis Selecciones",
            'closing' => "Atentamente,",
            'team_name' => "Sal y Pimienta"
        ]
    ];

    $current_texts = $email_content_texts[$language];

    $message = "
    <html><head><title>{$current_texts['subject']}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin:0; padding:0; background-color:#f4f4f4; }
        .email-container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #ffffff; }
        .email-header { padding-bottom:10px; margin-bottom:20px; border-bottom:1px solid #eee; text-align:center; }
        .email-header h2 { margin:0; color: #4a5c8e; }
        .button { display: inline-block; padding: 10px 20px; margin: 20px 0; background-color: #5e74b2; color: #ffffff !important; text-decoration: none; border-radius: 4px; font-weight:bold; }
        .button:hover { background-color: #4a5c8e; }
        .summary-table { width:100%; border-collapse: collapse; margin-top:15px; margin-bottom:15px; }
        .summary-table th, .summary-table td { text-align: left; padding: 10px; border-bottom: 1px solid #eee; }
        .summary-table th { background-color: #f9f9f9; font-weight:bold; }
        .email-footer { margin-top:25px; font-size:0.9em; text-align:center; color:#777; }
    </style>
    </head><body>
      <div class='email-container'>
        <div class='email-header'><h2>{$current_texts['subject']}</h2></div>
        <p>{$current_texts['greeting']}</p>
        <p>{$current_texts['intro']}</p>
        {$selections_summary_html}
        <p>{$current_texts['edit_prompt']}</p>
        <div style='text-align:center;'><a href='{$edit_link}' class='button'>{$current_texts['edit_button_text']}</a></div>
        <p style='margin-top: 25px;'>{$current_texts['closing']}<br>{$current_texts['team_name']}</p>
      </div>
      <div class='email-footer'><p>© " . date("Y") . " " . htmlspecialchars($event_name_to_use) . " Organizers</p></div>
    </body></html>";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $from_email = "noreply@yourdomain.com"; // ** CHANGE THIS **
    //$from_name = htmlspecialchars($event_name_to_use) . " Team";
    $from_name = "SalyPimienta";
    $headers .= "From: \"{$from_name}\" <{$from_email}>" . "\r\n";

    if (mail($guest_email, $current_texts['subject'], $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Confirmation email sent successfully to ' . $guest_email]);
    } else {
        $lastError = error_get_last();
        $errorMessage = 'Failed to send confirmation email using mail(). Check server logs and mail configuration.';
        if ($lastError && isset($lastError['message'])) {
            $errorMessage .= ' PHP Error: ' . $lastError['message'];
        }
        error_log("PHP mail() failed for $guest_email: " . $errorMessage);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    }
}
?>