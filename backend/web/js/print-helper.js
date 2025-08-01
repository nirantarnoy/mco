/**
 * Print Helper Functions
 */
class PrintHelper {

    /**
     * Print document with custom options
     */
    static printDocument(options = {}) {
        const defaultOptions = {
            showPrintDialog: true,
            closeAfterPrint: false,
            autoPrint: false
        };

        const settings = Object.assign(defaultOptions, options);

        // Apply print styles
        this.applyPrintStyles();

        if (settings.autoPrint) {
            window.print();
        }

        if (settings.closeAfterPrint) {
            window.onafterprint = function() {
                window.close();
            };
        }
    }

    /**
     * Apply print-specific styles
     */
    static applyPrintStyles() {
        const printStyles = `
            <style id="print-styles">
                @media print {
                    .no-print { display: none !important; }
                    .print-only { display: block !important; }
                    body { margin: 0; padding: 0; }
                    @page { margin: 0.5in; }
                }
            </style>
        `;

        // Remove existing print styles
        const existingStyles = document.getElementById('print-styles');
        if (existingStyles) {
            existingStyles.remove();
        }

        // Add new print styles
        document.head.insertAdjacentHTML('beforeend', printStyles);
    }

    /**
     * Preview print layout
     */
    static previewPrint() {
        this.applyPrintStyles();
        document.body.classList.add('print-preview');

        // Create preview controls
        const previewControls = `
            <div class="print-preview-controls no-print" style="
                position: fixed;
                top: 10px;
                right: 10px;
                z-index: 9999;
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 10px;
                border-radius: 5px;
            ">
                <button onclick="PrintHelper.printDocument()" style="margin-right: 5px;">Print</button>
                <button onclick="PrintHelper.exitPreview()">Exit Preview</button>
            </div>
        `;

        document.body.insertAdjacentHTML('afterbegin', previewControls);
    }

    /**
     * Exit print preview
     */
    static exitPreview() {
        document.body.classList.remove('print-preview');
        const controls = document.querySelector('.print-preview-controls');
        if (controls) {
            controls.remove();
        }
    }

    /**
     * Save as PDF (modern browsers)
     */
    static saveAsPDF(filename = 'document.pdf') {
        if (window.chrome) {
            // Chrome-specific print to PDF
            window.print();
        } else {
            // Fallback for other browsers
            alert('กรุณาใช้ฟังก์ชัน Print และเลือก "Save as PDF" ในการบันทึกเป็น PDF');
            window.print();
        }
    }
}

// Auto-initialize print helpers when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+P for print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            PrintHelper.printDocument();
        }

        // Ctrl+Shift+P for print preview
        if (e.ctrlKey && e.shiftKey && e.key === 'P') {
            e.preventDefault();
            PrintHelper.previewPrint();
        }
    });
});