<?php
/**
 * The template for displaying the acknowledgement slip.
 *
 * This template is used by the 'reunion_acknowledgement_slip' shortcode.
 * It handles both searching for a registration and displaying the slip.
 *
 * @package Reunion_Registration
 */

// Don't access this file directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
    .acknowledgement-container {
        max-width: 800px;
        margin: 20px auto;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        background: #f0f2f5;
        padding: 20px;
        border-radius: 8px;
    }
    .search-box {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
    }
    .search-box h3 {
        margin-top: 0;
    }
    .search-box form {
        display: flex;
        gap: 10px;
    }
    .search-box input[type="text"] {
        flex-grow: 1;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .search-box button {
        padding: 10px 20px;
        border: none;
        background-color: #0073aa;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }
    .slip-wrapper {
        border: 1px solid #ddd;
        box-shadow: 0 0 15px rgba(0,0,0,0.07);
        padding: 20px;
        border-radius: 10px;
        background: #fff;
    }
    .slip-header {
        text-align: center;
        padding-bottom: 10px;
        margin-bottom: 15px;
        border-bottom: 2px dashed #ccc;
    }
    .slip-header .logo {
        max-width: 150px;
        max-height: 100px;
        margin-bottom: 15px;
    }
    .slip-profile-pic {
        border: 3px solid #ddd;
        padding: 4px;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        object-fit: cover;
    }
    .no-profile-pic {
        border: 3px solid #ddd;
        padding: 4px;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 14px;
        text-align: center;
        margin: 0 auto;
    }
    .slip-details-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .slip-details-table td {
        padding: 12px;
        border: 1px solid #eee;
    }
    .slip-details-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .slip-details-table td:first-child {
        font-weight: bold;
        width: 30%;
    }
    .slip-footer {
        text-align: center;
        margin-top: 30px;
        font-style: italic;
        color: #555;
        border-top: 1px solid #ccc;
        padding-top: 10px;
    }
    .slip-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
        padding: 10px 0;
    }
    .slip-buttons button {
        padding: 12px 25px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
        border: none;
        color: #fff;
        transition: background-color 0.2s ease-in-out;
    }
    .print-btn {
        background: #0073aa;
    }
    .print-btn:hover {
        background: #005a87;
    }
    .pdf-btn {
        background: #d63638;
    }
    .pdf-btn:hover {
        background: #b02a2c;
    }
    .pdf-btn:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }
    .no-record {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border: 1px solid #f5c6cb;
        border-radius: 5px;
        text-align: center;
    }
    
    .pdf-loading {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    
    @media print {
        body, .acknowledgement-container {
            margin: 0;
            padding: 0;
            background: #fff;
        }
        body * {
            visibility: hidden;
        }
        #slip-to-print,
        #slip-to-print * {
            visibility: visible;
        }
        #slip-to-print {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .search-box,
        .slip-buttons,
        .navbar,
        .footer {
            display: none !important;
        }
        .slip-wrapper {
            border: none;
            box-shadow: none;
        }
    }
</style>

<div class="acknowledgement-container">
    <?php if (empty($atts['record'])) : ?>
        <div class="search-box">
            <h3>Download Acknowledgement Slip</h3>
            <form method="get" action="">
                <input type="text" name="search_query" value="<?php echo esc_attr($search_term ?? ''); ?>" placeholder="Enter Unique ID or Mobile Number" required>
                <button type="submit">Search</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($record) : ?>
        <div id="slip-to-print">
            <div class="slip-wrapper">
                <div class="slip-header">
                    <?php if ($logo_base64) : ?><img src="<?php echo esc_attr($logo_base64); ?>" alt="Logo" class="logo"><?php endif; ?>
                    <h2>Reunion - Acknowledgement Slip</h2>
                </div>

                <div style="text-align:center; margin-bottom:20px;">
                    <?php if (!empty($record->profile_picture_url)) : ?>
                        <img src="<?php echo esc_url($record->profile_picture_url); ?>" alt="Profile Picture" class="slip-profile-pic">
                    <?php else : ?>
                        <div class="no-profile-pic">
                            No Photo<br>Available
                        </div>
                    <?php endif; ?>
                </div>

                <table class="slip-details-table">
                    <tr>
                        <td>Registration ID</td>
                        <td id="slip-unique-id"><strong><?php echo esc_html($record->unique_id); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td style="font-weight:bold; color:<?php echo ($record->status === 'Paid') ? 'green' : 'red'; ?>;">
                            <?php echo esc_html($record->status); ?>
                        </td>
                    </tr>
                     <tr>
                        <td>Total Fee Paid</td>
                        <td><strong><?php echo esc_html(number_format((float)$record->total_fee, 2)); ?> BDT</strong></td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td><?php echo esc_html($record->name); ?></td>
                    </tr>
                    <?php if (!empty($record->father_name)) : ?>
                     <tr>
                        <td>Father's Name</td>
                        <td><?php echo esc_html($record->father_name); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($record->mother_name)) : ?>
                     <tr>
                        <td>Mother's Name</td>
                        <td><?php echo esc_html($record->mother_name); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Batch</td>
                        <td><?php echo esc_html($record->batch); ?></td>
                    </tr>
                    <?php if (!empty($record->profession)) : ?>
                     <tr>
                        <td>Profession</td>
                        <td><?php echo esc_html($record->profession); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($record->blood_group)) : ?>
                     <tr>
                        <td>Blood Group</td>
                        <td><?php echo esc_html($record->blood_group); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>T-Shirt Size</td>
                        <td><?php echo esc_html($record->tshirt_size); ?></td>
                    </tr>
                    <tr>
                        <td>Mobile</td>
                        <td><?php echo esc_html($record->mobile_number); ?></td>
                    </tr>
                    <tr>
                        <td>Spouse Attending</td>
                        <td><?php echo esc_html($record->spouse_status); ?></td>
                    </tr>
                    <?php if ($record->spouse_status === 'Yes' && !empty($record->spouse_name)) : ?>
                        <tr>
                            <td>Spouse Name</td>
                            <td><?php echo esc_html($record->spouse_name); ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php
                    if ($record->child_status === 'Yes' && !empty($record->child_details)) {
                        $children = json_decode($record->child_details, true);
                        if (!empty($children) && is_array($children)) {
                    ?>
                            <tr>
                                <td>Children Details</td>
                                <td>
                                    <?php
                                    foreach ($children as $child) {
                                        $child_name = esc_html($child['name']);
                                        $age_str = '(Age not available)';
                                        if (!empty($child['dob'])) {
                                            try {
                                                $dob = new DateTime($child['dob']);
                                                $today = new DateTime('today');
                                                $age = $dob->diff($today)->y;
                                                $age_str = '(Age: ' . $age . ' years)';
                                            } catch (Exception $e) {
                                                $age_str = '(Invalid Date)';
                                            }
                                        }
                                        echo "{$child_name} {$age_str}<br>";
                                    }
                                    ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </table>

                <div class="slip-footer">
                    <p>This is a computer-generated acknowledgement slip and requires no signature.</p>
                </div>
            </div>
        </div>

        <div class="slip-buttons">
            <button class="pdf-btn" id="download-pdf-btn">Download PDF</button>
            <button class="print-btn" onclick="window.print()">Print Slip</button>
        </div>

        <div id="pdf-loading" class="pdf-loading">
            <div>
                <div>Generating PDF...</div>
                <div style="margin-top: 10px;">Please wait...</div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pdfBtn = document.getElementById('download-pdf-btn');
            
            if (pdfBtn) {
                pdfBtn.addEventListener('click', function() {
                    generatePDF();
                });
                
                // Right click for alternative method
                pdfBtn.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    if (confirm('Download not working? Try alternative method?')) {
                        alternativeDownload();
                    }
                });
            }
            
            function generatePDF() {
                // Check if running on mobile
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                
                if (isMobile) {
                    // Use simple method for mobile
                    mobilePDFGenerate();
                } else {
                    // Use enhanced method for PC
                    pcPDFGenerate();
                }
            }
            
            function mobilePDFGenerate() {
    if (typeof html2canvas === 'undefined' || typeof window.jspdf === 'undefined') {
        alert('PDF libraries not loaded. Please refresh the page.');
        return;
    }

    const btn = document.getElementById('download-pdf-btn');
    const loading = document.getElementById('pdf-loading');
    const slipElement = document.getElementById('slip-to-print');
    const uniqueId = document.getElementById('slip-unique-id').textContent.trim();

    btn.innerHTML = 'Generating...';
    btn.disabled = true;
    loading.style.display = 'flex';

    // Create a proper mobile-optimized container
    const mobileContainer = document.createElement('div');
    mobileContainer.style.cssText = `
        position: absolute;
        left: -9999px;
        top: 0;
        width: 794px;
        background-color: #ffffff;
        font-family: Arial, sans-serif;
        padding: 30px;
        box-sizing: border-box;
        line-height: 1.4;
    `;
    
    // Clone and optimize content for mobile
    const slipClone = slipElement.cloneNode(true);
    
    // Optimize images for mobile
    const images = slipClone.querySelectorAll('img');
    images.forEach(img => {
        img.style.maxWidth = '100%';
        img.style.height = 'auto';
        if (img.classList.contains('slip-profile-pic') || img.classList.contains('no-profile-pic')) {
            img.style.width = '100px';
            img.style.height = '100px';
        }
        if (img.classList.contains('logo')) {
            img.style.maxWidth = '120px';
            img.style.maxHeight = '80px';
        }
    });
    
    // Optimize table for mobile
    const table = slipClone.querySelector('.slip-details-table');
    if (table) {
        table.style.cssText = `
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 20px;
        `;
        
        const cells = table.querySelectorAll('td');
        cells.forEach(cell => {
            cell.style.cssText = `
                padding: 8px 12px;
                border: 1px solid #ddd;
                vertical-align: top;
                word-wrap: break-word;
            `;
        });
        
        const labelCells = table.querySelectorAll('td:first-child');
        labelCells.forEach(cell => {
            cell.style.cssText += `
                font-weight: bold;
                width: 35%;
                background-color: #f8f9fa;
            `;
        });
    }
    
    // Add optimized content to container
    mobileContainer.appendChild(slipClone);
    document.body.appendChild(mobileContainer);

    // Wait a bit for rendering, then generate
    setTimeout(function() {
        const { jsPDF } = window.jspdf;
        
        html2canvas(mobileContainer, {
            scale: 2, // Good quality for mobile
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            width: 794,
            height: mobileContainer.scrollHeight,
            logging: false,
            removeContainer: false,
            scrollX: 0,
            scrollY: 0,
            windowWidth: 794,
            windowHeight: mobileContainer.scrollHeight
        }).then(function(canvas) {
            try {
                const imgData = canvas.toDataURL('image/png', 0.95);
                
                // Create PDF with proper mobile dimensions
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4',
                    putOnlyUsedFonts: true,
                    floatPrecision: 16
                });

                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const imgWidth = pdfWidth - 20; // 10mm margin on each side
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                // Check if content fits on one page
                if (imgHeight <= pdfHeight - 20) {
                    // Single page - center it
                    const yOffset = (pdfHeight - imgHeight) / 2;
                    pdf.addImage(imgData, 'PNG', 10, Math.max(10, yOffset), imgWidth, imgHeight);
                } else {
                    // Multiple pages needed
                    let remainingHeight = imgHeight;
                    let currentY = 0;
                    let pageCount = 0;
                    
                    while (remainingHeight > 0) {
                        if (pageCount > 0) {
                            pdf.addPage();
                        }
                        
                        const pageImgHeight = Math.min(remainingHeight, pdfHeight - 20);
                        const sourceY = currentY * canvas.height / imgHeight;
                        const sourceHeight = pageImgHeight * canvas.height / imgHeight;
                        
                        // Create a temporary canvas for this page
                        const pageCanvas = document.createElement('canvas');
                        pageCanvas.width = canvas.width;
                        pageCanvas.height = sourceHeight;
                        const pageCtx = pageCanvas.getContext('2d');
                        
                        pageCtx.drawImage(canvas, 0, sourceY, canvas.width, sourceHeight, 0, 0, canvas.width, sourceHeight);
                        
                        const pageImgData = pageCanvas.toDataURL('image/png', 0.95);
                        pdf.addImage(pageImgData, 'PNG', 10, 10, imgWidth, pageImgHeight);
                        
                        remainingHeight -= pageImgHeight;
                        currentY += pageImgHeight;
                        pageCount++;
                        
                        // Safety check to prevent infinite loop
                        if (pageCount > 10) break;
                    }
                }
                
                // Try multiple download methods for mobile
                try {
                    pdf.save('reunion-slip-' + uniqueId + '.pdf');
                } catch (saveError) {
                    // Mobile fallback: blob download
                    const blob = pdf.output('blob');
                    const url = URL.createObjectURL(blob);
                    
                    // Create download link
                    const downloadLink = document.createElement('a');
                    downloadLink.href = url;
                    downloadLink.download = 'reunion-slip-' + uniqueId + '.pdf';
                    downloadLink.style.cssText = `
                        display: block;
                        padding: 15px;
                        background: #4CAF50;
                        color: white;
                        text-decoration: none;
                        border-radius: 5px;
                        margin: 10px;
                        text-align: center;
                        font-size: 16px;
                    `;
                    downloadLink.innerHTML = '📱 Tap here to download PDF';
                    
                    // Add to page
                    btn.parentNode.insertBefore(downloadLink, btn.nextSibling);
                    
                    // Auto-click for immediate download
                    setTimeout(() => {
                        downloadLink.click();
                    }, 100);
                    
                    // Remove link after some time
                    setTimeout(() => {
                        URL.revokeObjectURL(url);
                        if (downloadLink.parentNode) {
                            downloadLink.parentNode.removeChild(downloadLink);
                        }
                    }, 30000);
                }
                
                // Success feedback
                btn.innerHTML = '✅ Downloaded';
                setTimeout(() => {
                    btn.innerHTML = 'Download PDF';
                }, 3000);
                
            } catch (pdfError) {
                console.error('PDF Creation Error:', pdfError);
                alert('PDF creation failed. Using alternative method...');
                alternativeDownload();
            }
        }).catch(function(canvasError) {
            console.error('Canvas Error:', canvasError);
            alert('Canvas generation failed. Using alternative method...');
            alternativeDownload();
        }).finally(function() {
            // Clean up
            if (mobileContainer && mobileContainer.parentNode) {
                document.body.removeChild(mobileContainer);
            }
            btn.disabled = false;
            loading.style.display = 'none';
        });
    }, 1000); // Give more time for mobile rendering
}
            
            function pcPDFGenerate() {
                const btn = document.getElementById('download-pdf-btn');
                const loading = document.getElementById('pdf-loading');
                const slipElement = document.getElementById('slip-to-print');
                const uniqueId = document.getElementById('slip-unique-id').textContent.trim();

                btn.innerHTML = 'Generating...';
                btn.disabled = true;
                loading.style.display = 'flex';

                // PC-specific approach
                setTimeout(function() {
                    try {
                        // Check libraries
                        if (typeof html2canvas === 'undefined') {
                            throw new Error('html2canvas not loaded');
                        }
                        if (typeof window.jspdf === 'undefined') {
                            throw new Error('jsPDF not loaded');
                        }

                        const { jsPDF } = window.jspdf;
                        
                        // Create a temporary container
                        const tempContainer = document.createElement('div');
                        tempContainer.style.position = 'absolute';
                        tempContainer.style.left = '-9999px';
                        tempContainer.style.top = '0';
                        tempContainer.style.width = '800px';
                        tempContainer.style.backgroundColor = '#ffffff';
                        tempContainer.innerHTML = slipElement.innerHTML;
                        document.body.appendChild(tempContainer);

                        html2canvas(tempContainer, {
                            scale: 3,
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: '#ffffff',
                            width: 800,
                            height: tempContainer.scrollHeight,
                            logging: false,
                            imageTimeout: 15000,
                            removeContainer: false
                        }).then(function(canvas) {
                            try {
                                const imgData = canvas.toDataURL('image/png', 1.0);
                                const pdf = new jsPDF({
                                    orientation: 'portrait',
                                    unit: 'mm',
                                    format: 'a4'
                                });

                                const pdfWidth = pdf.internal.pageSize.getWidth();
                                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                                
                                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                                
                                // Multiple download attempts for PC
                                try {
                                    pdf.save('reunion-slip-' + uniqueId + '.pdf');
                                } catch (saveError1) {
                                    try {
                                        // Fallback 1: Blob download
                                        const blob = pdf.output('blob');
                                        const url = URL.createObjectURL(blob);
                                        const a = document.createElement('a');
                                        a.href = url;
                                        a.download = 'reunion-slip-' + uniqueId + '.pdf';
                                        document.body.appendChild(a);
                                        a.click();
                                        document.body.removeChild(a);
                                        URL.revokeObjectURL(url);
                                    } catch (saveError2) {
                                        // Fallback 2: Open in new window
                                        const pdfDataUri = pdf.output('datauristring');
                                        const newWindow = window.open();
                                        newWindow.document.write('<iframe width="100%" height="100%" src="' + pdfDataUri + '"></iframe>');
                                    }
                                }
                                
                            } catch (pdfError) {
                                console.error('PDF Creation Error:', pdfError);
                                alternativeDownload();
                            }
                        }).catch(function(canvasError) {
                            console.error('Canvas Error:', canvasError);
                            alternativeDownload();
                        }).finally(function() {
                            // Clean up
                            if (tempContainer && tempContainer.parentNode) {
                                document.body.removeChild(tempContainer);
                            }
                            btn.innerHTML = 'Download PDF';
                            btn.disabled = false;
                            loading.style.display = 'none';
                        });

                    } catch (generalError) {
                        console.error('General Error:', generalError);
                        alternativeDownload();
                        btn.innerHTML = 'Download PDF';
                        btn.disabled = false;
                        loading.style.display = 'none';
                    }
                }, 1000);
            }
            
            function alternativeDownload() {
                const slipElement = document.getElementById('slip-to-print');
                const uniqueId = document.getElementById('slip-unique-id').textContent.trim();
                
                if (!slipElement) return;
                
                const slipContent = slipElement.outerHTML;
                const printWindow = window.open('', '_blank', 'width=800,height=600');
                
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Reunion Slip - ${uniqueId}</title>
                        <style>
                            body { 
                                font-family: Arial, sans-serif; 
                                margin: 0; 
                                padding: 20px; 
                                background: white;
                            }
                            .slip-wrapper {
                                border: 1px solid #ddd;
                                padding: 20px;
                                border-radius: 10px;
                                background: #fff;
                            }
                            .slip-details-table { 
                                width: 100%; 
                                border-collapse: collapse; 
                                margin-top: 20px;
                            }
                            .slip-details-table td { 
                                padding: 12px; 
                                border: 1px solid #eee; 
                            }
                            .slip-details-table tr:nth-child(even) {
                                background-color: #f9f9f9;
                            }
                            .slip-details-table td:first-child {
                                font-weight: bold;
                                width: 30%;
                            }
                            .slip-header { 
                                text-align: center; 
                                margin-bottom: 20px; 
                                border-bottom: 2px dashed #ccc;
                                padding-bottom: 10px;
                            }
                            .slip-profile-pic, .no-profile-pic { 
                                width: 120px; 
                                height: 120px; 
                                margin: 0 auto 20px; 
                                display: block;
                            }
                            .no-profile-pic {
                                border: 3px solid #ddd;
                                border-radius: 50%;
                                background-color: #f5f5f5;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: #999;
                                font-size: 14px;
                                text-align: center;
                            }
                            .slip-footer {
                                text-align: center;
                                margin-top: 30px;
                                font-style: italic;
                                color: #555;
                                border-top: 1px solid #ccc;
                                padding-top: 10px;
                            }
                            @media print { 
                                body { margin: 0; padding: 10px; } 
                                .slip-wrapper { border: none; }
                            }
                            .print-controls {
                                text-align: center;
                                margin: 20px 0;
                                padding: 20px;
                                background: #f0f0f0;
                                border-radius: 5px;
                            }
                            .print-controls button {
                                padding: 10px 20px;
                                margin: 0 10px;
                                border: none;
                                border-radius: 5px;
                                cursor: pointer;
                                font-size: 16px;
                            }
                            .print-btn-alt {
                                background: #0073aa;
                                color: white;
                            }
                            .close-btn {
                                background: #666;
                                color: white;
                            }
                            @media print {
                                .print-controls { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-controls">
                            <button class="print-btn-alt" onclick="window.print()">🖨️ Print This Page</button>
                            <button class="close-btn" onclick="window.close()">❌ Close</button>
                            <br><br>
                            <small>Use your browser's "Save as PDF" option while printing to save as PDF</small>
                        </div>
                        ${slipContent}
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
                printWindow.focus();
            }
        });
        </script>

    <?php elseif (!empty($search_term)) : ?>
        <div class="no-record">
            No registration was found for the provided Unique ID or Mobile Number. Please check your input and try again.
        </div>
    <?php endif; ?>
</div>