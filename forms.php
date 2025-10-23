<?php
require_once 'config/config.php';
requireLogin();

header('Location: dashboard.php');
exit;

$db = Database::getInstance();

// Handle form creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_form'])) {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $fields = $_POST['fields'] ?? [];
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $shareToken = generateToken(32);
    
    if (!empty($title) && !empty($fields)) {
        $db->query(
            "INSERT INTO form_templates (created_by, title, description, fields, share_token, is_public) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$_SESSION['user_id'], $title, $description, json_encode($fields), $shareToken, $isPublic]
        );
        
        if (isAjax()) {
            jsonResponse(['success' => true, 'share_token' => $shareToken]);
        }
        header('Location: forms.php?created=1');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    $formId = intval($_POST['form_id']);
    $formData = $_POST['form_data'] ?? [];
    
    $db->query(
        "INSERT INTO form_submissions (form_id, user_id, data) VALUES (?, ?, ?)",
        [$formId, $_SESSION['user_id'], json_encode($formData)]
    );
    
    if (isAjax()) {
        jsonResponse(['success' => true, 'message' => 'Form submitted successfully']);
    }
    header('Location: forms.php?submitted=1');
    exit;
}

// Get user's forms
$myForms = $db->fetchAll(
    "SELECT * FROM form_templates WHERE created_by = ? ORDER BY created_at DESC",
    [$_SESSION['user_id']]
);

// Get public forms
$publicForms = $db->fetchAll(
    "SELECT ft.*, u.name as creator_name 
     FROM form_templates ft 
     LEFT JOIN users u ON ft.created_by = u.id 
     WHERE ft.is_public = 1 
     ORDER BY ft.created_at DESC 
     LIMIT 20"
);

// Load specific form if token provided
$sharedForm = null;
if (isset($_GET['token'])) {
    $sharedForm = $db->fetchOne(
        "SELECT * FROM form_templates WHERE share_token = ?",
        [sanitize($_GET['token'])]
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Forms - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .forms-page {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    .forms-page .page-header {
        text-align: center;
        margin-bottom: 3rem;
        color: white;
    }
    .forms-page .page-header h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
        text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        color: white;
    }
    .forms-page .page-header .subtitle {
        font-size: 1.25rem;
        color: rgba(255, 255, 255, 0.9);
    }
    .form-view, .forms-section {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        margin-bottom: 2rem;
    }
    .form-header h1 {
        color: var(--dark);
        margin-bottom: 1rem;
    }
    .form-header p {
        color: var(--text);
        font-size: 1.125rem;
    }
    .forms-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }
    .form-card {
        background: white;
        border: 2px solid var(--border);
        border-radius: 16px;
        padding: 2rem;
        transition: all 0.3s ease;
    }
    .form-card:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
        transform: translateY(-5px);
    }
    .form-card h3 {
        color: var(--dark);
        margin-bottom: 0.75rem;
    }
    .form-card p {
        color: var(--text);
        margin-bottom: 1rem;
    }
    .form-meta {
        color: var(--text-light);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    .forms-section h2 {
        color: var(--dark);
        margin-bottom: 2rem;
        font-size: 2rem;
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="forms-page">
        <div class="container">
            <?php if ($sharedForm): ?>
            <!-- Shared Form View -->
            <div class="form-view">
                <div class="form-header">
                    <h1><?php echo sanitize($sharedForm['title']); ?></h1>
                    <p><?php echo sanitize($sharedForm['description']); ?></p>
                </div>
                
                <div class="form-progress" style="margin-bottom: 2rem;">
                    <div class="progress-bar" style="height: 8px; background: var(--border); border-radius: 4px; overflow: hidden;">
                        <div class="progress-fill" style="height: 100%; width: 0%; background: linear-gradient(90deg, var(--primary), var(--secondary)); transition: width 0.3s ease;"></div>
                    </div>
                    <p class="progress-text" style="text-align: center; margin-top: 0.5rem; color: var(--text-light);">0% Complete</p>
                </div>
                
                <form id="sharedForm" method="POST" class="dynamic-form">
                    <input type="hidden" name="submit_form" value="1">
                    <input type="hidden" name="form_id" value="<?php echo $sharedForm['id']; ?>">
                    
                    <?php 
                    $fields = json_decode($sharedForm['fields'], true);
                    foreach ($fields as $index => $field): 
                    ?>
                    <div class="form-group">
                        <label for="field_<?php echo $index; ?>">
                            <?php echo sanitize($field['label']); ?>
                            <?php if ($field['required']): ?><span class="req">*</span><?php endif; ?>
                        </label>
                        
                        <?php if ($field['type'] === 'text'): ?>
                            <input type="text" id="field_<?php echo $index; ?>" 
                                   name="form_data[<?php echo $index; ?>]" 
                                   <?php echo $field['required'] ? 'required' : ''; ?>>
                        
                        <?php elseif ($field['type'] === 'textarea'): ?>
                            <textarea id="field_<?php echo $index; ?>" 
                                      name="form_data[<?php echo $index; ?>]" 
                                      rows="4"
                                      <?php echo $field['required'] ? 'required' : ''; ?>></textarea>
                        
                        <?php elseif ($field['type'] === 'select'): ?>
                            <select id="field_<?php echo $index; ?>" 
                                    name="form_data[<?php echo $index; ?>]"
                                    <?php echo $field['required'] ? 'required' : ''; ?>>
                                <option value="">-- Select --</option>
                                <?php foreach ($field['options'] as $option): ?>
                                <option value="<?php echo sanitize($option); ?>"><?php echo sanitize($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        
                        <?php elseif ($field['type'] === 'radio'): ?>
                            <?php foreach ($field['options'] as $optIndex => $option): ?>
                            <label class="radio-label">
                                <input type="radio" name="form_data[<?php echo $index; ?>]" 
                                       value="<?php echo sanitize($option); ?>"
                                       <?php echo $field['required'] ? 'required' : ''; ?>>
                                <?php echo sanitize($option); ?>
                            </label>
                            <?php endforeach; ?>
                        
                        <?php elseif ($field['type'] === 'checkbox'): ?>
                            <?php foreach ($field['options'] as $optIndex => $option): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="form_data[<?php echo $index; ?>][]" 
                                       value="<?php echo sanitize($option); ?>">
                                <?php echo sanitize($option); ?>
                            </label>
                            <?php endforeach; ?>
                        
                        <?php elseif ($field['type'] === 'number'): ?>
                            <input type="number" id="field_<?php echo $index; ?>" 
                                   name="form_data[<?php echo $index; ?>]"
                                   <?php echo $field['required'] ? 'required' : ''; ?>>
                        
                        <?php elseif ($field['type'] === 'date'): ?>
                            <input type="date" id="field_<?php echo $index; ?>" 
                                   name="form_data[<?php echo $index; ?>]"
                                   <?php echo $field['required'] ? 'required' : ''; ?>>
                        <?php endif; ?>
                        
                        <?php if (!empty($field['description'])): ?>
                        <small class="field-help"><?php echo sanitize($field['description']); ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="button" id="clearForm" class="btn btn-secondary">Clear Form</button>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Form</button>
                    </div>
                </form>
                
                <script>
                // Form progress tracking
                const form = document.getElementById('sharedForm');
                const progressFill = document.querySelector('.progress-fill');
                const progressText = document.querySelector('.progress-text');
                const clearBtn = document.getElementById('clearForm');
                
                function updateProgress() {
                    const fields = form.querySelectorAll('input[required], select[required], textarea[required]');
                    let filled = 0;
                    fields.forEach(field => {
                        if (field.type === 'radio') {
                            const name = field.name;
                            if (form.querySelector(`input[name="${name}"]:checked`)) {
                                filled++;
                            }
                        } else if (field.value.trim()) {
                            filled++;
                        }
                    });
                    const progress = fields.length > 0 ? Math.round((filled / fields.length) * 100) : 0;
                    progressFill.style.width = progress + '%';
                    progressText.textContent = progress + '% Complete';
                }
                
                // Track changes
                form.addEventListener('input', updateProgress);
                form.addEventListener('change', updateProgress);
                
                // Clear form
                clearBtn.addEventListener('click', () => {
                    if (confirm('Are you sure you want to clear all fields?')) {
                        form.reset();
                        updateProgress();
                    }
                });
                
                // Form validation with better messages
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let firstEmpty = null;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim() && !firstEmpty) {
                            firstEmpty = field;
                        }
                    });
                    
                    if (firstEmpty) {
                        e.preventDefault();
                        firstEmpty.focus();
                        firstEmpty.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Show error message
                        const label = firstEmpty.closest('.form-group').querySelector('label');
                        const fieldName = label ? label.textContent.replace('*', '').trim() : 'This field';
                        alert(`Please fill in: ${fieldName}`);
                    }
                });
                
                // Initial progress
                updateProgress();
                </script>
            </div>
            
            <?php else: ?>
            <!-- Forms Library View -->
            <div class="page-header">
                <h1>Assessment Forms</h1>
                <p class="subtitle">Track your progress with customizable forms</p>
            </div>
            
            <div style="text-align: center; margin-bottom: 2rem;">
                <button id="createFormBtn" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">Create New Form</button>
            </div>
            
            <?php if (isset($_GET['created'])): ?>
            <div class="alert success">Form created successfully!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['submitted'])): ?>
            <div class="alert success">Form submitted successfully!</div>
            <?php endif; ?>
            
            <!-- Form Builder Modal -->
            <div id="formBuilderModal" class="modal">
                <div class="modal-content large">
                    <div class="modal-header">
                        <h2>Create Assessment Form</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <form id="formBuilder" method="POST">
                        <input type="hidden" name="create_form" value="1">
                        
                        <div class="form-group">
                            <label for="form_title">Form Title</label>
                            <input type="text" id="form_title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="form_description">Description</label>
                            <textarea id="form_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="is_public">
                                Make this form public (others can use it)
                            </label>
                        </div>
                        
                        <div class="form-fields-section">
                            <h3>Form Fields</h3>
                            <div id="formFields"></div>
                            <button type="button" id="addField" class="btn btn-secondary">+ Add Field</button>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Create Form</button>
                            <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- My Forms -->
            <?php if (!empty($myForms)): ?>
            <section class="forms-section">
                <h2>My Forms</h2>
                <div class="forms-grid">
                    <?php foreach ($myForms as $form): ?>
                    <div class="form-card">
                        <h3><?php echo sanitize($form['title']); ?></h3>
                        <p><?php echo sanitize($form['description']); ?></p>
                        <div class="form-meta">
                            <span>Created: <?php echo date('M d, Y', strtotime($form['created_at'])); ?></span>
                            <?php if ($form['is_public']): ?>
                            <span class="badge">Public</span>
                            <?php endif; ?>
                        </div>
                        <div class="form-actions">
                            <a href="?token=<?php echo $form['share_token']; ?>" class="btn btn-primary">Fill Form</a>
                            <button class="btn btn-secondary copy-link" data-link="<?php echo SITE_URL; ?>/forms.php?token=<?php echo $form['share_token']; ?>">
                                Copy Link
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Public Forms -->
            <?php if (!empty($publicForms)): ?>
            <section class="forms-section">
                <h2>Public Forms</h2>
                <div class="forms-grid">
                    <?php foreach ($publicForms as $form): ?>
                    <div class="form-card">
                        <h3><?php echo sanitize($form['title']); ?></h3>
                        <p><?php echo sanitize($form['description']); ?></p>
                        <div class="form-meta">
                            <span>By: <?php echo sanitize($form['creator_name'] ?? 'Anonymous'); ?></span>
                        </div>
                        <div class="form-actions">
                            <a href="?token=<?php echo $form['share_token']; ?>" class="btn btn-primary">Fill Form</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/form-builder.js"></script>
</body>
</html>
