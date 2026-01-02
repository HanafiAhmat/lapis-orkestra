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

const customFormMethods = () => {
  const forms = document.querySelectorAll('form');

  forms.forEach((form) => {
    let methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
      method = ('' + methodInput.value).toUpperCase();
      if (['PUT', 'PATCH', 'DELETE'].includes(method)) {
        form.addEventListener('submit', (event) => {
          // Prevent the default form submission
          event.preventDefault();

          // Get form data
          const formData = new FormData(form);

          fetch(form.action, {
            method: method,
            headers: {
              // signals backend to return json
              'Accept': 'application/json',
              'X-Csrf-Token': formData.get('csrf_token')
            },
            // Note that we don't have to set the Content-Type header: the correct header is automatically set when we pass a FormData object into fetch().
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
            console.log('Success lah:', result);
          })
          .catch(error => {
            console.error('Error lah:', error);
          });
        });
      }
    }
  });
}

// Optional: Run on DOM load
document.addEventListener('DOMContentLoaded', () => {
  autoDismissAlerts();
  closeAlert();
  customFormMethods();
});

document.getElementById('routeToggle')
        .addEventListener('click', (event) => {
          document.getElementById('routeMenu').classList.toggle('open');
        });
