document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('update-part-form');
  if (!form) return;

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(form);

    fetch('/form/update/part', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success') {
          if (!formData.has('id')) {
            // created -> go to the detail page if id available
            if (data.id !== undefined && data.id !== null) {
              window.location = '/parts?id=' + data.id;
            } else {
              alert('Part created successfully, but no id returned.');
              form.reset();
            }
          } else {
            alert('Part updated successfully!');
          }
        } else {
          alert('Update failed: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(err => {
        console.error(err);
        alert('An error occurred. Please try again.');
      });
  });
});

