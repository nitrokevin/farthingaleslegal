document.addEventListener('DOMContentLoaded', function () {
  // Only run if there is a download link AND the modal exists
  const modalEl = document.getElementById('downloadModal');
  const downloadLinks = document.querySelectorAll('.download-link');
  const form = document.getElementById('emailCaptureForm');

  if (!modalEl || downloadLinks.length === 0 || !form) {
    return; // exit if modal or download links are not present
  }

  const modal = new Foundation.Reveal($(modalEl));
  const emailInput = document.getElementById('email');
  const nameInput = document.getElementById('name');
  const fileInput = document.getElementById('file_url');
  const message = document.getElementById('formMessage');
  const submitBtn = form.querySelector('button[type="submit"]');
  let currentFile = '';

  // Intercept download clicks
  downloadLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      currentFile = this.dataset.fileUrl;

      // If already submitted before, open file directly
      if (document.cookie.includes('emailCaptured=true')) {
        openFile(currentFile);
        return;
      }

      // Set hidden field and show modal
      fileInput.value = currentFile;
      emailInput.value = '';
      nameInput.value = '';
      message.style.display = 'none';
      form.style.display = 'block';
      modal.open();
    });
  });

  // Handle form submission
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const email = emailInput.value.trim();
    const name = nameInput.value.trim();
    const file = fileInput.value;

    if (!email || !name) return;

    submitBtn.disabled = true;

    // Fallback: open a blank tab immediately to satisfy browser pop-up rules
    const newTab = window.open('', '_blank');

    fetch(ajaxData.ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'save_email',
          user_name: name,
          email: email,
          file: file
        })
      })
      .then(res => res.json())
      .then(data => {
        form.style.display = 'none';
        message.style.display = 'block';

        // Set cookie
        const d = new Date();
        d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
        document.cookie = `emailCaptured=true;path=/;expires=${d.toUTCString()}`;

        // Open file in new tab or fallback
        if (newTab) {
          newTab.location = file;
        } else {
          window.location.href = file;
        }

        // Close modal shortly after
        setTimeout(() => {
          modal.close();
        }, 500);
      })
      .catch(error => {
        console.error('Download form error:', error);
        alert('Something went wrong, please try again.');
        submitBtn.disabled = false;
      })
      .finally(() => {
        submitBtn.disabled = false;
      });
  });

  // Helper: opens a file in a new tab
  function openFile(url) {
    const tab = window.open('', '_blank');
    if (tab) {
      tab.location = url;
    } else {
      window.location.href = url;
    }
  }
});