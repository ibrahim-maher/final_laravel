// public/js/badge-printing.js
class BadgePrintingSystem {
    constructor() {
        this.apiUrl = '/api/badges';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.printWindows = new Map();
        this.isProcessing = false;
        
        this.init();
    }
    
    init() {
        console.log('Badge Printing System initialized');
        
        // Set up global functions for backward compatibility
        window.badgePrinting = this;
        window.printSingleBadge = (id) => this.printSingleBadge(id);
        window.previewBadge = (id) => this.previewBadge(id);
        window.printSelectedBadges = () => this.printSelectedBadges();
        window.generateMissingQrCodes = () => this.generateMissingQrCodes();
        
        // Clean up closed windows
        setInterval(() => this.cleanupClosedWindows(), 5000);
    }
    
    /**
     * Get CSRF token for requests
     */
    getCsrfToken() {
        return this.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }
    
    /**
     * Make API request with proper headers
     */
    async apiRequest(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                ...options.headers
            }
        };
        
        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Request failed' }));
            throw new Error(errorData.message || `HTTP ${response.status}`);
        }
        
        return response.json();
    }
    
    /**
     * Show notification to user
     */
    showNotification(message, type = 'info', duration = 5000) {
        const colors = {
            info: 'bg-blue-500',
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            error: 'bg-red-500'
        };

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 z-50 translate-x-full`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
            notification.classList.add('translate-x-0');
        }, 10);
        
        // Auto remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }
    
    /**
     * Show loading overlay
     */
    showLoading(message = 'Processing...') {
        const existingOverlay = document.getElementById('badge-loading-overlay');
        if (existingOverlay) existingOverlay.remove();
        
        const overlay = document.createElement('div');
        overlay.id = 'badge-loading-overlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        overlay.innerHTML = `
            <div class="bg-white rounded-lg p-6 flex items-center max-w-sm">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700">${message}</span>
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }
    
    /**
     * Hide loading overlay
     */
    hideLoading() {
        const overlay = document.getElementById('badge-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
    
    /**
     * Get selected registration IDs from checkboxes
     */
    getSelectedRegistrationIds() {
        const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.getAttribute('data-registration-id'));
    }
    
    /**
     * Print single badge
     */
    async printSingleBadge(registrationId) {
        try {
            const checkbox = document.querySelector(`[data-registration-id="${registrationId}"]`);
            if (checkbox && checkbox.getAttribute('data-can-print') !== 'true') {
                this.showNotification('This registration cannot be printed. Missing template or QR code.', 'warning');
                return;
            }
            
            const url = `/registrations/${registrationId}/print-badge`;
            const printWindow = window.open(url, '_blank', 'width=900,height=700,scrollbars=yes,resizable=yes');
            
            if (printWindow) {
                this.printWindows.set(`single-${registrationId}`, printWindow);
                this.showNotification('Opening badge for printing...', 'info', 2000);
            } else {
                this.showNotification('Please allow popups to print badges', 'warning');
            }
            
        } catch (error) {
            console.error('Print single badge error:', error);
            this.showNotification('Failed to print badge: ' + error.message, 'error');
        }
    }
    
    /**
     * Preview single badge
     */
    async previewBadge(registrationId) {
        try {
            const loading = this.showLoading('Loading badge preview...');
            
            const response = await this.apiRequest(`/registrations/${registrationId}/preview-badge`);
            
            this.hideLoading();
            
            // Create modal for preview
            this.showPreviewModal(response.html, response.template);
            
        } catch (error) {
            this.hideLoading();
            console.error('Preview badge error:', error);
            this.showNotification('Failed to preview badge: ' + error.message, 'error');
        }
    }
    
    /**
     * Show preview modal
     */
    showPreviewModal(html, template) {
        // Remove existing modal
        const existingModal = document.getElementById('badge-preview-modal');
        if (existingModal) existingModal.remove();
        
        const modal = document.createElement('div');
        modal.id = 'badge-preview-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-4xl max-h-screen overflow-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Badge Preview</h3>
                        <button onclick="this.closest('#badge-preview-modal').remove()" 
                                class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="badge-preview-content flex justify-center p-4 bg-gray-50 rounded">
                        ${html}
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600">Template: ${template.name || 'Unnamed'}</p>
                        <p class="text-sm text-gray-500">Size: ${template.width}cm × ${template.height}cm</p>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close on click outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function closeOnEscape(e) {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', closeOnEscape);
            }
        });
    }
    
    /**
     * Print selected badges
     */
    async printSelectedBadges() {
        try {
            if (this.isProcessing) {
                this.showNotification('Already processing badges...', 'warning');
                return;
            }
            
            const registrationIds = this.getSelectedRegistrationIds();
            
            if (registrationIds.length === 0) {
                this.showNotification('Please select registrations to print badges for', 'warning');
                return;
            }
            
            this.isProcessing = true;
            const loading = this.showLoading('Preparing badges for printing...');
            
            // Check printability first
            const printabilityCheck = await this.apiRequest('/registrations/check-badge-templates', {
                method: 'POST',
                body: JSON.stringify({ registration_ids: registrationIds })
            });
            
            const printableIds = printabilityCheck.results
                .filter(result => result.can_print)
                .map(result => result.registration_id);
            
            if (printableIds.length === 0) {
                this.hideLoading();
                this.isProcessing = false;
                this.showNotification('None of the selected registrations can be printed', 'warning');
                return;
            }
            
            if (printableIds.length < registrationIds.length) {
                const proceed = confirm(
                    `Only ${printableIds.length} out of ${registrationIds.length} registrations can be printed. Continue?`
                );
                if (!proceed) {
                    this.hideLoading();
                    this.isProcessing = false;
                    return;
                }
            }
            
            // Create form and submit for bulk printing
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/registrations/bulk-print-badges';
            form.target = '_blank';
            form.style.display = 'none';
            
            // Add CSRF token
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = this.getCsrfToken();
            form.appendChild(tokenInput);
            
            // Add registration IDs
            printableIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'registration_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            this.hideLoading();
            this.isProcessing = false;
            
            this.showNotification(`Opening ${printableIds.length} badges for printing...`, 'success');
            
        } catch (error) {
            this.hideLoading();
            this.isProcessing = false;
            console.error('Print selected badges error:', error);
            this.showNotification('Failed to print badges: ' + error.message, 'error');
        }
    }
    
    /**
     * Generate missing QR codes
     */
    async generateMissingQrCodes() {
        try {
            if (this.isProcessing) {
                this.showNotification('Already processing...', 'warning');
                return;
            }
            
            const registrationIds = this.getSelectedRegistrationIds();
            
            if (registrationIds.length === 0) {
                this.showNotification('Please select registrations to generate QR codes for', 'warning');
                return;
            }
            
            this.isProcessing = true;
            const loading = this.showLoading('Generating QR codes...');
            
            const response = await this.apiRequest('/registrations/generate-missing-qr-codes', {
                method: 'POST',
                body: JSON.stringify({ registration_ids: registrationIds })
            });
            
            this.hideLoading();
            this.isProcessing = false;
            
            if (response.generated > 0) {
                this.showNotification(response.message, 'success');
                
                // Refresh the page to show updated badge statuses
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                this.showNotification('No QR codes needed to be generated', 'info');
            }
            
        } catch (error) {
            this.hideLoading();
            this.isProcessing = false;
            console.error('Generate QR codes error:', error);
            this.showNotification('Failed to generate QR codes: ' + error.message, 'error');
        }
    }
    
    /**
     * Show badge template manager (placeholder for future feature)
     */
    showBadgeTemplateManager() {
        this.showNotification('Badge template manager coming soon!', 'info');
    }
    
    /**
     * Clean up closed print windows
     */
    cleanupClosedWindows() {
        for (const [key, window] of this.printWindows.entries()) {
            if (window.closed) {
                this.printWindows.delete(key);
            }
        }
    }
    
    /**
     * Close all print windows
     */
    closeAllPrintWindows() {
        for (const [key, window] of this.printWindows.entries()) {
            if (!window.closed) {
                window.close();
            }
        }
        this.printWindows.clear();
    }
    
    /**
     * Check if system is ready
     */
    isReady() {
        return !this.isProcessing && this.getCsrfToken() !== null;
    }
    
    /**
     * Get system status
     */
    getStatus() {
        return {
            ready: this.isReady(),
            processing: this.isProcessing,
            openWindows: this.printWindows.size,
            csrfToken: !!this.getCsrfToken()
        };
    }
}

// Initialize the badge printing system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.badgePrintingSystem = new BadgePrintingSystem();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BadgePrintingSystem;
}