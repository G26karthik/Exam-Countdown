// 🚀 MyExamTrack - Enhanced JavaScript for Viral-Ready Features
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all features
    initializeCountdowns();
    checkExamAlerts();
    setupEventListeners();
    showNotifications();
    initializeAnimations();
    setupKeyboardShortcuts();
    
    // Update countdowns every second
    setInterval(updateCountdowns, 1000);
    
    // Check for alerts every 30 seconds
    setInterval(checkExamAlerts, 30000);
    
    // Update next exam countdown in stats
    setInterval(updateNextExamCountdown, 1000);
});

// Initialize animations and visual effects
function initializeAnimations() {
    // Add entrance animations to cards
    const cards = document.querySelectorAll('.modern-exam-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-fade-in');
    });
    
    // Add hover effects to interactive elements
    setupHoverEffects();
}

// Setup keyboard shortcuts for power users
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N: Add new exam
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const addModal = new bootstrap.Modal(document.getElementById('addExamModal'));
            addModal.show();
        }
        
        // Escape: Close any open modal
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
            });
        }
        
        // F5 or Ctrl+R: Refresh page (allowed)
        if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key === 'r')) {
            // Allow default refresh behavior
            return true;
        }
    });
}

// Enhanced countdown update with visual effects
function updateCountdown(examId, examDateTime) {
    const countdownElement = document.getElementById(`countdown-${examId}`);
    if (!countdownElement) return;
    
    const now = new Date().getTime();
    const examTime = new Date(examDateTime).getTime();
    const timeDiff = examTime - now;
    
    if (timeDiff <= 0) {
        countdownElement.innerHTML = '<div class="exam-passed"><i class="fas fa-check-circle"></i><span>Exam has passed</span></div>';
        updateCardUrgency(examId, 'passed');
        return;
    }
    
    const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
    
    // Update countdown display with enhanced HTML
    const timeUnitsContainer = countdownElement.querySelector('.time-units');
    if (timeUnitsContainer) {
        timeUnitsContainer.innerHTML = `
            <div class="time-unit">
                <span class="time-number">${days}</span>
                <span class="time-label">Days</span>
            </div>
            <div class="time-unit">
                <span class="time-number">${hours}</span>
                <span class="time-label">Hours</span>
            </div>
            <div class="time-unit">
                <span class="time-number">${minutes}</span>
                <span class="time-label">Mins</span>
            </div>
            <div class="time-unit">
                <span class="time-number">${seconds}</span>
                <span class="time-label">Secs</span>
            </div>
        `;
    }
    
    // Determine urgency level
    const totalSeconds = Math.floor(timeDiff / 1000);
    let urgencyLevel = 'normal';
    
    if (totalSeconds <= 600) { // 10 minutes
        urgencyLevel = 'flash';
    } else if (totalSeconds <= 3600) { // 1 hour
        urgencyLevel = 'critical';
    } else if (totalSeconds <= 86400) { // 24 hours
        urgencyLevel = 'urgent';
    } else if (totalSeconds <= 259200) { // 3 days
        urgencyLevel = 'moderate';
    }
    
    updateCardUrgency(examId, urgencyLevel);
    
    // Add pulsing effect for very urgent exams
    const card = document.querySelector(`[data-exam-id="${examId}"]`);
    if (card) {
        if (totalSeconds <= 600) { // 10 minutes - add flash animation
            card.classList.add('flash-urgent');
        } else {
            card.classList.remove('flash-urgent');
        }
        
        if (totalSeconds <= 3600) { // 1 hour - add pulse animation
            card.classList.add('pulse-urgent');
        } else {
            card.classList.remove('pulse-urgent');
        }
    }
}

// Update next exam countdown in dashboard stats
function updateNextExamCountdown() {
    const nextExamElement = document.getElementById('next-exam-countdown');
    if (!nextExamElement) return;
    
    // Find the next exam card
    const examCards = document.querySelectorAll('.modern-exam-card:not(.urgency-passed)');
    let nextExam = null;
    let earliestTime = null;
    
    examCards.forEach(card => {
        const examDateTime = card.getAttribute('data-exam-datetime');
        const examTime = new Date(examDateTime).getTime();
        const now = new Date().getTime();
        
        if (examTime > now && (earliestTime === null || examTime < earliestTime)) {
            earliestTime = examTime;
            nextExam = {
                datetime: examDateTime,
                subject: card.querySelector('.subject-name').textContent.replace('📘 ', '')
            };
        }
    });
    
    if (nextExam) {
        const now = new Date().getTime();
        const timeDiff = earliestTime - now;
        const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        
        nextExamElement.innerHTML = `${days}d ${hours}h`;
    }
}

// Enhanced alert system with better notifications
function checkExamAlerts() {
    const examCards = document.querySelectorAll('.modern-exam-card');
    const alerts = [];
    
    examCards.forEach(card => {
        const examDateTime = card.getAttribute('data-exam-datetime');
        const examId = card.getAttribute('data-exam-id');
        const subject = card.querySelector('.subject-name').textContent.replace('📘 ', '');
        
        const now = new Date().getTime();
        const examTime = new Date(examDateTime).getTime();
        const timeDiff = examTime - now;
        const totalSeconds = Math.floor(timeDiff / 1000);
        
        // Check for different alert levels
        if (totalSeconds > 0) {
            if (totalSeconds <= 600 && !card.dataset.flashAlertShown) { // 10 minutes
                alerts.push({
                    type: 'flash',
                    subject: subject,
                    time: '10 minutes',
                    icon: 'fas fa-bolt',
                    color: 'danger'
                });
                card.dataset.flashAlertShown = 'true';
            } else if (totalSeconds <= 3600 && !card.dataset.criticalAlertShown) { // 1 hour
                alerts.push({
                    type: 'critical',
                    subject: subject,
                    time: '1 hour',
                    icon: 'fas fa-exclamation-triangle',
                    color: 'warning'
                });
                card.dataset.criticalAlertShown = 'true';
            } else if (totalSeconds <= 86400 && !card.dataset.urgentAlertShown) { // 24 hours
                alerts.push({
                    type: 'urgent',
                    subject: subject,
                    time: '24 hours',
                    icon: 'fas fa-clock',
                    color: 'info'
                });
                card.dataset.urgentAlertShown = 'true';
            }
        }
    });
    
    // Show alerts
    alerts.forEach(alert => {
        showExamAlert(alert);
    });
}

// Enhanced alert display with toast notifications
function showExamAlert(alert) {
    // Create toast notification
    const toastContainer = getOrCreateToastContainer();
    const toastId = `toast-${Date.now()}`;
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-bg-${alert.color} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${alert.icon} me-2"></i>
                    <strong>${alert.subject}</strong> exam in ${alert.time}!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: alert.type === 'flash' ? 10000 : 5000 // Flash alerts stay longer
    });
    
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
    
    // Play notification sound for critical alerts
    if (alert.type === 'flash' || alert.type === 'critical') {
        playNotificationSound();
    }
}

// Get or create toast container
function getOrCreateToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    return container;
}

// Play notification sound
function playNotificationSound() {
    // Create a subtle notification sound using Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
        // Fallback: no sound if Web Audio API is not available
        console.log('Audio notification not available');
    }
}

// Enhanced hover effects
function setupHoverEffects() {
    // Add hover effect to exam cards
    const cards = document.querySelectorAll('.modern-exam-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.transition = 'all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add floating animation to add button
    const floatingBtn = document.querySelector('.floating-add-btn');
    if (floatingBtn) {
        floatingBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(90deg)';
        });
        
        floatingBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    }
}

// Enhanced form validation with real-time feedback
function setupFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                validateInput(this);
            });
            
            input.addEventListener('blur', function() {
                validateInput(this);
            });
        });
    });
}

// Real-time input validation
function validateInput(input) {
    const isValid = input.checkValidity();
    
    // Remove existing validation classes
    input.classList.remove('is-valid', 'is-invalid');
    
    // Add appropriate validation class
    if (input.value.trim() !== '') {
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');
    }
    
    // Special validation for datetime inputs
    if (input.type === 'datetime-local' && input.value) {
        const selectedDate = new Date(input.value);
        const now = new Date();
        
        if (selectedDate <= now) {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            
            // Show custom error message
            let errorDiv = input.parentNode.querySelector('.invalid-feedback');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                input.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = 'Exam date must be in the future!';
        }
    }
}

// Enhanced subject autocomplete
function setupSubjectAutocomplete() {
    const subjectInput = document.querySelector('input[name="subject"]');
    if (!subjectInput) return;
    
    // Get existing subjects from the filter dropdown
    const subjectOptions = document.querySelectorAll('#subject option');
    const subjects = Array.from(subjectOptions)
        .map(option => option.value)
        .filter(value => value !== '');
    
    // Create datalist for autocomplete
    if (subjects.length > 0) {
        let datalist = document.getElementById('subjects-datalist');
        if (!datalist) {
            datalist = document.createElement('datalist');
            datalist.id = 'subjects-datalist';
            document.body.appendChild(datalist);
        }
        
        // Populate datalist
        datalist.innerHTML = subjects.map(subject => 
            `<option value="${subject}">`
        ).join('');
        
        // Link input to datalist
        subjectInput.setAttribute('list', 'subjects-datalist');
    }
}

// Initialize all countdown timers
function initializeCountdowns() {
    const examCards = document.querySelectorAll('.modern-exam-card');
    examCards.forEach(card => {
        const examDateTime = card.getAttribute('data-exam-datetime');
        const examId = card.getAttribute('data-exam-id');
        updateCountdown(examId, examDateTime);
    });
}

// Update all countdowns
function updateCountdowns() {
    const examCards = document.querySelectorAll('.modern-exam-card');
    examCards.forEach(card => {
        const examDateTime = card.getAttribute('data-exam-datetime');
        const examId = card.getAttribute('data-exam-id');
        updateCountdown(examId, examDateTime);
    });
}

// Update card urgency styling with enhanced effects
function updateCardUrgency(examId, urgencyLevel) {
    const card = document.querySelector(`[data-exam-id="${examId}"]`);
    if (!card) return;
    
    // Remove all urgency classes
    card.classList.remove('urgency-normal', 'urgency-moderate', 'urgency-urgent', 'urgency-critical', 'urgency-flash', 'urgency-passed');
    
    // Add new urgency class
    card.classList.add(`urgency-${urgencyLevel}`);
    
    // Add special effects for critical exams
    if (urgencyLevel === 'flash') {
        card.classList.add('flash-animation');
    } else {
        card.classList.remove('flash-animation');
    }
}

// Setup enhanced event listeners
function setupEventListeners() {
    // Enhanced delete confirmation
    window.deleteExam = function(examId) {
        // Get exam subject for confirmation
        const card = document.querySelector(`[data-exam-id="${examId}"]`);
        const subject = card ? card.querySelector('.subject-name').textContent.replace('📘 ', '') : 'this exam';
        
        // Create custom confirmation modal
        const confirmHTML = `
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                            </h5>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to delete the exam:</p>
                            <div class="alert alert-light">
                                <i class="fas fa-book me-2"></i>
                                <strong>${subject}</strong>
                            </div>
                            <p class="text-muted mb-0">This action cannot be undone!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete(${examId})">
                                <i class="fas fa-trash me-2"></i>Delete Exam
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('deleteConfirmModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add new modal to body
        document.body.insertAdjacentHTML('beforeend', confirmHTML);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
        
        // Remove modal from DOM when hidden
        document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    };
    
    // Confirm delete function
    window.confirmDelete = function(examId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete_exam.php';
        form.style.display = 'none';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id';
        input.value = examId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    };
    
    // Enhanced edit exam function
    window.editExam = function(examId) {
        fetch(`edit_exam.php?id=${examId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showToast('Error loading exam data', 'danger');
                    return;
                }                  // Populate edit form
                document.getElementById('edit_exam_id').value = data.id;
                document.getElementById('edit_subject').value = data.subject;
                document.getElementById('edit_exam_datetime').value = data.exam_datetime;
                document.getElementById('edit_semester').value = data.semester;
                document.getElementById('edit_exam_type').value = data.exam_type;
                document.getElementById('edit_priority').value = data.priority;
                
                // Show edit modal
                const editModal = new bootstrap.Modal(document.getElementById('editExamModal'));
                editModal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error loading exam data', 'danger');
            });
    };
    
    // Setup form validation
    setupFormValidation();
    
    // Setup subject autocomplete
    setupSubjectAutocomplete();
    
    // Add smooth scrolling to top when floating button is used
    const floatingBtn = document.querySelector('.floating-add-btn');
    if (floatingBtn) {
        floatingBtn.addEventListener('click', function() {
            // Smooth scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Show toast notification
function showToast(message, type = 'info') {
    const toastContainer = getOrCreateToastContainer();
    const toastId = `toast-${Date.now()}`;
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Show notifications from URL parameters
function showNotifications() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Success messages
    if (urlParams.get('success') === 'exam_added') {
        showToast('🎉 Exam added successfully!', 'success');
    } else if (urlParams.get('success') === 'exam_updated') {
        showToast('✏️ Exam updated successfully!', 'success');
    } else if (urlParams.get('success') === 'exam_deleted') {
        showToast('🗑️ Exam deleted successfully!', 'success');
    }
    
    // Error messages
    if (urlParams.get('error') === 'missing_fields') {
        showToast('❌ Please fill in all required fields', 'danger');
    } else if (urlParams.get('error') === 'past_date') {
        showToast('⏰ Exam date must be in the future', 'warning');
    } else if (urlParams.get('error') === 'duplicate_exam') {
        showToast('⚠️ An exam with the same subject and time already exists', 'warning');
    } else if (urlParams.get('error') === 'database_error') {
        showToast('💥 Database error occurred. Please try again.', 'danger');
    } else if (urlParams.get('error') === 'unauthorized') {
        showToast('🔒 You can only edit your own exams', 'danger');
    }
    
    // Clean URL after showing notifications
    if (urlParams.has('success') || urlParams.has('error')) {
        const cleanUrl = window.location.href.split('?')[0];
        window.history.replaceState({}, document.title, cleanUrl);
    }
}

// Enhanced performance optimization
// Debounced resize handler
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        // Recalculate layouts if needed
        setupHoverEffects();
    }, 250);
});

// Lazy loading for non-critical features
function initializeLazyFeatures() {
    // Initialize features that aren't immediately needed
    setTimeout(() => {
        setupSubjectAutocomplete();
    }, 1000);
}

// Call lazy features
initializeLazyFeatures();


// Check for exam alerts
function checkExamAlerts() {
    const examCards = document.querySelectorAll('.exam-card');
    const alertsToShow = [];
    
    examCards.forEach(card => {
        const examDateTime = card.getAttribute('data-exam-datetime');
        const subject = card.querySelector('.card-title').textContent;
        
        const now = new Date().getTime();
        const examTime = new Date(examDateTime).getTime();
        const timeDiff = examTime - now;
        const totalSeconds = Math.floor(timeDiff / 1000);
        
        // Check for 1 hour alert
        if (totalSeconds > 0 && totalSeconds <= 3600) {
            alertsToShow.push({
                type: 'critical',
                subject: subject,
                message: `Your ${subject} exam is in less than 1 hour!`,
                time: formatTimeRemaining(totalSeconds)
            });
        }
        // Check for 24 hour alert
        else if (totalSeconds > 3600 && totalSeconds <= 86400) {
            alertsToShow.push({
                type: 'warning',
                subject: subject,
                message: `Your ${subject} exam is within 24 hours!`,
                time: formatTimeRemaining(totalSeconds)
            });
        }
    });
    
    // Show alerts if any
    if (alertsToShow.length > 0) {
        showExamAlerts(alertsToShow);
    }
}

// Format time remaining for alerts
function formatTimeRemaining(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    
    if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} and ${minutes} minute${minutes > 1 ? 's' : ''}`;
    } else {
        return `${minutes} minute${minutes > 1 ? 's' : ''}`;
    }
}

// Show exam alerts
function showExamAlerts(alerts) {
    const alertModalBody = document.getElementById('alertModalBody');
    let alertContent = '<div class="alert-list">';
    
    alerts.forEach(alert => {
        const iconClass = alert.type === 'critical' ? 'fas fa-exclamation-circle text-danger' : 'fas fa-clock text-warning';
        alertContent += `
            <div class="alert alert-${alert.type === 'critical' ? 'danger' : 'warning'} mb-2">
                <i class="${iconClass} me-2"></i>
                <strong>${alert.message}</strong><br>
                <small>Time remaining: ${alert.time}</small>
            </div>
        `;
    });
    
    alertContent += '</div>';
    alertModalBody.innerHTML = alertContent;
    
    // Show the modal
    const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
    alertModal.show();
}





// Setup event listeners
function setupEventListeners() {
    // Form validation for add exam
    const addForm = document.querySelector('#addExamModal form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const datetime = this.querySelector('[name="exam_datetime"]').value;
            const now = new Date();
            const examDate = new Date(datetime);
            
            if (examDate <= now) {
                e.preventDefault();
                showNotification('Exam date must be in the future', 'danger');
            }
        });
    }
    
    // Form validation for edit exam
    const editForm = document.querySelector('#editExamModal form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const datetime = this.querySelector('[name="exam_datetime"]').value;
            const now = new Date();
            const examDate = new Date(datetime);
            
            if (examDate <= now) {
                e.preventDefault();
                showNotification('Exam date must be in the future', 'danger');
            }
        });
    }
    
    // Auto-dismiss notifications
    setTimeout(() => {
        const notifications = document.querySelectorAll('.notification .alert');
        notifications.forEach(notification => {
            if (notification.classList.contains('alert-dismissible')) {
                const closeBtn = notification.querySelector('.btn-close');
                if (closeBtn) closeBtn.click();
            }
        });
    }, 5000);
}

// Show notifications based on URL parameters
function showNotifications() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const success = urlParams.get('success');
        let message = '';
        
        switch (success) {
            case 'exam_added':
                message = 'Exam added successfully!';
                break;
            case 'exam_updated':
                message = 'Exam updated successfully!';
                break;
            case 'exam_deleted':
                message = 'Exam deleted successfully!';
                break;
        }
        
        if (message) {
            showNotification(message, 'success');
        }
    }
    
    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        let message = '';
        
        switch (error) {
            case 'missing_fields':
                message = 'Please fill in all required fields.';
                break;
            case 'past_date':
                message = 'Exam date must be in the future.';
                break;
            case 'database_error':
                message = 'Database error occurred. Please try again.';
                break;
            case 'exam_not_found':
                message = 'Exam not found.';
                break;
            case 'missing_id':
                message = 'Invalid exam ID.';
                break;
            default:
                message = 'An error occurred. Please try again.';
        }
        
        showNotification(message, 'danger');
    }
    
    // Clean up URL
    if (urlParams.has('success') || urlParams.has('error')) {
        const cleanUrl = window.location.pathname + window.location.search.replace(/[?&](success|error)=[^&]*/g, '').replace(/^&/, '?').replace(/\?$/, '');
        window.history.replaceState({}, '', cleanUrl);
    }
}

// Show notification function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <strong>${type === 'success' ? 'Success!' : type === 'danger' ? 'Error!' : 'Info!'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const closeBtn = notification.querySelector('.btn-close');
        if (closeBtn) closeBtn.click();
    }, 5000);
}

// Utility function to debounce function calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Smooth scroll to top function
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Add scroll to top button
document.addEventListener('scroll', debounce(() => {
    const scrollBtn = document.getElementById('scrollToTop');
    if (window.pageYOffset > 300) {
        if (!scrollBtn) {
            const btn = document.createElement('button');
            btn.id = 'scrollToTop';
            btn.className = 'btn btn-primary position-fixed';
            btn.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px;';
            btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            btn.onclick = scrollToTop;
            document.body.appendChild(btn);
        }
    } else if (scrollBtn) {
        scrollBtn.remove();
    }
}, 100));

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + N to add new exam
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        const addBtn = document.querySelector('[data-bs-target="#addExamModal"]');
        if (addBtn) addBtn.click();
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) modalInstance.hide();
        });
    }
});

// Service worker registration for offline support (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // You can uncomment this if you want to add a service worker later
        // navigator.serviceWorker.register('/sw.js');
    });
}
