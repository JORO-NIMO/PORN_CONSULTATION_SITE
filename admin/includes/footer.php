<!-- Footer -->
<footer class="footer mt-auto py-3 bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 text-muted">
                &copy; <?php echo date('Y'); ?> Freedom Path. All rights reserved.
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted">
                    <i class="fas fa-server me-1"></i> Server: <?php echo gethostname(); ?>
                    <span class="mx-2">|</span>
                    <span id="server-time"></span>
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom Scripts -->
<script>
// Update server time
function updateServerTime() {
    const now = new Date();
    document.getElementById('server-time').textContent = 'Time: ' + now.toLocaleTimeString();
}

// Update time every second
setInterval(updateServerTime, 1000);
updateServerTime();

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Initialize popovers
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
});

// Initialize DataTables
$(document).ready(function() {
    $('.datatable').DataTable({
        responsive: true,
        stateSave: true,
        pageLength: 25,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries per page",
            zeroRecords: "No matching records found",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries to show",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"top"f<"clear">>rt<"bottom"lip><"clear">',
        initComplete: function() {
            // Add custom filter dropdowns here if needed
        }
    });
});

// Confirm before deleting
function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        event.preventDefault();
        return false;
    }
    return true;
}

// Add csrf token to all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': '<?php echo generateCSRFToken(); ?>'
    }
});

// Global error handling
$(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
    console.error('AJAX Error:', ajaxSettings.url, jqXHR.status, thrownError);
    
    let errorMessage = 'An error occurred. Please try again.';
    
    if (jqXHR.status === 401) {
        errorMessage = 'Your session has expired. Please refresh the page and log in again.';
        window.location.href = '/login.php?expired=1';
    } else if (jqXHR.status === 403) {
        errorMessage = 'You do not have permission to perform this action.';
    } else if (jqXHR.status === 404) {
        errorMessage = 'The requested resource was not found.';
    } else if (jqXHR.status === 422) {
        // Handle validation errors
        const errors = jqXHR.responseJSON.errors;
        errorMessage = [];
        for (const field in errors) {
            errorMessage.push(errors[field].join(' '));
        }
        errorMessage = errorMessage.join('\n');
    } else if (jqXHR.status >= 500) {
        errorMessage = 'A server error occurred. Please try again later.';
    }
    
    // Show error message
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ${errorMessage}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Add alert to the top of the content area
    $('main').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
});
</script>

<!-- Page-specific scripts -->
<?php if (function_exists('pageSpecificScripts')) echo pageSpecificScripts(); ?>

</body>
</html>
