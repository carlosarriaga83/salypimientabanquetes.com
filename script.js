document.addEventListener('DOMContentLoaded', () => {
    // HTML Elements for Intro and App Wrapper
    const appWrapper = document.getElementById('appWrapper');

    // --- APPLICATION STATE ---
    // App initialization will now occur directly after data fetching.

    // --- APPLICATION STATE ---
    const appState = {
        currentLanguage: 'es',              // Default language
        coordinatorEmail: '',               // Email of the person making selections
        eventId: EVENT_ID,                  // Event ID from PHP (e.g., "387")
        eventData: null,                    // Stores event structure (courses, dish options)
        dishesData: null,                   // Stores details of all relevant dishes { dishId: dishObject }
        guests: [],                         // Array of guest objects being managed client-side
                                            // Each guest: { internalId, name, email, explicitlySetEmail, dietaryRestrictions, selections, isFromDB, isSubmittedToDB }
        currentGuestInternalId: null,       // Client-side ID for the guest currently being added/edited
        isEditingGuest: false,              // Flag: true if modifying an existing guest from appState.guests
        forceShowNewGuestForm: false,       // Flag to ensure new guest form shows after clicking "Add Another Guest"
        currentStage: 1,                    // Current visible stage number (1-indexed)
        totalStages: 7,                     // Total number of stages in the process (added Thank You stage)
    };

    // --- TRANSLATIONS OBJECT ---
    // (Contains all UI text strings for English 'en' and Spanish 'es')
    const translations = {
        en: {
            pageTitle: "Event Menu Selection", 
            welcomeStageTitle: "Welcome", welcomeMessageText: "Welcome to the menu selection for:", continueButton: "Continue",
            addToGoogleCalendar: "Add to Google Calendar", addToAppleCalendar: "Add to Apple Calendar",
            setupStageTitle: "Initial Setup", langSelectLabel: "Select Language:", 
            coordinatorEmailLabel: "Your Email:", nextButton: "Next", backButton: "Back",
            stage2Title: "Guest Information", stage2Instructions: "Enter guest details. Email is optional (defaults to your email if blank).",
            stage2ChoicePrompt: "You have existing guest selections. What would you like to do?",
            guestNameLabel: "Guest Name:", guestEmailLabelOptional: "Guest Email (Optional):",
            selectMenuButton: "Select Menu for this Guest", errorInvalidEmail: "Please enter a valid email address.",
            errorNameTaken: "This name has already made a selection for this event. Please use a different name or edit their selection.",
            errorGuestNameRequired: "Guest name is required.", errorCoordinatorEmailRequired: "An email is required.",
            stage3Title: "Dietary Restrictions", guestInfoForDietary: "Guest:",
            allergiesLabel: "Allergies or other restrictions (optional):", dietaryRestrictionsLabel: "Dietary Preference:",
            noneOption: "None", vegOption: "Vegetarian", veganOption: "Vegan",
            menuSelectionBaseTitle: "Menu Selection for", currentGuestEmailLabel: "Email:",
            saveSelectionsButton: "Save Selections", updateSelectionsButton: "Update Selections",
            errorCourseMissing: "Please select an option for each course.", summaryTitle: "Selections Summary",
            prepareEmailButton: "Prepare Summary Email (All Guests)", 
            emailPreparedMsg: "Email content prepared. Click the link below to open your email client.",
            emailServerNote: "Note: Actual email sending requires server-side setup. This will prepare a 'mailto:' link.",
            startOverButton: "Start Over", confirmStartOver: "Are you sure you want to start over? All current selections will be lost.",
            editButtonLabel: "Edit", deleteButtonLabel: "Delete", 
            confirmDeleteGuest: "Are you sure you want to delete this guest's selections? This action is client-side only until next submission.",
            submitAllButton: "Confirm & Submit All Selections", finalizeAndSummaryButton: "View Summary",
            addAnotherGuestButton: "Add Another Guest", addEditGuestButton: "Add/Edit Another Guest",
            submissionSuccess: "All guest selections submitted successfully to the database!",
            submissionError: "An error occurred while submitting some guest selections. Please review.",
            loadingDataText: "Loading...", courseDefaultName: "Course",
            noGuestsYet: "No guests have made selections yet.", noSelectionsMadeYet: "No selections made yet.",
            vegetarianSymbol: "V", veganSymbol: "VG",   
            incompatibleDish: " (Not suitable for current diet)", closeModal: "Close",
            dishNamePlaceholder: "Dish Name", openEmailClientLink: "Open in Email Client",
            sendConfirmationButton: "Send Confirmation Email", 
            emailSending: "Sending email...", emailSentSuccess: "Confirmation email sent successfully to ",
            emailSentError: "Failed to send confirmation email: ",
            saveFirstTooltip: "Selections must be submitted to the database first (use 'Confirm & Submit All') to enable individual email confirmation.",
            thankYouStageTitle: "Thank You!", thankYouMessageText: "Your selections have been successfully submitted.",
            calendarPromptText: "You can add the event to your calendar:", 
            // eventReminderText: "We look forward to seeing you at <strong>[Event Name]</strong> on <strong>[Event Date]</strong>!", // Old key, replaced
            eventReminderTextPart1: "We look forward to seeing you at",
            eventReminderTextPart2: "on",
            finishButton: "Finish",
            guestDeletedSuccess: "Guest selection deleted successfully.", // New
            guestDeletedErrorDB: "Failed to delete guest selection from the database. Please try again.", // New
            guestDeletedErrorNetwork: "A network error occurred while trying to delete the guest selection. Please check your connection and try again.", // New
            selectionPeriodClosedError: "We're sorry, the menu selection period for this event has closed. Selections can only be made up to 15 days before the event date ({eventDate}).", // Existing
            selectionPeriodNote: "Please note: Menu selections close 15 days before the event date ({eventDate})."
            // eventNameLabel & eventDateLabel are used by data-translate in HTML, but placeholders are for dynamic content spans
            // eventNamePlaceholder: "Event Name Not Available", eventDateNotAvailable: "Date Not Available" // These are now specific to welcome stage
        },
        es: {
            pageTitle: "Selección de Menú para Evento", 
            welcomeStageTitle: "Bienvenido/a", welcomeMessageText: "Bienvenido/a a la selección de menú para:", continueButton: "Continuar",
            addToGoogleCalendar: "Añadir a Google Calendar", addToAppleCalendar: "Añadir a Apple Calendar",
            setupStageTitle: "Configuración Inicial", langSelectLabel: "Seleccionar Idioma:",
            coordinatorEmailLabel: "Tu Email:", nextButton: "Siguiente", backButton: "Atrás",
            stage2Title: "Información del Invitado", stage2Instructions: "Introduce los datos del invitado. El email es opcional (se usará tu correo si está en blanco).",
            stage2ChoicePrompt: "Ya tienes selecciones de invitados. ¿Qué te gustaría hacer?",
            guestNameLabel: "Nombre del Invitado:", guestEmailLabelOptional: "Email del Invitado (Opcional):",
            selectMenuButton: "Seleccionar Menú para este Invitado", errorInvalidEmail: "Por favor, introduce un email válido.",
            errorNameTaken: "Este nombre ya ha realizado una selección para este evento. Por favor, usa un nombre diferente o edita su selección.",
            errorGuestNameRequired: "El nombre del invitado es obligatorio.", errorCoordinatorEmailRequired: "Al menos un email es obligatorio.",
            stage3Title: "Restricciones Alimentarias", guestInfoForDietary: "Invitado:",
            allergiesLabel: "Alergias u otras restricciones (opcional):", dietaryRestrictionsLabel: "Preferencia Alimentaria:",
            noneOption: "Ninguna", vegOption: "Vegetariano", veganOption: "Vegano",
            menuSelectionBaseTitle: "Selección de Menú para", currentGuestEmailLabel: "Email:",
            saveSelectionsButton: "Guardar Selecciones", updateSelectionsButton: "Actualizar Selecciones",
            errorCourseMissing: "Por favor, selecciona una opción para cada tiempo.", summaryTitle: "Resumen de Selecciones",
            prepareEmailButton: "Preparar Email de Resumen (Todos los Invitados)",
            emailPreparedMsg: "Contenido del email preparado. Haz clic en el enlace de abajo para abrir tu cliente de correo.",
            emailServerNote: "Nota: El envío real de emails requiere configuración del servidor. Esto preparará un enlace 'mailto:'.",
            startOverButton: "Empezar de Nuevo", confirmStartOver: "¿Estás seguro de que quieres empezar de nuevo? Todas las selecciones actuales se perderán.",
            editButtonLabel: "Editar", deleteButtonLabel: "Eliminar",
            confirmDeleteGuest: "¿Estás seguro de que quieres eliminar las selecciones de este invitado? Esta acción es solo local hasta el próximo envío.",
            submitAllButton: "Confirmar y Enviar Todas las Selecciones", finalizeAndSummaryButton: "Ver Resumen",
            addAnotherGuestButton: "Añadir Otro Invitado", addEditGuestButton: "Añadir/Editar Otro Invitado",
            submissionSuccess: "¡Todas las selecciones de invitados se enviaron con éxito a la base de datos!",
            submissionError: "Ocurrió un error al enviar algunas selecciones de invitados. Por favor, revisa.",
            loadingDataText: "Cargando...", courseDefaultName: "Tiempo",
            noGuestsYet: "Aún no hay invitados con selecciones.", noSelectionsMadeYet: "Aún no ha hecho selecciones.",
            vegetarianSymbol: "V", veganSymbol: "VG", incompatibleDish: " (No apto para dieta actual)",
            closeModal: "Cerrar", dishNamePlaceholder: "Nombre del Plato", openEmailClientLink: "Abrir en Cliente de Correo",
            sendConfirmationButton: "Enviar Email de Confirmación",
            emailSending: "Enviando email...", emailSentSuccess: "Email de confirmación enviado con éxito a ",
            emailSentError: "Error al enviar email de confirmación: ",
            thankYouStageTitle: "¡Gracias!", thankYouMessageText: "Tus selecciones han sido enviadas con éxito.",
            calendarPromptText: "Puedes añadir el evento a tu calendario:",
            // eventReminderText: "¡Esperamos verte en <strong>[Event Name]</strong> el <strong>[Event Date]</strong>!", // Old key, replaced
            eventReminderTextPart1: "¡Esperamos verte en",
            eventReminderTextPart2: "el",
            finishButton: "Finalizar",
            guestDeletedSuccess: "Selección de invitado eliminada con éxito.", // New
            guestDeletedErrorDB: "Error al eliminar la selección del invitado de la base de datos. Por favor, inténtalo de nuevo.", // New
            guestDeletedErrorNetwork: "Ocurrió un error de red al intentar eliminar la selección del invitado. Por favor, revisa tu conexión e inténtalo de nuevo.", // New
            saveFirstTooltip: "Las selecciones deben enviarse primero a la base de datos (usa 'Confirmar y Enviar Todas') para habilitar la confirmación individual por email.", // Existing
            selectionPeriodClosedError: "Lo sentimos, el período de selección de menú para este evento ha cerrado. Las selecciones solo se pueden realizar hasta 15 días antes de la fecha del evento ({eventDate}).",
            selectionPeriodNote: "Importante: La selección de menú cierra 15 días antes de la fecha del evento ({eventDate})."
            // eventNamePlaceholder: "Nombre del Evento no Disponible", eventDateNotAvailable: "Fecha no Disponible" // Specific to welcome stage
        }
    };

    // --- DOM ELEMENTS ---
    const progressBar = document.getElementById('progressBar');
    const stages = document.querySelectorAll('.stage'); 
    const languageToggleButton = document.getElementById('languageToggleButton');
    const languageToggleText = document.getElementById('languageToggleText');
    const loadingOverlay = document.getElementById('loadingOverlay');

    // Stage 1 (Welcome) Elements
    const welcomeEventNameElement = document.getElementById('welcomeEventName');
    const welcomeEventDateElement = document.getElementById('welcomeEventDate');
    // Calendar buttons are now on Stage 7, but we get them here as they are unique IDs
    const addToGoogleCalendarButton = document.getElementById('addToGoogleCalendarButton'); 
    const addToAppleCalendarButton = document.getElementById('addToAppleCalendarButton'); 
    const toSetupStageButton = document.getElementById('toSetupStageButton');

    // Stage 2 (Setup) Elements
    const coordinatorEmailInput = document.getElementById('coordinatorEmail');
    const coordinatorEmailError = document.getElementById('coordinatorEmailError');
    const toGuestInfoStageButton = document.getElementById('toGuestInfoStageButton');
    const welcomeStageMessageElement = document.createElement('p'); // Dynamically created for messages on Stage 1 (Welcome)
    welcomeStageMessageElement.className = 'form-message'; 

    // Stage 3 (Guest Info) Elements
    const stage2ChoiceContainer = document.getElementById('stage2ChoiceContainer');
    const stage2InputForm = document.getElementById('stage2InputForm');
    const finalizeAndGoToSummaryButton = document.getElementById('finalizeAndGoToSummaryButton');
    if (finalizeAndGoToSummaryButton) {
        finalizeAndGoToSummaryButton.style.backgroundColor = '#5cb85c';
        finalizeAndGoToSummaryButton.style.color = 'white'; // For better contrast
    }

    const showAddGuestFormButton = document.getElementById('showAddGuestFormButton');
    const guestNameInput = document.getElementById('guestName');
    const guestEmailInput = document.getElementById('guestEmail');
    const guestNameError = document.getElementById('guestNameError');
    const guestEmailError = document.getElementById('guestEmailError');
    const backToSetupStageButton = document.getElementById('backToSetupStageButton');
    const selectMenuForGuestButton = document.getElementById('selectMenuForGuestButton');
    
    // Stage 4 (Dietary) Elements
    const currentGuestNameForDietary = document.getElementById('currentGuestNameForDietary');
    const allergiesInput = document.getElementById('allergies');
    const dietaryTypeRadios = document.getElementsByName('dietaryType'); // NodeList of radio buttons
    const backToGuestInfoStageButton = document.getElementById('backToGuestInfoStageButton');
    const toMenuSelectionStageButton = document.getElementById('toMenuSelectionStageButton');

    // Stage 5 (Menu Selection) Elements
    const menuSelectionTitle = document.getElementById('menuSelectionTitle');
    const currentGuestNameForMenu = document.getElementById('currentGuestNameForMenu');
    const currentGuestEmailForMenu = document.getElementById('currentGuestEmailForMenu');
    const menuCoursesContainer = document.getElementById('menuCoursesContainer');
    const menuSelectionError = document.getElementById('menuSelectionError');
    const backToDietaryStageButton = document.getElementById('backToDietaryStageButton');
    const saveSelectionsButton = document.getElementById('saveSelectionsButton');

    // Stage 6 (Summary) Elements (Previously Stage 5)
    const summaryListContainer = document.getElementById('summaryListContainer');
    const prepareEmailButton = document.getElementById('prepareEmailButton');
    const emailPreparedMessage = document.getElementById('emailPreparedMessage');
    const emailServerNote = document.getElementById('emailServerNote');
    const mailtoLinkContainer = document.getElementById('mailtoLinkContainer');
    const backToGuestInfoFromSummaryButton = document.getElementById('backToGuestInfoFromSummaryButton');
    const submitAllButton = document.getElementById('submitAllButton');
    const startOverButton = document.getElementById('startOverButton');

    // Stage 7 (Thank You) Elements
    const thankYouEventNameElement = document.getElementById('thankYouEventName');
    const thankYouEventDateElement = document.getElementById('thankYouEventDate');
    const finishAndExitButton = document.getElementById('finishAndExitButton');

    // Modal Elements
    const imagePreviewModal = document.getElementById('imagePreviewModal');
    const modalImage = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');
    const closeModalButton = imagePreviewModal.querySelector('.close-modal-button');

    // Theme Toggle Button
    const themeToggleButton = document.getElementById('themeToggleButton'); 

    // --- UTILITY FUNCTIONS ---
    function updateProgressBar() {
        const progressPercentage = (appState.currentStage / appState.totalStages) * 100;
        console.log(`Progress: Stage ${appState.currentStage}/${appState.totalStages} = ${progressPercentage}%`); // For debugging
        progressBar.style.width = `${progressPercentage}%`;
    }

    function translatePage() {
        document.querySelectorAll('[data-translate]').forEach(element => {
            const translationKey = element.getAttribute('data-translate');
            const translationExists = translations[appState.currentLanguage] && translations[appState.currentLanguage][translationKey];

            if (translationExists) {
                const translatedText = translations[appState.currentLanguage][translationKey];
                if (element.tagName === 'INPUT' && element.hasAttribute('placeholder')) {
                    element.placeholder = translatedText;
                } else {
                    // Handle buttons with spinner icons carefully
                    if (element.classList.contains('send-confirmation-button') && element.querySelector('i.fa-spinner')) {
                        // Find the text node part of the button to update, leave icon
                        const textNode = Array.from(element.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
                        if (textNode) {
                            textNode.textContent = ` ${translations[appState.currentLanguage].emailSending}`; // Add space before text
                        }
                    } else {
                        element.innerHTML = translatedText;
                    }
                }
            }
        });

        document.documentElement.lang = appState.currentLanguage; // Set HTML lang attribute

        // Update dynamic button texts not covered by data-translate
        if (saveSelectionsButton) {
            saveSelectionsButton.textContent = translations[appState.currentLanguage][appState.isEditingGuest ? 'updateSelectionsButton' : 'saveSelectionsButton'];
        }
        if (selectMenuForGuestButton) {
           selectMenuForGuestButton.textContent = translations[appState.currentLanguage][appState.isEditingGuest ? 'updateSelectionsButton' : 'selectMenuButton'];
        }

        // Re-translate summary stage buttons if visible (especially titles for disabled buttons)
        if (appState.currentStage === 5) {
            summaryListContainer.querySelectorAll('.send-confirmation-button').forEach(button => {
                const guestId = button.dataset.guestInternalId;
                const guest = findGuestByInternalId(guestId);
                if (guest && !button.querySelector('i.fa-spinner')) { 
                    const isDisabled = !guest.isSubmittedToDB;
                    button.title = isDisabled ? translations[appState.currentLanguage].saveFirstTooltip : translations[appState.currentLanguage].sendConfirmationButton;
                    // Set innerHTML if it doesn't have a spinner, otherwise it was set by emailSending logic
                    if (!button.querySelector('i.fa-spinner')) {
                        button.innerHTML = translations[appState.currentLanguage].sendConfirmationButton;
                    }
                }
            });
        }
        // Re-translate dynamic stage 1 message if it exists
        if (welcomeStageMessageElement.parentNode && welcomeStageMessageElement.dataset.translationKey) {
            let messageText = translations[appState.currentLanguage][welcomeStageMessageElement.dataset.translationKey] || welcomeStageMessageElement.dataset.defaultText || "";
            if (welcomeStageMessageElement.dataset.eventDate) {
                messageText = messageText.replace("{eventDate}", welcomeStageMessageElement.dataset.eventDate);
            }
            welcomeStageMessageElement.textContent = messageText;
        }
        // Re-translate dynamic thank you stage event details if visible
        if (appState.currentStage === 7) {
            populateThankYouStageDetails(); // This will handle translation of dynamic parts
        }

        // Update total stages for progress bar if it changed (e.g. dynamically)
        // Though in this case it's fixed, good practice if stages could change.
        // No, appState.totalStages is now fixed at 7.

        updateProgressBar(); // Ensure progress bar is correct after language change
    }
    
    function getTranslatedDishProperty(dishId, property) {
        const lang = appState.currentLanguage;
        const translationKey = `dish_${dishId}_${property.toLowerCase()}`;
        if (translations[lang] && translations[lang][translationKey]) {
            return translations[lang][translationKey];
        }
        // Fallback to data from server if translation not found
        return (appState.dishesData && appState.dishesData[dishId] && appState.dishesData[dishId][property]) ? 
               appState.dishesData[dishId][property] : 
               `Unknown ${property}`; // Fallback string
    }

    function clearEventDetails() {
        // For Welcome Stage
        const placeholderName = translations[appState.currentLanguage].eventNamePlaceholder || 'Event Name Not Available';
        const placeholderDate = translations[appState.currentLanguage].eventDateNotAvailable || 'Date Not Available';

        if (welcomeEventNameElement) welcomeEventNameElement.textContent = placeholderName;
        if (welcomeEventDateElement) welcomeEventDateElement.textContent = placeholderDate;
    }

    function updateStage2View() {
        let showInputFormView = false; // Determines if the guest name/email input form is shown
        
        if (appState.isEditingGuest && appState.currentGuestInternalId) {
            // Case 1: Editing an existing guest. Show input form.
            showInputFormView = true;
            const guest = findGuestByInternalId(appState.currentGuestInternalId);
            if (guest) {
                guestNameInput.value = guest.name;
                // If email was defaulted to coordinator's and not explicitly set by user, show blank for editing
                guestEmailInput.value = (guest.email === appState.coordinatorEmail && !guest.explicitlySetEmail) ? '' : guest.email;
                // The button to proceed (select/update menu) will be shown if showInputFormView is true.
            } else {
                console.error("Editing guest but guest not found:", appState.currentGuestInternalId);
                showInputFormView = false; // Fallback
            }
        } else {
            // Case 2: Not editing a specific guest.
            if (appState.forceShowNewGuestForm === true || appState.guests.length === 0) {
                // Show input form if "Add Another Guest" was clicked or no guests exist yet.
                showInputFormView = true;
                guestNameInput.value = ''; // Clear form for new guest
                guestEmailInput.value = '';
                guestNameError.textContent = '';
                guestEmailError.textContent = '';
                if (appState.forceShowNewGuestForm) appState.forceShowNewGuestForm = false; // Reset flag
            } else {
                // Default: Guests exist, not editing, not forcing new guest form -> show choices.
                showInputFormView = false; // This will make stage2ChoiceContainer visible
            }
        }

        // Apply visibility to forms
        stage2InputForm.style.display = showInputFormView ? 'block' : 'none';
        stage2ChoiceContainer.style.display = showInputFormView ? 'none' : 'block';

        // The selectMenuForGuestButton should be visible if the input form is visible.
        if (selectMenuForGuestButton) selectMenuForGuestButton.style.display = showInputFormView ? 'inline-block' : 'none';
        
        translatePage(); // Ensure button texts are correct after view update
    }

    function showStage(stageNumber) {
        const currentActiveStage = document.querySelector('.stage.active');
        if (currentActiveStage) {
            currentActiveStage.classList.add('exiting');
            setTimeout(() => {
                currentActiveStage.classList.remove('active', 'exiting');
                currentActiveStage.style.display = 'none';

                const nextStage = document.getElementById(`stage${stageNumber}`);
                if (nextStage) {
                    nextStage.style.display = 'block';
                    void nextStage.offsetWidth; // Force reflow for transition
                    nextStage.classList.add('active');
                }
                appState.currentStage = stageNumber;
                updateProgressBar();
                updateNavButtonVisibility();
                if (stageNumber === 3) { updateStage2View(); } // Old stage 2 is now stage 3
                if (stageNumber === 6) { populateSummaryStage(); } // Old stage 5 is now stage 6
                if (stageNumber === 7) { populateThankYouStageDetails(); }
            }, 400); // Match CSS transition duration
        } else { 
            // Initial stage load (now stage 1 - Welcome - after intro)
            const nextStage = document.getElementById(`stage${stageNumber}`);
            if (nextStage) {
                nextStage.style.display = 'block';
                void nextStage.offsetWidth; 
                nextStage.classList.add('active');
            }
            appState.currentStage = stageNumber;
            updateProgressBar();
            updateNavButtonVisibility();
            if (stageNumber === 3) { updateStage2View(); }
            // No need to call populateThankYouStageDetails here as it's not the initial stage.
        }
    }
    
    function updateNavButtonVisibility() {
        if (backToSetupStageButton) backToSetupStageButton.style.display = (appState.currentStage === 3) ? 'inline-block' : 'none';
        if (backToGuestInfoStageButton) backToGuestInfoStageButton.style.display = (appState.currentStage === 4) ? 'inline-block' : 'none';
        if (backToDietaryStageButton) backToDietaryStageButton.style.display = (appState.currentStage === 5) ? 'inline-block' : 'none';
        if (backToGuestInfoFromSummaryButton) backToGuestInfoFromSummaryButton.style.display = (appState.currentStage === 6) ? 'inline-block' : 'none';
        
        if(submitAllButton) {
            submitAllButton.style.display = (appState.guests.length > 0 && appState.currentStage === 6) ? 'inline-block' : 'none';
        }
        // if (prepareEmailButton) { // Coordinator summary email button
            //prepareEmailButton.style.display = (appState.guests.length > 0 && appState.currentStage === 5) ? 'inline-block' : 'none';
        // }
    }

    function isValidEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }
    function findGuestByInternalId(internalId) { return appState.guests.find(g => g.internalId === internalId); }
    function findGuestIndexByInternalId(internalId) { return appState.guests.findIndex(g => g.internalId === internalId); }
    function generateInternalId() { return Date.now().toString(36) + Math.random().toString(36).substr(2); }

    // --- LAZY LOADING IMAGES ---
    let imageObserver;

    function initializeImageObserver() {
        // Check if IntersectionObserver is supported
        if (!('IntersectionObserver' in window)) {
            console.warn("IntersectionObserver not supported, images will load immediately.");
            // Fallback for older browsers: load all images immediately
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
            return;
        }

        // Create an observer that loads images when they are visible or near the viewport
        imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src; // Load the actual image
                    img.removeAttribute('data-src'); // Remove data-src to prevent re-observing
                    img.classList.add('loaded'); // Add a class for potential fade-in effect
                    observer.unobserve(img); // Stop observing once loaded
                }
            });
        }, { rootMargin: "0px 0px 200px 0px" }); // Load images when they are within 200px of the viewport
    }

    // Helper function to format a Date object to YYYYMMDD string
    function formatDateToYYYYMMDD(date) {
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-indexed
        const day = date.getDate().toString().padStart(2, '0');
        return `${year}${month}${day}`;
    }

    // --- API CALLS ---
    async function fetchEventData() {
        loadingOverlay.style.display = 'flex';
        let eventDateCheckOk = true;

        // Clear previous stage 1 message & initialize event details with placeholders
        if (welcomeStageMessageElement.parentNode) welcomeStageMessageElement.parentNode.removeChild(welcomeStageMessageElement);
        welcomeStageMessageElement.textContent = ''; welcomeStageMessageElement.className = 'form-message'; 
        welcomeStageMessageElement.removeAttribute('data-translation-key');
        welcomeStageMessageElement.removeAttribute('data-default-text');
        welcomeStageMessageElement.removeAttribute('data-event-date');
        clearEventDetails(); // Clear welcome stage details initially
        try {
            const response = await fetch(`api.php?action=get_event_data&e_id=${appState.eventId}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();

            if (data.success && data.event) {
                // dishObject is the actual dish data, dishKey is its ID (e.g., "d101")
                const populateDishTranslations = (dishObject, dishKey) => {
                    if (dishObject && dishObject.NAME && (!translations.es[`dish_${dishKey}_name`])) {
                        if (!translations.es) translations.es = {}; // Ensure 'es' object exists
                        translations.es[`dish_${dishKey}_name`] = dishObject.NAME;
                    }
                    if (dishObject && dishObject.DESCRIPCION && (!translations.es[`dish_${dishKey}_descripcion`])) {
                        if (!translations.es) translations.es = {};
                        translations.es[`dish_${dishKey}_descripcion`] = dishObject.DESCRIPCION;
                    }
                };

                appState.eventData = data.event;
                appState.dishesData = data.dishes;

                // Populate Spanish translations for dishes if not present
                Object.keys(appState.dishesData || {}).forEach(dishId => {
                    populateDishTranslations(appState.dishesData[dishId], dishId);
                });

                // Start loading dish images after getting basic data.
                // Do this only if appWrapper is already visible (intro sequence complete)
                if (appWrapper && appWrapper.classList.contains('visible')) {
                    loadDishImages(appState.dishesData);
                } else {
                    // If not visible yet, defer image loading until app init is done and appWrapper is shown.
                    console.log("Deferring image loading until appWrapper is visible.");
                }

                // Function to load images - can be called now or later.
                async function loadDishImages() {
                    if (!appState.dishesData || Object.keys(appState.dishesData).length === 0) {
                        console.warn("No dishes data available to load images for.");
                        return;
                    }

                    const dishIds = Object.keys(appState.dishesData);
                    if (dishIds.length === 0) {
                        console.log("No dish IDs found, skipping image load.");
                        return;
                    }

                    try {
                        const imageResponse = await fetch(`api.php?action=get_dish_images_by_ids&dish_ids=${dishIds.join(',')}`);
                        if (!imageResponse.ok) {
                            throw new Error(`Image fetch error: ${imageResponse.status}`);
                        }
                        const imageData = await imageResponse.json();
                        if (imageData.success && imageData.dish_images) {
                            // Update appState.dishesData with image URLs (base64 data URLs).
                            Object.assign(appState.dishesData, imageData.dish_images);
                            // No UI update here; it happens when populateMenuSelectionStage renders the menu
                        } else {
                            console.error("Failed to load dish images:", imageData.error || "Unknown error");
                        }
                    } catch (error) {
                        console.error("Error during image fetch:", error);
                    }
                } // This was an extra }); that should be removed. The function definition ends above.

                // Populate Event Name and Date on Welcome Stage
                const placeholderName = translations[appState.currentLanguage].eventNamePlaceholder || 'Event Name Not Available';
                const placeholderDate = translations[appState.currentLanguage].eventDateNotAvailable || 'Date Not Available';
                if (welcomeEventNameElement) {
                    welcomeEventNameElement.textContent = appState.eventData.NAME || placeholderName;
                }

                // Date Check Logic
                if (appState.eventData.FECHA) {
                    const eventDateStr = appState.eventData.FECHA; // Expected "YYYY-MM-DD"
                    const dateParts = eventDateStr.split('-');
                    const eventYear = parseInt(dateParts[0]);
                    const eventMonth = parseInt(dateParts[1]) - 1; // JS months are 0-indexed
                    const eventDay = parseInt(dateParts[2]);
                    const eventDate = new Date(eventYear, eventMonth, eventDay); 

                    if (isNaN(eventDate.getTime())) {
                        console.error("Invalid event date after parsing:", eventDateStr, eventDate);
                        if (welcomeEventDateElement) {
                            welcomeEventDateElement.textContent = placeholderDate;
                        }
                        // Decide how to handle: maybe allow to proceed but log, or hard fail
                    } else {
                        const today = new Date(); 
                        today.setHours(0, 0, 0, 0); // Normalize today to start of day in local timezone
                        
                        const fifteenDaysBeforeEvent = new Date(eventDate.getTime()); // Clone
                        fifteenDaysBeforeEvent.setDate(eventDate.getDate() - 15);
                        // No need to setHours on fifteenDaysBeforeEvent as it's derived from eventDate (already 00:00)

                        const formattedEventDate = eventDate.toLocaleDateString(
                            appState.currentLanguage === 'es' ? 'es-ES' : 'en-US', 
                            { year: 'numeric', month: 'long', day: 'numeric' }
                        );
                        if (welcomeEventDateElement) {
                            welcomeEventDateElement.textContent = formattedEventDate;
                        }
                        
                        welcomeStageMessageElement.dataset.eventDate = formattedEventDate; // For re-translation if language changes

                        if (today >= fifteenDaysBeforeEvent) {
                            eventDateCheckOk = false;
                            welcomeStageMessageElement.dataset.translationKey = 'selectionPeriodClosedError';
                            welcomeStageMessageElement.dataset.defaultText = "Selection period has closed."; // Fallback
                            welcomeStageMessageElement.classList.add('error-message');
                            if (toSetupStageButton) { // Disable "Continue" button on Welcome stage
                                toSetupStageButton.disabled = true; 
                                toSetupStageButton.title = "Selection period closed"; // Tooltip
                            }
                        } else {
                            welcomeStageMessageElement.dataset.translationKey = 'selectionPeriodNote';
                            welcomeStageMessageElement.dataset.defaultText = "Selection period is open."; // Fallback
                            welcomeStageMessageElement.classList.add('info-message');
                            if (toSetupStageButton) { // Enable "Continue" button on Welcome stage
                                toSetupStageButton.disabled = false; 
                                toSetupStageButton.title = ""; 
                            }
                        }
                        // Insert the message element into Stage 1 DOM
                        const welcomeStageDiv = document.getElementById('stage1');
                        // Insert before the "Continue" button on Stage 1
                        const insertBeforeElOnWelcome = toSetupStageButton; 
                        if (welcomeStageDiv && insertBeforeElOnWelcome) {
                             welcomeStageDiv.insertBefore(welcomeStageMessageElement, insertBeforeElOnWelcome);
                        } else if (welcomeStageDiv) { // Fallback: append to welcomeStageDiv
                             welcomeStageDiv.appendChild(welcomeStageMessageElement);
                        }
                    }
                } else { 
                    console.warn("Event date (FECHA) not found in event data. Proceeding without date check.");
                    if (welcomeEventDateElement) {
                         welcomeEventDateElement.textContent = placeholderDate;
                    }
                }
            } else { 
                clearEventDetails(); // Ensure placeholders are set if data.event is missing
                throw new Error(data.error || "Failed to load event data."); 
            }
        } catch (error) { 
            console.error("Error fetching event data:", error); 
            clearEventDetails(); // Ensure placeholders are set on any error

            welcomeStageMessageElement.textContent = "Could not load event details.";
            welcomeStageMessageElement.classList.add('error-message');
            const welcomeStageDiv = document.getElementById('stage1');
            if (welcomeStageDiv && toSetupStageButton) welcomeStageDiv.insertBefore(welcomeStageMessageElement, toSetupStageButton);
            else if (welcomeStageDiv) welcomeStageDiv.appendChild(welcomeStageMessageElement);
            if (toSetupStageButton) toSetupStageButton.disabled = true; // Disable continue on error
            eventDateCheckOk = false; // Critical failure
        } finally { 
            loadingOverlay.style.display = 'none'; 
            translatePage(); // Translate static elements and the new message
        }
        return eventDateCheckOk; // Return status for intro logic
    }

    async function fetchCoordinatorSelections(coordinatorEmail) {
        loadingOverlay.style.display = 'flex';
        try {
            const response = await fetch(`api.php?action=get_coordinator_selections&e_id=${appState.eventId}&coordinator_email=${encodeURIComponent(coordinatorEmail)}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();

            if (data.success && data.selections) {
                appState.guests = data.selections.map(selDataFromDB => {
                    const selections = {};
                    for (const key in selDataFromDB.SELECTED) {
                        if (key.startsWith('T') && !["RESTRICTIONS", "ALERGIAS"].includes(key)) {
                            selections[key] = selDataFromDB.SELECTED[key];
                        }
                    }
                    return {
                        internalId: generateInternalId(), 
                        name: selDataFromDB.NAME,
                        email: selDataFromDB.GUEST_EMAIL || selDataFromDB.USER,
                        explicitlySetEmail: !!selDataFromDB.GUEST_EMAIL && selDataFromDB.GUEST_EMAIL !== selDataFromDB.USER,
                        dietaryRestrictions: { 
                            type: selDataFromDB.SELECTED.RESTRICTIONS ? (selDataFromDB.SELECTED.RESTRICTIONS[0] || '') : '', 
                            allergies: selDataFromDB.SELECTED.ALERGIAS || '' 
                        },
                        selections: selections,
                        isFromDB: true, 
                        isSubmittedToDB: true // Assume data from DB means it's "submitted"
                    };
                });
            } else { 
                appState.guests = []; 
                console.log("No existing selections or API error for coordinator selections:", data.error || "No selections array");
            }
        } catch (error) { 
            console.error("Error fetching coordinator selections:", error);
            appState.guests = []; // Reset on error
        } finally { 
            loadingOverlay.style.display = 'none'; 
        }
    }
    
    async function checkGuestExistsDB(name) { 
        try {
            const response = await fetch(`api.php?action=check_guest&e_id=${appState.eventId}&name=${encodeURIComponent(name)}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json(); 
            return data.exists;
        } catch (error) { 
            console.error("Error checking guest (DB):", error); 
            return true; // Fail safe, assume exists to prevent accidental overwrite on error
        }
    }

    async function submitAllSelections() {
        if (appState.guests.length === 0) { 
            alert(translations[appState.currentLanguage].errorNoGuestsToSubmit); 
            return; 
        }
        loadingOverlay.style.display = 'flex'; 
        let allDBSavesSuccessful = true; 
        let dbSaveErrors = [];
        let successfullySavedGuestsForEmailing = [];

        // Step 1: Save all guest data to the database
        for (const guest of appState.guests) {
            const payload = {
                USER: appState.coordinatorEmail, 
                GUEST_EMAIL: guest.email,       
                SELECTED: { 
                    RESTRICTIONS: guest.dietaryRestrictions.type ? [guest.dietaryRestrictions.type] : [], 
                    ALERGIAS: guest.dietaryRestrictions.allergies, 
                    ...guest.selections 
                },
                E_ID: appState.eventId, 
                NAME: guest.name
            };
            
            try {
                const response = await fetch('api.php?action=save_single_guest_selection', { 
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) 
                });
                const result = await response.json();
                if (result.success) { 
                    guest.isSubmittedToDB = true; 
                    successfullySavedGuestsForEmailing.push(guest);
                } else { 
                    allDBSavesSuccessful = false; guest.isSubmittedToDB = false; 
                    dbSaveErrors.push(`DB Save Failed for ${guest.name}: ${result.message || 'Unknown error'}`); 
                }
            } catch (error) { 
                allDBSavesSuccessful = false; guest.isSubmittedToDB = false;
                dbSaveErrors.push(`Network error during DB Save for ${guest.name}: ${error.message}`);
            }
        }

        // Step 2: If all DB saves were successful, proceed to send individual emails
        let allEmailsInitiatedSuccessfully = true; 
        let emailSendErrors = [];

        if (allDBSavesSuccessful && successfullySavedGuestsForEmailing.length > 0) {
            populateSummaryStage(); // Refresh UI to enable buttons if needed before sending

            for (const guestToEmail of successfullySavedGuestsForEmailing) {
                const emailInitiated = await sendGuestConfirmationEmail(guestToEmail, true); // true to suppress individual alerts
                if (!emailInitiated) { 
                    allEmailsInitiatedSuccessfully = false; 
                    emailSendErrors.push(`Failed to initiate email for ${guestToEmail.name}.`);
                }
                await new Promise(resolve => setTimeout(resolve, 300)); // Small delay between email API calls
            }
        }
        
        loadingOverlay.style.display = 'none'; // Hide global overlay after all operations

        // Final messaging to the user
        let finalMessage = "";
        if (dbSaveErrors.length > 0) { 
            finalMessage += translations[appState.currentLanguage].submissionError + "\nDB Save Issues:\n" + dbSaveErrors.join("\n"); 
        } else { 
            finalMessage += translations[appState.currentLanguage].submissionSuccess; 
        }

        if (allDBSavesSuccessful && successfullySavedGuestsForEmailing.length > 0) {
            if (emailSendErrors.length > 0) { 
                finalMessage += "\n\nSome confirmation emails could not be initiated (check console for details):\n" + emailSendErrors.join("\n"); 
            } // No need for "all emails initiated successfully" message here, as we are moving to a new stage.
        }
        // If DB saves failed, the user gets an alert and stays on summary to review.
        // Otherwise, proceed to Thank You stage.
        
        loadingOverlay.style.display = 'none'; // Hide global overlay after all operations

        if (dbSaveErrors.length > 0) { 
            alert(translations[appState.currentLanguage].submissionError + "\nDB Save Issues:\n" + dbSaveErrors.join("\n")); 
            populateSummaryStage(); // Refresh summary and stay on Stage 6
        } else { 
            // DB Saves were successful.
            // Individual email errors (if any) were collected in emailSendErrors.
            // We can inform the user about email issues on the thank you page or just log them.
            if (emailSendErrors.length > 0) {
                console.warn("Some confirmation emails could not be initiated:", emailSendErrors.join("\n"));
                // Optionally, display a non-blocking message about email issues on stage 7.
            }
            showStage(7); // Go to Thank You Stage
        }
    }

    function prepareCoordinatorSummaryEmail(guestsForSummary) {
        let introMessage = `Dear Coordinator (${appState.coordinatorEmail}),\n\nHere is a summary of guest selections for Event: ${appState.eventData?.NAME || appState.eventId}\n\n`;
        let summaryText = introMessage;
        guestsForSummary.forEach((guest, index) => {
            summaryText += `Guest #${index + 1}: ${guest.name} (${guest.email})\n`;
            if (guest.dietaryRestrictions.type) { 
                const dtKey = guest.dietaryRestrictions.type === 'OPT_VEGETARIANO' ? 'vegOption' : (guest.dietaryRestrictions.type === 'OPT_VEGANO' ? 'veganOption' : ''); 
                summaryText += `  Diet: ${translations[appState.currentLanguage][dtKey] || translations[appState.currentLanguage].noneOption}\n`;
            }
            if (guest.dietaryRestrictions.allergies) { 
                summaryText += `  Allergies: ${guest.dietaryRestrictions.allergies}\n`; 
            }
            summaryText += `  Selections:\n`;
            Object.entries(guest.selections).forEach(([cKey, dId]) => { 
                const d = appState.dishesData[dId]; 
                const dName = d ? getTranslatedDishProperty(d.ID, 'NAME') : '?'; 
                const cTitle = (translations[appState.currentLanguage][`courseName_${cKey}`]) || `${translations[appState.currentLanguage].courseDefaultName} ${cKey.substring(1)}`; 
                summaryText += `    ${cTitle}: ${dName}\n`;
            });
            summaryText += "\n";
        });
        const subject = `Selections Summary - Event: ${appState.eventData?.NAME || appState.eventId}`;
        const mailtoSubject = encodeURIComponent(subject); 
        const mailtoBody = encodeURIComponent(summaryText);
        const mailtoLink = `mailto:${appState.coordinatorEmail}?subject=${mailtoSubject}&body=${mailtoBody}`;
        
        mailtoLinkContainer.innerHTML = `<p><strong>Coordinator Selections Summary Prepared:</strong></p><a href="${mailtoLink}" target="_blank" data-translate="openEmailClientLink">${translations[appState.currentLanguage].openEmailClientLink}</a>`;
        emailPreparedMessage.textContent = `Coordinator selections summary email content prepared. Click the link above.`;
        emailPreparedMessage.style.display = 'block'; 
        emailServerNote.style.display = 'block';
    }

    async function sendGuestConfirmationEmail(guest, suppressIndividualAlert = false) {
        if (!guest || !guest.email) { 
            if (!suppressIndividualAlert) alert("Guest data or email is missing."); 
            return false; 
        }

        const btnSelector = `.send-confirmation-button[data-guest-internal-id="${guest.internalId}"]`;
        const sendBtn = document.querySelector(btnSelector);
        const originalBtnText = translations[appState.currentLanguage].sendConfirmationButton; 

        if (sendBtn) { 
            sendBtn.disabled = true; 
            sendBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${translations[appState.currentLanguage].emailSending}`; 
        }

        let sumHtml = '<h4>Your Selections:</h4><table class="summary-table" style="width:100%; border-collapse: collapse;"><thead><tr><th style="text-align:left; padding:8px; border-bottom:1px solid #eee; background-color:#f9f9f9;">Course</th><th style="text-align:left; padding:8px; border-bottom:1px solid #eee; background-color:#f9f9f9;">Dish</th></tr></thead><tbody>';
        if (Object.keys(guest.selections).length > 0) { 
            Object.entries(guest.selections).forEach(([cK, dId]) => { 
                const d = appState.dishesData[dId]; 
                const dN = d ? getTranslatedDishProperty(d.ID, 'NAME') : '?'; 
                const cT = (translations[appState.currentLanguage][`courseName_${cK}`]) || `${translations[appState.currentLanguage].courseDefaultName} ${cK.substring(1)}`; 
                sumHtml += `<tr><td style="padding:8px; border-bottom:1px solid #eee;">${cT}</td><td style="padding:8px; border-bottom:1px solid #eee;">${dN}</td></tr>`;
            });
        } else { 
            sumHtml += `<tr><td colspan="2" style="padding:8px; border-bottom:1px solid #eee;">${translations[appState.currentLanguage].noSelectionsMadeYet}</td></tr>`;
        }
        sumHtml += '</tbody></table>';
        if (guest.dietaryRestrictions.type || guest.dietaryRestrictions.allergies) { 
            sumHtml += '<h4 style="margin-top:15px;">Your Dietary Information:</h4><p style="margin:5px 0;">'; 
            if (guest.dietaryRestrictions.type) { 
                const dTK = guest.dietaryRestrictions.type === 'OPT_VEGETARIANO' ? 'vegOption' : (guest.dietaryRestrictions.type === 'OPT_VEGANO' ? 'veganOption' : ''); 
                sumHtml += `<strong>Preference:</strong> ${translations[appState.currentLanguage][dTK] || translations[appState.currentLanguage].noneOption}<br>`;
            } 
            if (guest.dietaryRestrictions.allergies) { 
                sumHtml += `<strong>Allergies/Restrictions:</strong> ${guest.dietaryRestrictions.allergies}`;
            } 
            sumHtml += '</p>';
        }
        const payload = { 
            guestEmail: guest.email, guestName: guest.name, eventId: appState.eventId, 
            eventName: appState.eventData?.NAME || "the event", language: appState.currentLanguage, summaryHtml: sumHtml 
        };
        try {
            const r = await fetch('api.php?action=send_guest_confirmation_email', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const res = await r.json();
            if (!suppressIndividualAlert) { 
                if (res.success) { alert(translations[appState.currentLanguage].emailSentSuccess + guest.email); } 
                else { alert(translations[appState.currentLanguage].emailSentError + (res.message || '?')); }
            }
            return res.success;
        } catch (e) { 
            if (!suppressIndividualAlert) { alert(translations[appState.currentLanguage].emailSentError + e.message); } 
            console.error(`Email send error for ${guest.name}:`, e); 
            return false;
        } finally { 
            if (sendBtn) { sendBtn.disabled = false; sendBtn.innerHTML = originalBtnText; }
        }
    }

    async function deleteGuestSelectionFromDB(eventId, guestName) {
        try {
            // Add action to the URL query string for consistency with other POST requests
            const response = await fetch('api.php?action=delete_guest_selection', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    e_id: eventId, // action is now in URL, no longer strictly needed in body
                    name: guestName
                })
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json(); // Expect { success: true/false, message: '...' }
        } catch (error) {
            console.error('Error in deleteGuestSelectionFromDB:', error);
            // Return a consistent error structure for the caller
            return { success: false, message: error.message || 'Network request failed' };
        }
    }

    // --- STAGE SPECIFIC LOGIC (Event Listeners & Stage Population) ---
    if (languageToggleButton) {
        languageToggleButton.addEventListener('click', () => {
            appState.currentLanguage = appState.currentLanguage === 'en' ? 'es' : 'en';
            updateLanguageToggleDisplay();
            handleLanguageChange();
        });
    }

    function handleLanguageChange() {
        translatePage(); // Translates labels and other static text

        // Any other logic that needs to run when language changes

        // Re-populate event name and re-format/display event date on Welcome Stage if data exists
        const placeholderName = translations[appState.currentLanguage].eventNamePlaceholder || 'Event Name Not Available';
        const placeholderDate = translations[appState.currentLanguage].eventDateNotAvailable || 'Date Not Available';
        if (appState.eventData) {
            if (welcomeEventNameElement) {
                welcomeEventNameElement.textContent = appState.eventData.NAME || placeholderName;
            }
            if (appState.eventData.FECHA && welcomeEventDateElement) {
                const dateParts = appState.eventData.FECHA.split('-');
                const eventDate = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                if (!isNaN(eventDate.getTime())) {
                    const formattedEventDate = eventDate.toLocaleDateString(
                        appState.currentLanguage === 'es' ? 'es-ES' : 'en-US',
                        { year: 'numeric', month: 'long', day: 'numeric' }
                    );
                    welcomeEventDateElement.textContent = formattedEventDate;
                    // Also update the date used in the setup stage message if it exists
                    if (welcomeStageMessageElement.dataset.eventDate) welcomeStageMessageElement.dataset.eventDate = formattedEventDate;
                } else {
                    welcomeEventDateElement.textContent = placeholderDate;
                }
            } else if (welcomeEventDateElement) { // If no FECHA, ensure placeholder
                welcomeEventDateElement.textContent = placeholderDate;
            }
        } else { 
            clearEventDetails(); // Ensure placeholders are set according to new language
        }

        // Re-translate dynamic setup stage message if it exists
        if (welcomeStageMessageElement.parentNode && welcomeStageMessageElement.dataset.translationKey) {
            let messageText = translations[appState.currentLanguage][welcomeStageMessageElement.dataset.translationKey] || welcomeStageMessageElement.dataset.defaultText || "";
            if (welcomeStageMessageElement.dataset.eventDate) messageText = messageText.replace("{eventDate}", welcomeStageMessageElement.dataset.eventDate);
            welcomeStageMessageElement.textContent = messageText;
        }
        if (appState.currentStage === 6) populateSummaryStage(); 
        if (appState.currentStage === 5 && appState.currentGuestInternalId) {
            const g = findGuestByInternalId(appState.currentGuestInternalId); 
            if (g) populateMenuSelectionStage(g); // Stage 5 is Menu Selection
        }
    }

    function updateLanguageToggleDisplay() {
        if (languageToggleText) {
            languageToggleText.textContent = appState.currentLanguage.toUpperCase();
        }
    }

    // Stage 1 (Welcome) -> Stage 2 (Setup)
    toSetupStageButton.addEventListener('click', () => showStage(2));

    // Calendar Button Event Listeners
    if (addToGoogleCalendarButton) {
        addToGoogleCalendarButton.addEventListener('click', () => {
            if (!appState.eventData || !appState.eventData.FECHA || !appState.eventData.NAME) {
                alert("Event details are not fully loaded yet.");
                return;
            }
            const eventName = appState.eventData.NAME || 'Event';
            const eventDateStr = appState.eventData.FECHA; // "YYYY-MM-DD"

            const dateParts = eventDateStr.split('-');
            const eventStartDateObj = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));

            // For an all-day event, Google Calendar expects the end date to be the day AFTER the event.
            const eventEndDateObj = new Date(eventStartDateObj);
            eventEndDateObj.setDate(eventStartDateObj.getDate() + 1);

            const googleStartDateStr = formatDateToYYYYMMDD(eventStartDateObj);
            const googleEndDateStr = formatDateToYYYYMMDD(eventEndDateObj);

            const encodedEventName = encodeURIComponent(eventName);
            const eventDescription = encodeURIComponent(appState.eventData.DESCRIPCION || `Details for ${eventName}`);
            const eventLocation = encodeURIComponent(appState.eventData.LOCATION || '');

            const googleCalendarUrl = `https://www.google.com/calendar/render?action=TEMPLATE&text=${encodedEventName}&dates=${googleStartDateStr}/${googleEndDateStr}&details=${eventDescription}&location=${eventLocation}`;
            window.open(googleCalendarUrl, '_blank');
        });
    }

    if (addToAppleCalendarButton) {
        addToAppleCalendarButton.addEventListener('click', () => {
            if (!appState.eventData || !appState.eventData.FECHA || !appState.eventData.NAME) {
                alert("Event details are not fully loaded yet.");
                return;
            }

            const eventName = appState.eventData.NAME || 'Event';
            const eventDateStr = appState.eventData.FECHA; // "YYYY-MM-DD"
            const eventDescription = (appState.eventData.DESCRIPCION || `Details for ${eventName}`).replace(/\n/g, '\\n');
            const eventLocation = appState.eventData.LOCATION || '';

            const dateParts = eventDateStr.split('-');
            const eventStartDateObj = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
            
            const eventEndDateObj = new Date(eventStartDateObj);
            eventEndDateObj.setDate(eventStartDateObj.getDate() + 1);

            const toICSDate = (date) => {
                return date.getUTCFullYear() +
                       ('0' + (date.getUTCMonth() + 1)).slice(-2) +
                       ('0' + date.getUTCDate()).slice(-2) + 'T' +
                       ('0' + date.getUTCHours()).slice(-2) +
                       ('0' + date.getUTCMinutes()).slice(-2) +
                       ('0' + date.getUTCSeconds()).slice(-2) + 'Z';
            };

            const icsStartDateStr = formatDateToYYYYMMDD(eventStartDateObj);
            const icsEndDateStr = formatDateToYYYYMMDD(eventEndDateObj);

            const icsStamp = toICSDate(new Date());
            const uid = `event-${appState.eventId}-${Date.now()}@salypimientabanquetes.com`; // Unique ID

            const icsContent = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//SalYPimientaBanquetes//EventCalendar//EN',
                'BEGIN:VEVENT',
                `UID:${uid}`,
                `DTSTAMP:${icsStamp}`,
                `DTSTART;VALUE=DATE:${icsStartDateStr}`,
                `DTEND;VALUE=DATE:${icsEndDateStr}`,
                `SUMMARY:${eventName}`,
                `DESCRIPTION:${eventDescription}`,
                `LOCATION:${eventLocation}`,
                'END:VEVENT',
                'END:VCALENDAR'
            ].join('\r\n');

            const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${eventName.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_allday.ics`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        });
    }

    // Stage 2 (Setup) -> Stage 3 (Guest Info)
    toGuestInfoStageButton.addEventListener('click', async () => { 
        const email = coordinatorEmailInput.value.trim(); 
        if (!isValidEmail(email)) { 
            coordinatorEmailError.textContent = translations[appState.currentLanguage].errorInvalidEmail; 
            return; 
        } 
        coordinatorEmailError.textContent = ''; 
        appState.coordinatorEmail = email; 
        await fetchCoordinatorSelections(appState.coordinatorEmail); 
        showStage(3);
    });

    finalizeAndGoToSummaryButton.addEventListener('click', () => showStage(6)); // To Summary (Stage 6)

    showAddGuestFormButton.addEventListener('click', () => { 
        appState.currentGuestInternalId = null; 
        appState.isEditingGuest = false; 
        appState.forceShowNewGuestForm = true; // Set flag
        updateStage2View(); // This will now correctly show the input form and its button
    });

    selectMenuForGuestButton.addEventListener('click', async () => { 
        const name = guestNameInput.value.trim(); 
        let guestSpecificEmail = guestEmailInput.value.trim(); 
        let finalGuestEmail; let explicitlySetEmailFlag = false; 
        guestNameError.textContent = ''; guestEmailError.textContent = ''; 
        if (!name) { guestNameError.textContent = translations[appState.currentLanguage].errorGuestNameRequired; return; } 
        if (guestSpecificEmail) {
            if (!isValidEmail(guestSpecificEmail)) { guestEmailError.textContent = translations[appState.currentLanguage].errorInvalidEmail; return; } 
            finalGuestEmail = guestSpecificEmail; explicitlySetEmailFlag = true;
        } else { 
            finalGuestEmail = appState.coordinatorEmail; explicitlySetEmailFlag = false; 
        } 
        const existingClientGuest = appState.guests.find(g => g.name.toLowerCase() === name.toLowerCase() && g.internalId !== appState.currentGuestInternalId); 
        if (existingClientGuest) { guestNameError.textContent = translations[appState.currentLanguage].errorNameTaken; return; } 
        
        let checkDB = false; 
        if (!appState.isEditingGuest) { 
            checkDB = true; 
        } else { 
            const cGuest = findGuestByInternalId(appState.currentGuestInternalId); 
            if (cGuest && cGuest.name.toLowerCase() !== name.toLowerCase()) { checkDB = true; }
        } 
        if (checkDB) { 
            const nameExists = await checkGuestExistsDB(name); 
            if (nameExists) { guestNameError.textContent = translations[appState.currentLanguage].errorNameTaken; return; }
        }
        
        let guestToProcess; 
        if (appState.isEditingGuest && appState.currentGuestInternalId) {
            guestToProcess = findGuestByInternalId(appState.currentGuestInternalId); 
            if (guestToProcess) { 
                guestToProcess.name = name; guestToProcess.email = finalGuestEmail; 
                guestToProcess.explicitlySetEmail = explicitlySetEmailFlag; 
                guestToProcess.isSubmittedToDB = false; // Mark as changed, needs resubmission
            } else { return; }
        } else {
            const newId = generateInternalId(); 
            guestToProcess = { 
                internalId: newId, name: name, email: finalGuestEmail, 
                explicitlySetEmail: explicitlySetEmailFlag, 
                dietaryRestrictions: { allergies: '', type: '' }, 
                selections: {}, 
                isFromDB: false, isSubmittedToDB: false 
            }; 
            appState.guests.push(guestToProcess); 
            appState.currentGuestInternalId = newId; 
            appState.isEditingGuest = true; 
        } 
        populateDietaryStage(guestToProcess); 
        showStage(4); // To Dietary (Stage 4)
    });

    backToSetupStageButton.addEventListener('click', () => showStage(2)); // Back to Setup (Stage 2)

    function populateDietaryStage(guest) { 
        currentGuestNameForDietary.textContent = guest.name; 
        allergiesInput.value = guest.dietaryRestrictions.allergies || ''; 
        let dietTypeFound = false; 
        dietaryTypeRadios.forEach(radio => { 
            radio.checked = (radio.value === guest.dietaryRestrictions.type); 
            if (radio.checked) dietTypeFound = true; 
        }); 
        if (!dietTypeFound) { document.getElementById('dietNone').checked = true; }
    }

    toMenuSelectionStageButton.addEventListener('click', () => { // To Menu Selection (Stage 5)
        const guest = findGuestByInternalId(appState.currentGuestInternalId); if (!guest) return; 
        const oldDietType = guest.dietaryRestrictions.type; const oldAllergies = guest.dietaryRestrictions.allergies; 
        guest.dietaryRestrictions.allergies = allergiesInput.value.trim(); 
        guest.dietaryRestrictions.type = Array.from(dietaryTypeRadios).find(r => r.checked).value; 
        if (oldDietType !== guest.dietaryRestrictions.type || oldAllergies !== guest.dietaryRestrictions.allergies) { 
            guest.isSubmittedToDB = false; // Mark as changed if diet info changed
        } 
        populateMenuSelectionStage(guest); showStage(5);
    });

    backToGuestInfoStageButton.addEventListener('click', () => showStage(3)); // Back to Guest Info (Stage 3)

    function populateMenuSelectionStage(guest) { 
        currentGuestNameForMenu.textContent = guest.name; currentGuestEmailForMenu.textContent = guest.email; 
        menuCoursesContainer.innerHTML = ''; menuSelectionError.textContent = ''; 
        if (!appState.eventData || !appState.dishesData) { return; } 
        saveSelectionsButton.textContent = translations[appState.currentLanguage][appState.isEditingGuest ? 'updateSelectionsButton' : 'saveSelectionsButton']; 
        const courses = {}; 
        for (const key in appState.eventData) { 
            if (key.match(/^T\d+_O\d+$/) && appState.eventData[key] && !key.endsWith("_O0")) { 
                const cKey = key.split('_')[0]; 
                if (!courses[cKey]) courses[cKey] = []; 
                courses[cKey].push({ optionKey: key, dishId: appState.eventData[key] }); 
            }
        } 
        let courseCounter = 1; 
        for (const courseKey in courses) { 
            const section = document.createElement('div'); section.className = 'course-section'; 
            const titleTxt = (translations[appState.currentLanguage][`courseName_${courseKey}`]) || `${translations[appState.currentLanguage].courseDefaultName} ${courseCounter}`; 
            section.innerHTML = `<h3>${titleTxt}</h3>`; 
            courses[courseKey].forEach(opt => { 
                const dish = appState.dishesData[opt.dishId]; if (!dish) { return; } 
                let isCompatible = true; let incompatibleReason = ""; 
                if (guest.dietaryRestrictions.type === 'OPT_VEGETARIANO' && dish.OPT_VEGETARIANO !== 'on') isCompatible = false; 
                if (guest.dietaryRestrictions.type === 'OPT_VEGANO' && dish.OPT_VEGANO !== 'on') isCompatible = false; 
                if(!isCompatible) incompatibleReason = translations[appState.currentLanguage].incompatibleDish; 
                const optDiv = document.createElement('div'); optDiv.className = 'menu-option'; 
                if (!isCompatible) optDiv.classList.add('disabled-diet'); 
                optDiv.dataset.dishId = opt.dishId; optDiv.dataset.courseKey = courseKey; 
                const radioId = `g_${guest.internalId}_${courseKey}_${opt.dishId}`; const radioName = `g_${guest.internalId}_${courseKey}`; 
                let iconsHtml = '<span class="dietary-icons">';
                if (dish.OPT_VEGETARIANO === 'on') iconsHtml += `<span class="icon-vegetarian" title="Vegetarian"><i class="fas fa-leaf"></i> ${translations[appState.currentLanguage].vegetarianSymbol}</span>`; 
                if (dish.OPT_VEGANO === 'on') iconsHtml += `<span class="icon-vegan" title="Vegan"><i class="fas fa-seedling"></i> ${translations[appState.currentLanguage].veganSymbol}</span>`; 
                iconsHtml += '</span>'; 
                const imageSrc = dish.DISH_PIC && dish.DISH_PIC.trim() !== '' ? dish.DISH_PIC : 'images/generic-dish-placeholder.png';
                const isActualImage = dish.DISH_PIC && dish.DISH_PIC.trim() !== '' && imageSrc !== 'images/generic-dish-placeholder.png';

                optDiv.innerHTML = `
                    <input type="radio" name="${radioName}" id="${radioId}" value="${opt.dishId}" ${!isCompatible ? 'disabled' : ''} ${guest.selections[courseKey] === opt.dishId && isCompatible ? 'checked' : ''}>
                    <div class="menu-option-image-container">
                        <img src="${imageSrc}" alt="${getTranslatedDishProperty(dish.ID, 'NAME')}" class="menu-option-image" onerror="this.onerror=null; this.src='images/generic-dish-placeholder.png'; this.style.display='block'; const icon = this.nextElementSibling; if(icon && icon.classList.contains('menu-option-image-expand-icon')) icon.style.display='none';">
                        ${isActualImage ? '<span class="menu-option-image-expand-icon"><i class="fas fa-expand-alt"></i></span>' : ''}
                    </div>
                    <div class="menu-option-details"><h4>${getTranslatedDishProperty(dish.ID, 'NAME')} ${iconsHtml}</h4><p>${getTranslatedDishProperty(dish.ID, 'DESCRIPCION')} <span class="incompatible-reason">${incompatibleReason}</span></p></div>`;

                if (guest.selections[courseKey] === opt.dishId) { 
                    if (isCompatible) { optDiv.classList.add('selected'); }
                    else { delete guest.selections[courseKey]; guest.isSubmittedToDB = false; } 
                } 

                const radioInput = optDiv.querySelector('input[type="radio"]');

                if (isCompatible) {
                    optDiv.addEventListener('click', () => { 
                        const oldSelection = guest.selections[courseKey]; 
                        document.querySelectorAll(`.menu-option[data-course-key="${courseKey}"]`).forEach(el => el.classList.remove('selected')); 
                        optDiv.classList.add('selected'); optDiv.querySelector('input[type="radio"]').checked = true; 
                        if (oldSelection !== opt.dishId) guest.isSubmittedToDB = false; 
                    }); 
                } 

                const imgEl = optDiv.querySelector('.menu-option-image');
                if (isActualImage) { // Use the pre-calculated boolean
                    imgEl.style.cursor = 'pointer';
                    // Add event listener to the image itself for preview
                    imgEl.addEventListener('click', (e) => {
                        // Prevent the click from also selecting the radio button
                        e.stopPropagation(); modalImage.src = dish.DISH_PIC; 
                        modalCaption.textContent = getTranslatedDishProperty(dish.ID, 'NAME'); 
                        imagePreviewModal.style.display = 'flex'; 
                    });
                } else { 
                    imgEl.style.cursor = 'default'; 
                } 
                section.appendChild(optDiv);

                // Observe the image for lazy loading
                if (imageObserver && imgEl && imgEl.dataset.src) {
                    imageObserver.observe(imgEl);
                }
            }); 
            menuCoursesContainer.appendChild(section); courseCounter++;
        }
    }
    
    saveSelectionsButton.addEventListener('click', () => {
        const guest = findGuestByInternalId(appState.currentGuestInternalId); if (!guest) return;
        const oldSelectionsJSON = JSON.stringify(guest.selections); 
        guest.selections = {}; 
        let allSelected = true; 
        menuCoursesContainer.querySelectorAll('.course-section').forEach(section => {
            const firstOpt = section.querySelector('.menu-option:not(.disabled-diet)'); if (!firstOpt) return; 
            const cKey = firstOpt.dataset.courseKey;
            const selRadio = section.querySelector(`input[name="g_${guest.internalId}_${cKey}"]:checked`);
            if (selRadio) { guest.selections[cKey] = selRadio.value; } else { allSelected = false; }
        });
        if (!allSelected) { menuSelectionError.textContent = translations[appState.currentLanguage].errorCourseMissing; return; }
        menuSelectionError.textContent = '';
        if (JSON.stringify(guest.selections) !== oldSelectionsJSON) { guest.isSubmittedToDB = false; }
        appState.isEditingGuest = false; appState.currentGuestInternalId = null;
        showStage(3); // Back to Guest Info (Stage 3)
    });

    backToDietaryStageButton.addEventListener('click', () => { // Back to Dietary (Stage 4)
        const g = findGuestByInternalId(appState.currentGuestInternalId); 
        if (g) populateDietaryStage(g); 
        showStage(4); 
    });

    function populateSummaryStage() { 
        summaryListContainer.innerHTML = ''; 
        if (appState.guests.length === 0) { 
            summaryListContainer.innerHTML = `<p data-translate="noGuestsYet">${translations[appState.currentLanguage].noGuestsYet}</p>`; 
            updateNavButtonVisibility(); return; 
        } 
        appState.guests.forEach((guest, index) => { 
            const itemDiv = document.createElement('div'); itemDiv.className = 'summary-guest-item'; 
            let selHtml = '<ul>'; 
            if (Object.keys(guest.selections).length > 0) { 
                Object.entries(guest.selections).forEach(([cKey, dId]) => { 
                    const d = appState.dishesData[dId]; 
                    const dName = d ? getTranslatedDishProperty(d.ID, 'NAME') : '?'; 
                    const cTitle = (translations[appState.currentLanguage][`courseName_${cKey}`]) || `${translations[appState.currentLanguage].courseDefaultName} ${cKey.substring(1)}`; 
                    selHtml += `<li><strong>${cTitle}:</strong> ${dName}</li>`; 
                });
            } else { 
                selHtml += `<li data-translate="noSelectionsMadeYet">${translations[appState.currentLanguage].noSelectionsMadeYet}</li>`; 
            } 
            selHtml += '</ul>'; 
            let restHtml = ''; 
            if (guest.dietaryRestrictions.type) { 
                const dtKey = guest.dietaryRestrictions.type === 'OPT_VEGETARIANO' ? 'vegOption' : (guest.dietaryRestrictions.type === 'OPT_VEGANO' ? 'veganOption' : ''); 
                restHtml += `<p><strong>${translations[appState.currentLanguage].dietaryRestrictionsLabel}:</strong> ${translations[appState.currentLanguage][dtKey] || translations[appState.currentLanguage].noneOption}</p>`;
            } 
            if (guest.dietaryRestrictions.allergies) { 
                restHtml += `<p><strong>${translations[appState.currentLanguage].allergiesLabel}:</strong> ${guest.dietaryRestrictions.allergies}</p>`; 
            } 
            if (!guest.dietaryRestrictions.type && !guest.dietaryRestrictions.allergies) { 
                restHtml = `<p><strong>${translations[appState.currentLanguage].dietaryRestrictionsLabel}:</strong> ${translations[appState.currentLanguage].noneOption}</p>`; 
            } 
            const sendBtnDisabled = !guest.isSubmittedToDB; 
            const sendBtnText = translations[appState.currentLanguage].sendConfirmationButton; 
            const sendBtnTitleAttr = sendBtnDisabled ? translations[appState.currentLanguage].saveFirstTooltip : sendBtnText; 
            itemDiv.innerHTML = `
                <div class="actions-container">
                    <button class="nav-button edit-guest-button" data-guest-internal-id="${guest.internalId}">${translations[appState.currentLanguage].editButtonLabel}</button>
                    <button class="nav-button delete-guest-button danger-button" data-guest-internal-id="${guest.internalId}">${translations[appState.currentLanguage].deleteButtonLabel}</button>
                    <button class="nav-button send-confirmation-button" 
                            data-guest-internal-id="${guest.internalId}" 
                            title="${sendBtnTitleAttr}" 
                            ${sendBtnDisabled ? 'disabled' : ''}>${sendBtnText}</button>
                </div>
                <h4>${guest.name} (#${index + 1}) ${guest.isSubmittedToDB ? '<i class="fas fa-check-circle" style="color: #5e74b2; margin-left: 5px;" title="Submitted to Database"></i>' : ''}</h4>
                <p><strong>Email:</strong> ${guest.email}</p>
                ${restHtml}
                <p><strong>${translations[appState.currentLanguage].menuSelectionBaseTitle || 'Menu Selections'}:</strong></p>
                ${selHtml}`; 
            summaryListContainer.appendChild(itemDiv);
        });
        summaryListContainer.querySelectorAll('.edit-guest-button').forEach(b => b.addEventListener('click', (e) => { 
            const gId = e.target.dataset.guestInternalId; 
            const g = findGuestByInternalId(gId); 
            if (g) { 
                appState.currentGuestInternalId = gId; appState.isEditingGuest = true; 
                g.isSubmittedToDB = false; // Mark as needing re-submission if edited
                showStage(3); // To Guest Info (Stage 3)
            }
        }));
        summaryListContainer.querySelectorAll('.delete-guest-button').forEach(b => b.addEventListener('click', (e) => { 
            if (confirm(translations[appState.currentLanguage].confirmDeleteGuest)) { 
                const gId = e.target.dataset.guestInternalId;
                const guestToDelete = findGuestByInternalId(gId);
                const guestIndex = findGuestIndexByInternalId(gId);

                if (guestToDelete && guestIndex > -1) {
                    // If the guest was fetched from DB or previously submitted, try to delete from DB
                    if (guestToDelete.isFromDB || guestToDelete.isSubmittedToDB) {
                        loadingOverlay.style.display = 'flex'; // Show loading indicator
                        deleteGuestSelectionFromDB(appState.eventId, guestToDelete.name)
                            .then(apiResult => { // Expecting { success: true/false, message: '...' }
                                if (apiResult.success) {
                                    appState.guests.splice(guestIndex, 1);
                                    if (appState.currentGuestInternalId === gId) {
                                        appState.currentGuestInternalId = null;
                                        appState.isEditingGuest = false;
                                    }
                                    // No alert needed, summary will refresh and implicitly show success
                                } else {
                                    alert(apiResult.message || translations[appState.currentLanguage].guestDeletedErrorDB);
                                }
                            })
                            .catch(error => {
                                console.error("Error deleting guest selection:", error);
                                alert(translations[appState.currentLanguage].guestDeletedErrorNetwork);
                            })
                            .finally(() => {
                                loadingOverlay.style.display = 'none';
                                populateSummaryStage(); // Always re-render summary
                            });
                    } else {
                        // Guest was only client-side, just remove locally
                        appState.guests.splice(guestIndex, 1);
                        if (appState.currentGuestInternalId === gId) {
                            appState.currentGuestInternalId = null;
                            appState.isEditingGuest = false;
                        }
                        populateSummaryStage(); // Re-render summary
                        // No alert needed for local-only deletion
                    }
                }
            } // end confirm
        }));
        summaryListContainer.querySelectorAll('.send-confirmation-button').forEach(b => b.addEventListener('click', (e) => { 
            if (e.target.disabled) return; 
            const gId = e.target.dataset.guestInternalId; 
            const g = findGuestByInternalId(gId); 
            if (g) { sendGuestConfirmationEmail(g, false); } // false for suppressAlert
            else { alert("Could not find guest data."); }
        }));
        updateNavButtonVisibility();
    }

    prepareEmailButton.addEventListener('click', () => prepareCoordinatorSummaryEmail(appState.guests));
    submitAllButton.addEventListener('click', submitAllSelections);
    startOverButton.addEventListener('click', () => {
        if (confirm(translations[appState.currentLanguage].confirmStartOver)) {
            appState.coordinatorEmail = ''; appState.guests = []; 
            appState.currentGuestInternalId = null; appState.isEditingGuest = false;
            coordinatorEmailInput.value = ''; guestNameInput.value = ''; guestEmailInput.value = ''; 
            allergiesInput.value = ''; 
            if (document.getElementById('dietNone')) document.getElementById('dietNone').checked = true;
            menuCoursesContainer.innerHTML = ''; summaryListContainer.innerHTML = ''; 
            mailtoLinkContainer.innerHTML = ''; emailPreparedMessage.style.display = 'none'; emailServerNote.style.display = 'none';
            clearEventDetails(); // Set event details to placeholders
            coordinatorEmailError.textContent = ''; guestNameError.textContent = ''; guestEmailError.textContent = ''; menuSelectionError.textContent = '';
            // Clear setup stage message as well
            if (welcomeStageMessageElement.parentNode) welcomeStageMessageElement.parentNode.removeChild(welcomeStageMessageElement);
            if (toSetupStageButton) toSetupStageButton.disabled = false; // Re-enable next button on welcome stage
            showStage(1);
        }
    });
    backToGuestInfoFromSummaryButton.addEventListener('click', () => { // From Summary (Stage 6) back to Guest Info (Stage 3)
        appState.isEditingGuest = false; appState.currentGuestInternalId = null;
        showStage(3); 
    });

    // Stage 7: Thank You Stage Logic
    function populateThankYouStageDetails() {
        const placeholderName = translations[appState.currentLanguage].eventNamePlaceholder || 'Event Name'; // Assuming you might add these to translations
        const placeholderDate = translations[appState.currentLanguage].eventDateNotAvailable || 'Event Date'; // Fallback if specific translation keys aren't defined for this context
        
        const defaultEventNameIfDataMissing = "the event"; // Generic default if appState.eventData.NAME is missing
        const defaultEventDateIfDataMissing = "the date";   // Generic default if appState.eventData.FECHA is missing or invalid
        if (appState.eventData) {
            const eventName = appState.eventData.NAME || placeholderName;
            let eventDateDisplay = placeholderDate;
            if (appState.eventData.FECHA) {
                const dateParts = appState.eventData.FECHA.split('-');
                const eventDateObj = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                if (!isNaN(eventDateObj.getTime())) {
                    eventDateDisplay = eventDateObj.toLocaleDateString(
                        appState.currentLanguage === 'es' ? 'es-ES' : 'en-US',
                        { year: 'numeric', month: 'long', day: 'numeric' }
                    );
                }
            }
            if (thankYouEventNameElement) thankYouEventNameElement.textContent = eventName;
            if (thankYouEventDateElement) thankYouEventDateElement.textContent = eventDateDisplay;
        } else {
            if (thankYouEventNameElement) thankYouEventNameElement.textContent = placeholderName;
            if (thankYouEventDateElement) thankYouEventDateElement.textContent = placeholderDate;
        }
    }

    if (finishAndExitButton) {
        finishAndExitButton.addEventListener('click', () => {
            // Option 1: Go to a generic thank you page (if you have one)
            // window.location.href = "https://yourdomain.com/thankyou.html";

            // Option 2: "Close" the app by hiding the main container (simplistic)
            // appWrapper.style.display = 'none';
            // document.body.innerHTML = `<div style="text-align:center; padding: 50px; font-size: 1.5em;">Thank you! You can now close this page.</div>`;

            // Option 3: Go back to the initial welcome screen (stage 1)
            // This allows them to start over if they wish, but without clearing data yet.
            // For a full reset, they'd use the "Start Over" button on stage 6.
            showStage(1); 
            // Consider if you want to clear coordinatorEmail or guests here.
            // For now, it just navigates back. If a full reset is desired, call startOverButton's logic.
        });
    }

    // Modal Listeners
    closeModalButton.addEventListener('click', () => { imagePreviewModal.style.display = 'none'; });
    imagePreviewModal.addEventListener('click', (e) => { if (e.target === imagePreviewModal) { imagePreviewModal.style.display = 'none'; }});
    document.addEventListener('keydown', (e) => { if (e.key === "Escape" && imagePreviewModal.style.display === 'flex') { imagePreviewModal.style.display = 'none'; }});

    // Theme Toggle Logic
    function toggleTheme() { 
        document.body.classList.toggle('dark-theme'); 
        localStorage.setItem('theme', document.body.classList.contains('dark-theme') ? 'dark' : 'light'); 
    }
    function applySavedTheme() { 
        const savedTheme = localStorage.getItem('theme'); 
        if (savedTheme === 'dark') { document.body.classList.add('dark-theme'); } 
        else { document.body.classList.remove('dark-theme'); }
    }
    if (themeToggleButton) { 
        themeToggleButton.addEventListener('click', toggleTheme); 
    }
    
    // --- INITIALIZATION LOGIC (Handles Intro) ---
    // --- INITIALIZATION LOGIC ---
    function initializeAppLogic() {
        console.log("initializeAppLogic called"); 
        applySavedTheme(); // Apply theme before doing anything else that might depend on it
        updateLanguageToggleDisplay(); // Set initial text for language toggle
        updateProgressBar(); 
        updateNavButtonVisibility(); 
        translatePage(); 
        const welcomeStageEl = document.getElementById('stage1'); // The new welcome stage
        if (welcomeStageEl && appWrapper.classList.contains('visible')) {
             console.log("App wrapper is visible, showing welcome stage (Stage 1)."); 
             showStage(1); 
        } else if (welcomeStageEl) { 
            // This case implies appWrapper might not be visible yet, but we need to set initial stage for progress bar
            console.warn("App wrapper NOT YET visible when trying to show welcome stage (Stage 1), setting currentStage for progress bar."); 
            initializeImageObserver(); // Initialize observer even if appWrapper isn't visible yet
            appState.currentStage = 1; 
            updateProgressBar();
        } else { 
            console.error("Welcome Stage (Stage 1 container) not found!"); 
        }
    }

    // Fetch data first, then proceed with intro/app display logic
    fetchEventData().then((eventDateCheckOk) => { 
        console.log("Event data fetch complete. Date check passed:", eventDateCheckOk);
        initializeImageObserver(); // Ensure observer is initialized after data fetch
        // If eventDateCheckOk is false, Stage 1 will already show message and disable next button.
        // The app will initialize, and the user will see Stage 1 in its (potentially disabled) state.

        if(appWrapper) {
            appWrapper.classList.add('visible');
        }
        document.body.style.overflow = 'auto';
        
        initializeAppLogic(); // Initialize immediately

    }).catch(error => { 
        console.error("Critical error during initial data fetch sequence, cannot proceed with app init:", error);       
        // Even on critical error, still init app to show Stage 1 (welcome) with error message
        initializeAppLogic();

        // Display a critical error message to the user if appWrapper exists
        if(appWrapper) {
            appWrapper.innerHTML = `<div style="padding: 20px; text-align:center; color: #d32f2f;">
                                    <h2>Application Error</h2>
                                    <p>Could not load essential event data. Please check the event ID or try again later.</p>
                                 </div>`;
            appWrapper.classList.add('visible'); // Make sure wrapper is visible to show error
        }
        document.body.style.overflow = 'auto'; // Ensure scrollbars are restored even on critical error
        if(loadingOverlay) loadingOverlay.style.display = 'none'; // Hide loading overlay
    });
});