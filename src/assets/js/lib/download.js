document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('downloadModal');
  const form = document.getElementById('emailCaptureForm');

  if (!modalEl || !form) {
    return;
  }

  const modal = new Foundation.Reveal($(modalEl));
  const emailInput = document.getElementById('email');
  const nameInput = document.getElementById('name');
  const fileInput = document.getElementById('file_url');
  const mailingListInput = document.getElementById('mailing_list');
  const message = document.getElementById('formMessage');
  const submitBtn = form.querySelector('button[type="submit"]');
  let currentFile = '';

  // Use event delegation on document rather than direct listeners on links
  document.addEventListener('click', function (e) {
    const link = e.target.closest('.download-link');
    if (!link) return;

    e.preventDefault();
    currentFile = link.dataset.fileUrl;

    if (document.cookie.includes('emailCaptured=true')) {
      openFile(currentFile);
      return;
    }

    fileInput.value = currentFile;
    emailInput.value = '';
    nameInput.value = '';
    message.style.display = 'none';
    form.style.display = 'block';
    modal.open();
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const email = emailInput.value.trim();
    const name = nameInput.value.trim();
    const file = fileInput.value;
    const mailingList = mailingListInput.checked ? '1' : '0';

    if (!email || !name) return;

    submitBtn.disabled = true;

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
          file: file,
          mailing_list: mailingList
        })
      })
      .then(res => res.json())
      .then(data => {
        form.style.display = 'none';
        message.style.display = 'block';

        const d = new Date();
        d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
        document.cookie = `emailCaptured=true;path=/;expires=${d.toUTCString()}`;

        if (newTab) {
          newTab.location = file;
        } else {
          window.location.href = file;
        }

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

  function openFile(url) {
    const tab = window.open('', '_blank');
    if (tab) {
      tab.location = url;
    } else {
      window.location.href = url;
    }
  }
});