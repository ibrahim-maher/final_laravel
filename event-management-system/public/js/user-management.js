// public/js/user-management.js

/**
 * User Management JavaScript Functions
 * This file contains all JavaScript functionality for user management pages
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeUserManagement();
});

function initializeUserManagement() {
    // Initialize bulk selection functionality
    initializeBulkSelection();
    
    // Initialize form enhancements
    initializeFormEnhancements();
    
    // Initialize tooltips and other UI enhancements
    initializeUIEnhancements();
}

function initializeBulkSelection() {
    const selectAllCheckbox = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkActionSelect = document.getElementById('bulk-action-select');
    const eventSelection = document.getElementById('event-selection');

    if (!selectAllCheckbox || !userCheckboxes.length) {
        return; // Not on a page with bulk selection
    }

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Individual checkbox change
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateSelectedCount();
        });
    });

    // Bulk action change
    if (bulkActionSelect) {
        bulkActionSelect.addEventListener('change', function() {
            if (eventSelection) {
                if (this.value === 'assign_events' || this.value === 'remove_events') {
                    eventSelection.classList.remove('hidden');
                } else {
                    eventSelection.classList.add('hidden');
                }
            }
        });
    }

    function updateSelectAllState() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount === userCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < userCheckboxes.length;
    }

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        if (selectedCountSpan) {
            selectedCountSpan.textContent = checkedCount;
        }
        
        // Add selected user IDs to bulk form
        const bulkForm = document.getElementById('bulk-actions-form');
        if (bulkForm) {
            // Remove existing hidden inputs
            bulkForm.querySelectorAll('input[name="user_ids[]"]').forEach(input => input.remove());
            
            // Add new hidden inputs
            document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'user_ids[]';
                hiddenInput.value = checkbox.value;
                bulkForm.appendChild(hiddenInput);
            });
        }
    }

    // Bulk actions form submission with enhanced validation
    const bulkForm = document.getElementById('bulk-actions-form');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
            const action = bulkActionSelect ? bulkActionSelect.value : '';
            
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one user');
                return;
            }
            
            if (!action) {
                e.preventDefault();
                alert('Please select an action');
                return;
            }
            
            // Enhanced confirmation messages
            let confirmMessage = getConfirmMessage(action, selectedCount);
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return;
            }
        });
    }
}

function getConfirmMessage(action, selectedCount) {
    switch(action) {
        case 'delete':
            return `Are you sure you want to delete ${selectedCount} users? This action cannot be undone and will fail for users with existing registrations.`;
        case 'deactivate':
            return `Are you sure you want to deactivate ${selectedCount} users? This will prevent them from logging in.`;
        case 'activate':
            return `Are you sure you want to activate ${selectedCount} users?`;
        case 'assign_events':
            const selectedEvents = document.querySelectorAll('select[name="event_ids[]"] option:checked').length;
            if (selectedEvents === 0) {
                alert('Please select at least one event to assign');
                return false;
            }
            return `Are you sure you want to assign ${selectedEvents} events to ${selectedCount} users? This will only affect EVENT_MANAGER and USHER roles.`;
        case 'remove_events':
            const selectedEventsToRemove = document.querySelectorAll('select[name="event_ids[]"] option:checked').length;
            if (selectedEventsToRemove === 0) {
                alert('Please select at least one event to remove');
                return false;
            }
            return `Are you sure you want to remove ${selectedEventsToRemove} events from ${selectedCount} users?`;
        default:
            return `Are you sure you want to perform this action on ${selectedCount} users?`;
    }
}

function initializeFormEnhancements() {
    // Auto-submit form on filter changes for better UX
    document.querySelectorAll('select[name="per_page"], select[name="sort"], select[name="direction"]').forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });

    // Enhanced search functionality with debounce
    let searchTimeout;
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Auto-submit after 500ms of no typing
                if (this.value.length > 2 || this.value.length === 0) {
                    this.closest('form').submit();
                }
            }, 500);
        });
    }

    // Show loading state on form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }, 5000);
            }
        });
    });
}

function initializeUIEnhancements() {
    // Enhanced table row highlighting
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Bulk Actions Functions
function toggleBulkActions() {
    const panel = document.getElementById('bulk-actions-panel');
    if (!panel) return;
    
    panel.classList.toggle('hidden');
    
    if (!panel.classList.contains('hidden')) {
        // Reset selections when opening
        const selectAll = document.getElementById('select-all');
        if (selectAll) selectAll.checked = false;
        
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
        
        const selectedCount = document.getElementById('selected-count');
        if (selectedCount) selectedCount.textContent = '0';
        
        const bulkActionSelect = document.getElementById('bulk-action-select');
        if (bulkActionSelect) bulkActionSelect.value = '';
        
        const eventSelection = document.getElementById('event-selection');
        if (eventSelection) eventSelection.classList.add('hidden');
    }
}

// Event Assignment Modal Functions
function showAssignEventsModal(userId, userName) {
    const modal = document.getElementById('assign-events-modal');
    const form = document.getElementById('assign-events-form');
    
    if (!modal || !form) {
        console.error('Assign events modal or form not found');
        return;
    }
    
    // Set form action and modal title
    form.action = `/users/${userId}/assign-events`;
    const titleElement = modal.querySelector('h3');
    if (titleElement) {
        titleElement.textContent = `Assign Events to ${userName}`;
    }
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Focus on first checkbox for better UX
    setTimeout(() => {
        const firstCheckbox = modal.querySelector('input[type="checkbox"]');
        if (firstCheckbox) firstCheckbox.focus();
    }, 100);
}

function closeAssignEventsModal() {
    const modal = document.getElementById('assign-events-modal');
    if (!modal) return;
    
    modal.classList.add('hidden');
    
    // Reset form
    const form = document.getElementById('assign-events-form');
    if (form) {
        form.reset();
    }
}

// Modal Event Listeners
function initializeModalEventListeners() {
    // Close modal when clicking outside
    const modal = document.getElementById('assign-events-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAssignEventsModal();
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('assign-events-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeAssignEventsModal();
            }
        }
    });
}

// User Profile Functions
function initializeUserProfile() {
    // Initialize any user profile specific functionality
    console.log('User profile initialized');
}

// Notification Functions
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${getNotificationClasses(type)}`;
    notification.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                ${getNotificationIcon(type)}
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationClasses(type) {
    switch(type) {
        case 'success':
            return 'bg-green-100 border border-green-400 text-green-700';
        case 'error':
            return 'bg-red-100 border border-red-400 text-red-700';
        case 'warning':
            return 'bg-yellow-100 border border-yellow-400 text-yellow-700';
        case 'info':
            return 'bg-blue-100 border border-blue-400 text-blue-700';
        default:
            return 'bg-gray-100 border border-gray-400 text-gray-700';
    }
}

function getNotificationIcon(type) {
    switch(type) {
        case 'success':
            return '<i class="fas fa-check-circle text-green-400"></i>';
        case 'error':
            return '<i class="fas fa-exclamation-circle text-red-400"></i>';
        case 'warning':
            return '<i class="fas fa-exclamation-triangle text-yellow-400"></i>';
        case 'info':
            return '<i class="fas fa-info-circle text-blue-400"></i>';
        default:
            return '<i class="fas fa-bell text-gray-400"></i>';
    }
}

// Utility Functions
function confirmDelete(itemName = 'item') {
    return confirm(`Are you sure you want to delete this ${itemName}? This action cannot be undone.`);
}

function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(new Date(date));
}

// AJAX Helper Functions
function makeAjaxRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    };
    
    const mergedOptions = { ...defaultOptions, ...options };
    
    return fetch(url, mergedOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('AJAX request failed:', error);
            showNotification('Request failed. Please try again.', 'error');
            throw error;
        });
}

// Quick Actions
function quickActivateUser(userId) {
    makeAjaxRequest(`/users/${userId}/activate`, {
        method: 'POST'
    })
    .then(data => {
        showNotification('User activated successfully', 'success');
        location.reload();
    })
    .catch(error => {
        showNotification('Failed to activate user', 'error');
    });
}

function quickDeactivateUser(userId) {
    if (confirm('Are you sure you want to deactivate this user?')) {
        makeAjaxRequest(`/users/${userId}/deactivate`, {
            method: 'POST'
        })
        .then(data => {
            showNotification('User deactivated successfully', 'success');
            location.reload();
        })
        .catch(error => {
            showNotification('Failed to deactivate user', 'error');
        });
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeUserManagement();
    initializeModalEventListeners();
    initializeUserProfile();
    
    // Show success messages from server if any
    const successMessage = document.querySelector('[data-success-message]');
    if (successMessage) {
        showNotification(successMessage.dataset.successMessage, 'success');
    }
    
    // Show error messages from server if any
    const errorMessage = document.querySelector('[data-error-message]');
    if (errorMessage) {
        showNotification(errorMessage.dataset.errorMessage, 'error');
    }
});

// Export functions for global access
window.toggleBulkActions = toggleBulkActions;
window.showAssignEventsModal = showAssignEventsModal;
window.closeAssignEventsModal = closeAssignEventsModal;
window.showNotification = showNotification;
window.confirmDelete = confirmDelete;
window.quickActivateUser = quickActivateUser;
window.quickDeactivateUser = quickDeactivateUser;