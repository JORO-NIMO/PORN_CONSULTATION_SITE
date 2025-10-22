// Dynamic Form Builder
(function() {
    const createFormBtn = document.getElementById('createFormBtn');
    const modal = document.getElementById('formBuilderModal');
    const closeBtns = document.querySelectorAll('.modal-close');
    const addFieldBtn = document.getElementById('addField');
    const formFields = document.getElementById('formFields');
    
    let fieldCount = 0;
    
    // Modal controls
    if (createFormBtn) {
        createFormBtn.addEventListener('click', () => {
            modal.classList.add('active');
        });
    }
    
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    });
    
    // Close modal on outside click
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
    
    // Add field
    if (addFieldBtn) {
        addFieldBtn.addEventListener('click', () => {
            addFormField();
        });
    }
    
    function addFormField() {
        const fieldId = fieldCount++;
        const fieldHtml = `
            <div class="field-builder" data-field-id="${fieldId}">
                <div class="field-builder-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0.5rem; background: var(--light); border-radius: 8px; cursor: move;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="drag-handle" style="cursor: grab;">⋮⋮</span>
                        <h4 style="margin: 0;">Field ${fieldId + 1}</h4>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-sm duplicate-field" title="Duplicate">📋</button>
                        <button type="button" class="btn btn-sm danger remove-field" title="Remove">🗑️</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Field Type</label>
                    <select name="fields[${fieldId}][type]" class="field-type" required>
                        <option value="text">Text Input</option>
                        <option value="textarea">Text Area</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <option value="select">Dropdown</option>
                        <option value="radio">Radio Buttons</option>
                        <option value="checkbox">Checkboxes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Field Label</label>
                    <input type="text" name="fields[${fieldId}][label]" required>
                </div>
                <div class="form-group">
                    <label>Description (optional)</label>
                    <input type="text" name="fields[${fieldId}][description]">
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="fields[${fieldId}][required]" value="1">
                        Required field
                    </label>
                </div>
                <div class="field-options" style="display:none;">
                    <div class="form-group">
                        <label>Options (one per line)</label>
                        <textarea name="fields[${fieldId}][options]" rows="3" placeholder="Option 1\nOption 2\nOption 3"></textarea>
                    </div>
                </div>
            </div>
        `;
        
        formFields.insertAdjacentHTML('beforeend', fieldHtml);
        
        // Add event listeners
        const newField = formFields.lastElementChild;
        const removeBtn = newField.querySelector('.remove-field');
        const typeSelect = newField.querySelector('.field-type');
        const optionsDiv = newField.querySelector('.field-options');
        
        removeBtn.addEventListener('click', () => {
            if (confirm('Remove this field?')) {
                newField.remove();
            }
        });
        
        // Duplicate field
        const duplicateBtn = newField.querySelector('.duplicate-field');
        duplicateBtn.addEventListener('click', () => {
            const clone = newField.cloneNode(true);
            const newFieldId = fieldCount++;
            clone.dataset.fieldId = newFieldId;
            
            // Update field names
            clone.querySelectorAll('[name]').forEach(input => {
                const oldName = input.name;
                input.name = oldName.replace(/\[\d+\]/, `[${newFieldId}]`);
            });
            
            // Update header
            clone.querySelector('h4').textContent = `Field ${newFieldId + 1}`;
            
            // Re-attach event listeners
            const cloneRemoveBtn = clone.querySelector('.remove-field');
            cloneRemoveBtn.addEventListener('click', () => {
                if (confirm('Remove this field?')) {
                    clone.remove();
                }
            });
            
            const cloneDuplicateBtn = clone.querySelector('.duplicate-field');
            cloneDuplicateBtn.addEventListener('click', () => {
                duplicateBtn.click();
            });
            
            const cloneTypeSelect = clone.querySelector('.field-type');
            const cloneOptionsDiv = clone.querySelector('.field-options');
            cloneTypeSelect.addEventListener('change', () => {
                const type = cloneTypeSelect.value;
                if (type === 'select' || type === 'radio' || type === 'checkbox') {
                    cloneOptionsDiv.style.display = 'block';
                } else {
                    cloneOptionsDiv.style.display = 'none';
                }
            });
            
            newField.after(clone);
        });
        
        typeSelect.addEventListener('change', () => {
            const type = typeSelect.value;
            if (type === 'select' || type === 'radio' || type === 'checkbox') {
                optionsDiv.style.display = 'block';
            } else {
                optionsDiv.style.display = 'none';
            }
        });
    }
    
    // Form submission
    const formBuilder = document.getElementById('formBuilder');
    if (formBuilder) {
        formBuilder.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(formBuilder);
            
            // Process fields
            const fields = [];
            const fieldElements = formFields.querySelectorAll('.field-builder');
            
            fieldElements.forEach((fieldEl, index) => {
                const type = fieldEl.querySelector('.field-type').value;
                const label = fieldEl.querySelector('input[name*="[label]"]').value;
                const description = fieldEl.querySelector('input[name*="[description]"]').value;
                const required = fieldEl.querySelector('input[name*="[required]"]').checked;
                
                const field = { type, label, description, required };
                
                if (type === 'select' || type === 'radio' || type === 'checkbox') {
                    const optionsText = fieldEl.querySelector('textarea[name*="[options]"]').value;
                    field.options = optionsText.split('\n').filter(o => o.trim());
                }
                
                fields.push(field);
            });
            
            // Create new FormData with processed fields
            const submitData = new FormData();
            submitData.append('create_form', '1');
            submitData.append('title', formData.get('title'));
            submitData.append('description', formData.get('description'));
            if (formData.get('is_public')) {
                submitData.append('is_public', '1');
            }
            submitData.append('fields', JSON.stringify(fields));
            
            const submitBtn = formBuilder.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';
            
            try {
                const response = await fetch(formBuilder.action, {
                    method: 'POST',
                    body: submitData
                });
                
                if (response.ok) {
                    window.location.href = 'forms.php?created=1';
                } else {
                    alert('Failed to create form. Please try again.');
                }
            } catch (error) {
                alert('Network error. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Form';
            }
        });
    }
    
    // Copy link functionality
    document.querySelectorAll('.copy-link').forEach(btn => {
        btn.addEventListener('click', () => {
            const link = btn.dataset.link;
            navigator.clipboard.writeText(link).then(() => {
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => {
                    btn.textContent = originalText;
                }, 2000);
            });
        });
    });
})();
