// Toast Notification System
const toast = {
    show: (message, type = 'success') => {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="flex items-center">
                <span class="mr-2">${type === 'success' ? '✓' : '!'}</span>
                <p>${message}</p>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
};

// Form Validation
const validateForm = (form) => {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
            
            const errorMessage = input.dataset.error || 'This field is required';
            let errorDiv = input.nextElementSibling;
            
            if (!errorDiv || !errorDiv.classList.contains('error-message')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
            
            errorDiv.textContent = errorMessage;
        } else {
            input.classList.remove('border-red-500');
            const errorDiv = input.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.remove();
            }
        }
    });
    
    return isValid;
};

// Modal Management
const modal = {
    show: (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('modal');
            document.body.style.overflow = 'hidden';
        }
    },
    
    hide: (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('modal');
            document.body.style.overflow = '';
        }
    }
};



// Prayer Board Management System
const prayerBoard = {
    // Create a new envelope
    createEnvelope: async (boardId, data) => {
        try {
            // Form data preparation
            const formData = new URLSearchParams();
            formData.append('action', 'create_envelope');
            formData.append('board_id', boardId);
            formData.append('name', data.name);
            formData.append('color', data.color);
            
            // Make the request
            const response = await fetch('board.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned invalid response format');
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to create envelope');
            }
            
            return result;
        } catch (error) {
            console.error('Create envelope error:', error);
            throw new Error('Failed to create envelope. Please try again.');
        }
    },
    
    // Add a new prayer
    addPrayer: async (envelopeId, content) => {
        try {
            const formData = new URLSearchParams();
            formData.append('action', 'add_prayer');
            formData.append('envelope_id', envelopeId);
            formData.append('content', content);
            
            const response = await fetch('board.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned invalid response format');
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error('Failed to add prayer');
            }
            
            return result;
        } catch (error) {
            console.error('Add prayer error:', error);
            throw new Error('Failed to add prayer. Please try again.');
        }
    },
    
    // Mark a prayer as answered
    markAnswered: async (prayerId) => {
        try {
            const formData = new URLSearchParams();
            formData.append('action', 'mark_answered');
            formData.append('prayer_id', prayerId);
            
            const response = await fetch('board.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned invalid response format');
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error('Failed to mark prayer as answered');
            }
            
            return result;
        } catch (error) {
            console.error('Mark answered error:', error);
            throw new Error('Failed to mark prayer as answered. Please try again.');
        }
    }
};

// Form handling functions
async function saveEnvelope(event) {
    event.preventDefault();
    
    try {
        // Validate form
        if (!validateForm(event.target)) {
            return;
        }
        
        const name = document.getElementById('envelopeName').value;
        const color = document.getElementById('envelopeColor').value;
        
        // Get board ID from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const boardId = urlParams.get('id');
        
        // Log what we're sending
        console.log('Sending data:', {
            boardId,
            name,
            color
        });

        const formData = new URLSearchParams();
        formData.append('action', 'create_envelope');
        formData.append('board_id', boardId);
        formData.append('name', name);
        formData.append('color', color);

        const response = await fetch('board.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        });

        // Log the full response before trying to parse it
        const rawText = await response.text();
        console.log('Raw server response:', rawText);
        
        // Now we can see what the server is actually returning
        if (rawText.trim().startsWith('<!DOCTYPE')) {
            throw new Error('Server returned HTML instead of JSON. Session may have expired.');
        }

        const result = JSON.parse(rawText);
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to create envelope');
        }
        
        toast.show('Envelope created successfully');
        hideEnvelopeModal();
        window.location.reload();
    } catch (error) {
        toast.show(error.message, 'error');
        console.error('Full response error:', error);
    }
}

// Export functions for use in other scripts
window.prayerBoard = prayerBoard;
window.saveEnvelope = saveEnvelope;

// Drag and Drop Functionality
const initializeDragAndDrop = () => {
    const envelopes = document.querySelectorAll('.envelope-container');
    
    envelopes.forEach(envelope => {
        new Sortable(envelope.querySelector('.prayer-list'), {
            group: 'prayers',
            animation: 150,
            onEnd: async function(evt) {
                const prayerId = evt.item.dataset.id;
                const newEnvelopeId = evt.to.closest('.envelope-container').dataset.id;
                
                try {
                    const response = await fetch(`/api/prayers/${prayerId}/move`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ envelopeId: newEnvelopeId })
                    });
                    
                    if (!response.ok) throw new Error('Failed to move prayer');
                    
                    toast.show('Prayer moved successfully');
                } catch (error) {
                    toast.show(error.message, 'error');
                    // Revert the move
                    evt.from.appendChild(evt.item);
                }
            }
        });
    });
};

// Statistics and Analytics
const analytics = {
    updateStats: async (boardId) => {
        try {
            const response = await fetch(`/api/boards/${boardId}/stats`);
            if (!response.ok) throw new Error('Failed to fetch statistics');
            
            const stats = await response.json();
            
            // Update UI with statistics
            document.getElementById('total-prayers').textContent = stats.totalPrayers;
            document.getElementById('answered-prayers').textContent = stats.answeredPrayers;
            document.getElementById('answer-rate').textContent = `${stats.answerRate}%`;
            
            // Update chart if it exists
            if (window.prayerChart) {
                window.prayerChart.data = stats.chartData;
                window.prayerChart.update();
            }
        } catch (error) {
            toast.show('Failed to update statistics', 'error');
        }
    }
};

// Initialize all components
document.addEventListener('DOMContentLoaded', () => {
    // Initialize form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });

    // Initialize drag and drop
    initializeDragAndDrop();

    // Set up real-time updates for answered prayers
    const answeredPrayers = document.querySelectorAll('.prayer-item[data-answered="true"]');
    answeredPrayers.forEach(prayer => {
        prayer.classList.add('answered');
    });

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip absolute bg-gray-800 text-white px-2 py-1 rounded text-sm';
            tooltip.textContent = e.target.dataset.tooltip;
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;
            tooltip.style.left = `${rect.left + (rect.width - tooltip.offsetWidth) / 2}px`;
        });
        
        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });

    // Initialize auto-save for text inputs
    const autoSaveInputs = document.querySelectorAll('[data-autosave]');
    autoSaveInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(async () => {
                try {
                    const response = await fetch(input.dataset.autosave, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ value: input.value })
                    });
                    
                    if (!response.ok) throw new Error('Failed to save');
                    
                    toast.show('Changes saved', 'success');
                } catch (error) {
                    toast.show('Failed to save changes', 'error');
                }
            }, 1000);
        });
    });

    // Check for unsaved changes before leaving page
    const trackChanges = document.querySelectorAll('[data-track-changes]');
    let hasUnsavedChanges = false;
    
    trackChanges.forEach(element => {
        element.addEventListener('input', () => {
            hasUnsavedChanges = true;
        });
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

// Export functions for use in other scripts
window.prayerBoard = prayerBoard;
window.toast = toast;
window.modal = modal;
window.analytics = analytics;