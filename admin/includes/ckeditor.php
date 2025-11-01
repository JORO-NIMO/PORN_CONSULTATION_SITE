<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<!-- CKEditor Image Upload Handler -->
<script>
    // This function will be called by CKEditor when uploading files
    function uploadImage(file) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('upload', file);
            
            fetch('handlers/upload_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.uploaded) {
                    resolve({
                        default: data.url
                    });
                } else {
                    reject(data.error?.message || 'Upload failed');
                }
            })
            .catch(error => {
                reject('Upload failed: ' + error.message);
            });
        });
    }
</script>
