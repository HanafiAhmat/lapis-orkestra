let recordToDelete = null;

const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
const deleteNameSpan = document.getElementById('deleteRecordName');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

// When user confirms deletion
confirmDeleteBtn.addEventListener('click', async () => {
  if (!recordToDelete) return;

  doSubmitForm(recordToDelete.form);
  deleteModal.hide();
});

cancelDeleteBtn.addEventListener('click', async () => {
  recordToDelete = null;
});

/**
 * Fades out a DOM element
 */
const fadeOut = (element, duration) => {
  element.style.transition = `opacity ${duration}ms ease`;
  element.style.opacity = 1;

  requestAnimationFrame(() => {
    element.style.opacity = 0;
    setTimeout(() => {
      if (element.parentNode) {
        element.parentNode.removeChild(element);
      }
    }, duration);
  });
}

/**
 * Auto-dismiss and fade out alert boxes after a delay
 * Usage: autoDismissAlerts(); OR autoDismissAlerts({ delay: 4000, fadeSpeed: 300 });
 */
const autoDismissAlerts = ({ delay = 5000, fadeSpeed = 300 } = {}) => {
  const alerts = document.querySelectorAll('.alert-dismissible');

  alerts.forEach((alert) => {
    setTimeout(() => {
      fadeOut(alert, fadeSpeed);
    }, delay);
  });
}

const closeAlert = () => {
  const closeButtons = document.querySelectorAll('.alert-close-btn');

  closeButtons.forEach((closeButton) => {
    closeButton.addEventListener('click', () => {
      fadeOut(closeButton.target.parentNode, 300);
    });
  });
}

const doSubmitForm = (form) => {
  const formData = new FormData(form);
  const method = formData.get('_method').toUpperCase();

  fetch(form.action, {
    method: method,
    headers: {
      // signals backend to return json
      'Accept': 'application/json',
      'X-Csrf-Token': formData.get('csrf_token')
    },
    // Note that we don't have to set the Content-Type header. 
    // The correct header will be automatically set when we pass a FormData object into fetch().
    body: formData
  })
  .then(response => {
    if (response.ok && response.status == 204) {
      return {
        'status': 'success',
        'message': 'Record deleted successfully',
      };
    }

    return response.json();
  })
  .then(result => {
    if (result.status === 'success') {
      showToast(`✅ ${result.message}`, 'success');
      if (recordToDelete) {
        recordToDelete.rowElement.remove(); // remove row from table
      }
    } else if (result.status === 'fail') {
      showToast(`⚠️ ${result.message}`, 'warning');
    } else {
      showToast(`❌ ${result.message}`, 'danger');
    }

    recordToDelete = null;
  })
  .catch(error => {
    showToast('❌ Network/server error', 'danger');
    recordToDelete = null;
  });  
}

const customFormMethods = () => {
  const forms = document.querySelectorAll('form');

  forms.forEach((form) => {
    let methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
      method = ('' + methodInput.value).toUpperCase();
      // if (['PUT', 'PATCH', 'DELETE'].includes(method)) {
      if (['DELETE'].includes(method)) {
        form.addEventListener('submit', (event) => {
          // Prevent the default form submission
          event.preventDefault();

          // Get form data
          const formData = new FormData(form);

          if (method == 'DELETE') {
            recordToDelete = {
              id: formData.get('entity_id'),
              name: 'record ID ' + formData.get('entity_id'),
              rowElement: form.closest('tr'),
              form: form
            };
            deleteNameSpan.textContent = recordToDelete.name;
            deleteModal.show();
          } else {
            doSubmitForm(form);
          }
        });
      }
    }
  });
}

const MAX_VISIBLE_TOASTS = 3;

const showToast = (message, type = 'success') => {
  const container = document.querySelector('#toastContainer');

  // remove oldest if exceed max
  const existingToasts = container.querySelectorAll('.toast');
  if (existingToasts.length >= MAX_VISIBLE_TOASTS) {
    existingToasts[0].remove();
  }

  const toastId = `toast-${Date.now()}`;
  const toastHtml = `
    <div id="${toastId}" class="toast fade align-items-center text-bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  `;
  container.insertAdjacentHTML('beforeend', toastHtml);

  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, { delay: type === 'success' ? 2000 : 5000 });
  toast.show();
  toastElement.addEventListener('hidden.bs.toast', () => {
    toastElement.remove();
  });
}

// Optional: Run on DOM load
document.addEventListener('DOMContentLoaded', () => {
  autoDismissAlerts();
  closeAlert();
  customFormMethods();
});
