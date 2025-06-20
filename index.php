<?php
    // This should be the very first thing in your PHP file, before any HTML output.
    // $event_id = isset($_GET['e_id']) ? htmlspecialchars($_GET['e_id']) : null;
    // A more robust way to handle e_id, ensuring it's somewhat sane (e.g., alphanumeric)
    $event_id_raw = isset($_GET['e_id']) ? $_GET['e_id'] : null;
    if ($event_id_raw && preg_match('/^[a-zA-Z0-9_-]+$/', $event_id_raw)) {
        $event_id = htmlspecialchars($event_id_raw);
    } else if ($event_id_raw) { // If e_id exists but has invalid characters
        http_response_code(400); // Bad Request
        die("Error: Invalid Event ID format.");
    } else { // If e_id is not set at all
        http_response_code(400);
        die("Error: Event ID (e_id) is required in the URL.");
    }
?> 
<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="pageTitle">Event Menu Selection</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Removed Playfair Display, Montserrat, and Sacramento fonts as they were for the old intro -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <link rel="stylesheet" href="style.css?a=<?php echo time(); ?>"> 
    
</head>
<body>

    <!-- Language Toggle Button -->
    <button id="languageToggleButton" class="nav-button" title="Toggle Language" style="position: fixed; top: 15px; left: 15px; z-index: 10002; padding: 8px 12px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
        <span id="languageToggleText">ES</span>
    </button>

    <!-- Theme Toggle Button (Optional) -->
    <button id="themeToggleButton" class="nav-button" title="Toggle Theme" style="position: fixed; top: 15px; right: 15px; z-index: 10002; padding: 8px 12px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
        <i class="fas fa-adjust"></i> 
    </button>

    <!-- Main Application (Initially hidden or opacity 0) -->
    <div id="appWrapper" style="opacity: 0; transition: opacity 0.5s ease-in-out;">
        <div class="main-container">
            <!-- Stage 1: Welcome Screen -->
            <div id="stage1" class="stage"> 
                <h2 data-translate="welcomeStageTitle">Welcome</h2>
                <div class="welcome-message-container">
                    <p data-translate="welcomeMessageText">Welcome to the menu selection for:</p>
                    <h3 id="welcomeEventName" class="welcome-event-title"></h3>
                    <p id="welcomeEventDate" class="welcome-event-date"></p>
                </div>
                <!-- Message for date check will be inserted here by JS -->
                <button id="toSetupStageButton" class="nav-button next-button" data-translate="continueButton" style="margin-top: 15px; width: 100%;">Continue</button>
            </div>

            <!-- Stage 2: Initial Setup & Language Selection (Previously Stage 1) -->
            <div id="stage2" class="stage"> 
                <h2 data-translate="setupStageTitle">Initial Setup</h2>
                <!-- Language select dropdown removed, now a toggle button -->
                <div class="form-group">
                    <label for="coordinatorEmail" data-translate="coordinatorEmailLabel">Your Email:</label>
                    <input type="email" id="coordinatorEmail" placeholder="youremail@example.com">
                    <small id="coordinatorEmailError" class="error-message"></small>
                </div>
                
                <button id="toGuestInfoStageButton" class="nav-button next-button" data-translate="nextButton">Next</button>
            </div>

            <!-- Stage 3: Guest Name and Email (Previously Stage 2) -->
            <div id="stage3" class="stage">
                <h2 data-translate="stage2Title">Guest Information</h2>
                <div id="stage2ChoiceContainer" style="display:none;">
                    <p data-translate="stage2ChoicePrompt">You have existing guest selections. What would you like to do?</p>
                    <button id="finalizeAndGoToSummaryButton" class="nav-button" data-translate="finalizeAndSummaryButton">View Summary</button>
                    <button id="showAddGuestFormButton" class="nav-button" data-translate="addAnotherGuestButton" style="margin-top:10px;">Add Another Guest</button>
                </div>
                <div id="stage2InputForm">
                    <p data-translate="stage2Instructions">Enter guest details. Email is optional (defaults to coordinator's if blank).</p>
                    <div class="form-group">
                        <label for="guestName" data-translate="guestNameLabel">Guest Name:</label>
                        <input type="text" id="guestName" placeholder="e.g., John Doe">
                        <small id="guestNameError" class="error-message"></small>
                    </div>
                    <div class="form-group">
                        <label for="guestEmail" data-translate="guestEmailLabelOptional">Guest Email (Optional):</label>
                        <input type="email" id="guestEmail" placeholder="guest@example.com (optional)">
                        <small id="guestEmailError" class="error-message"></small>
                    </div>                    
                </div>
                <div class="button-group" style="margin-top: 20px;">
                    <button id="backToSetupStageButton" class="nav-button back-button" data-translate="backButton">Back</button>
                    <button id="selectMenuForGuestButton" class="nav-button next-button" data-translate="selectMenuButton" >Select Menu for this Guest</button>
                </div>
            </div>

            <!-- Stage 4: Guest Dietary Restrictions (Previously Stage 3) -->  
            <div id="stage4" class="stage">
                <h2 data-translate="stage3Title">Dietary Restrictions</h2>
                <p><span data-translate="guestInfoForDietary">Guest:</span> <strong id="currentGuestNameForDietary"></strong></p>
                <div class="form-group">
                    <label for="allergies" data-translate="allergiesLabel">Allergies or other restrictions (optional):</label>
                    <input type="text" id="allergies" placeholder="e.g., Peanuts, Gluten-free">
                </div>
                <fieldset class="form-group">
                    <legend data-translate="dietaryRestrictionsLabel">Dietary Preference:</legend>
                    <div class="radio-option">
                        <input type="radio" id="dietNone" name="dietaryType" value="" checked>
                        <label for="dietNone" data-translate="noneOption">None</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="dietVegetarian" name="dietaryType" value="OPT_VEGETARIANO">
                        <label for="dietVegetarian" data-translate="vegOption">Vegetarian</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="dietVegan" name="dietaryType" value="OPT_VEGANO">
                        <label for="dietVegan" data-translate="veganOption">Vegan</label>
                    </div>
                </fieldset>
                <div class="button-group">
                    <button id="backToGuestInfoStageButton" class="nav-button back-button" data-translate="backButton">Back</button>
                    <button id="toMenuSelectionStageButton" class="nav-button next-button" data-translate="nextButton">Next</button>
                </div>
            </div>

            <!-- Stage 5: Menu Selection (Previously Stage 4) -->
            <div id="stage5" class="stage">
                <h2 id="menuSelectionTitle" data-translate="menuSelectionBaseTitle">Menu Selection for <span id="currentGuestNameForMenu"></span></h2>
                <p><span data-translate="currentGuestEmailLabel">Email:</span> <span id="currentGuestEmailForMenu"></span></p>
                <div id="menuCoursesContainer"></div>
                <small id="menuSelectionError" class="error-message"></small>
                <div class="button-group">
                    <button id="backToDietaryStageButton" class="nav-button back-button" data-translate="backButton">Back</button>
                    <button id="saveSelectionsButton" class="nav-button next-button" data-translate="saveSelectionsButton">Save Selections</button>
                </div>
            </div>

            <!-- Stage 6: Summary (Previously Stage 5) -->
            <div id="stage6" class="stage">
                <h2 data-translate="summaryTitle">Selections Summary</h2>
                <div id="summaryListContainer"></div>
                <div id="emailSection" style="margin-top: 20px;">
                    <button id="prepareEmailButton" class="nav-button" style="display: none;" data-translate="prepareEmailButton">Prepare Summary Email (All Guests)</button>
                    <div id="emailPreparedMessage" class="success-message" style="display:none;"></div>
                    <p id="emailServerNote" style="font-size: 0.9em; margin-top: 10px; display:none;" data-translate="emailServerNote">Note: Actual email sending requires server-side setup. This will prepare a 'mailto:' link.</p>
                    <div id="mailtoLinkContainer" style="margin-top:10px;"></div>
                </div>
                <div class="button-group" style="margin-top: 20px;">
                    <button id="backToGuestInfoFromSummaryButton" class="nav-button back-button" data-translate="addEditGuestButton">Add/Edit Another Guest</button>
                    <button id="submitAllButton" class="nav-button next-button submit-final-button" data-translate="submitAllButton">Confirm & Submit All Selections</button>
                </div>
                <button id="startOverButton" class="nav-button danger-button" data-translate="startOverButton" style="margin-top: 15px;">Start Over</button>
            </div>

            <!-- Stage 7: Thank You / Confirmation -->
            <div id="stage7" class="stage">
                <h2 data-translate="thankYouStageTitle">Thank You!</h2>
                <div class="confirmation-message-container" style="text-align: center; margin-bottom: 25px;">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: #4CAF50; margin-bottom: 15px;"></i>
                    <p data-translate="thankYouMessageText" style="font-size: 1.1em;">Your selections have been successfully submitted.</p>
                    <p data-translate="calendarPromptText" style="margin-top: 20px;">You can add the event to your calendar:</p>
                </div>
                <div class="button-group welcome-actions" style="margin-top: 10px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <button id="addToGoogleCalendarButton" class="nav-button calendar-button" data-translate="addToGoogleCalendar" style="width: 80%; max-width: 300px;">Add to Google Calendar</button>
                    <button id="addToAppleCalendarButton" class="nav-button calendar-button" data-translate="addToAppleCalendar" style="width: 80%; max-width: 300px;">Add to Apple Calendar</button>
                </div>
                <div style="margin-top: 30px; text-align: center; font-size: 0.95em;">
                    <p>
                        <span data-translate="eventReminderTextPart1">We look forward to seeing you at</span>
                        <strong id="thankYouEventName">[Event Name]</strong>
                        <span data-translate="eventReminderTextPart2">on</span>
                        <strong id="thankYouEventDate">[Event Date]</strong>!
                    </p>
                </div>
                <button id="finishAndExitButton" class="nav-button" data-translate="finishButton" style="margin-top: 30px; width: 100%; background-color: #5cb85c; display: none;">Finish</button>
            </div>

        </div>
        <!-- Progress bar moved after main-container -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>
    </div> 


    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" class="modal" style="display:none;">
        <span class="close-modal-button" data-translate="closeModal">×</span>
        <img class="modal-content" id="modalImage" alt="Dish Preview">
        <div id="modalCaption" class="modal-caption" data-translate="dishNamePlaceholder">Dish Name</div>
    </div>

    <!-- Loading Spinner Overlay (for API calls) -->
    <div id="loadingOverlay" style="display:none;">
        <div class="spinner"></div>
        <p data-translate="loadingDataText">Loading event data...</p>
    </div>

    <script>
        const EVENT_ID = "<?php echo $event_id; ?>";
    </script>
    <script src="script.js?v=1.0.0" defer></script> <?php // Change version number when script.js changes ?>
</body>
</html>