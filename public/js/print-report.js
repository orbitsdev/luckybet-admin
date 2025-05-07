function printReport() {
    // Store the original page content
    const originalContent = document.body.innerHTML;
    
    // Get only the report content
    const reportContent = document.querySelector('.report-content').cloneNode(true);
    
    // Remove pagination elements
    var paginationElements = reportContent.querySelectorAll('.print\\:hidden, [class*="print:hidden"]');
    paginationElements.forEach(function(el) {
        el.remove();
    });
    
    // Create a new window with just the report content and styling
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Report</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                }
                .report-header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .report-header h1 {
                    margin-bottom: 5px;
                }
                .report-header p {
                    color: #666;
                }
                @media print {
                    button {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            ${reportContent}
            <script>
                window.onload = function() {
                    window.print();
                    // Close the window after printing (optional)
                    // window.onafterprint = function() { window.close(); };
                }
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
