(function ($) {
  "use strict";

  $(function () {
    const modalEl = $("#downloadModal");
    if (!modalEl.length) return;

    let modal;
    try {
      modal = new Foundation.Reveal(modalEl);
    } catch (e) {
      $(document).foundation();
      modal = new Foundation.Reveal(modalEl);
    }

    // Click handler for download links
    $(document).on("click", ".download-link", function (e) {
      e.preventDefault();

      const fileUrl = $(this).data("file-url");

      if (!fileUrl) {
        console.error("No file URL found.");
        return;
      }

      // If cookie already set, go straight to file
      if (document.cookie.indexOf("emailCaptured=true") !== -1) {
        window.open(fileUrl, "_blank");
        return;
      }

      // Set file URL in hidden field and open modal
      $("#file_url").val(fileUrl);
      modal.open();
    });
  });
})(jQuery);
