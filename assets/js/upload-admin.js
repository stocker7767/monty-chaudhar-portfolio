(() => {
  "use strict";
  function initialize() {
    document.querySelectorAll(".monty-project-uploader").forEach((box) => {
      if (box.dataset.initialized) return;
      box.dataset.initialized = "true";
      const input = box.querySelector(".monty-project-zip");
      const upload = box.querySelector(".monty-upload-button");
      const remove = box.querySelector(".monty-remove-button");
      const status = box.querySelector(".monty-upload-status");
      const current = box.querySelector(".monty-demo-current");
      const preview = box.querySelector(".monty-demo-preview");
      if (!input || !upload || !remove) return;
      let busy = false;
      async function request(action, file) {
        if (busy) return;
        busy = true;
        upload.disabled = true;
        remove.disabled = true;
        input.disabled = true;
        status.textContent = file
          ? "Checking and uploading your website…"
          : "Removing the uploaded demo…";
        status.classList.remove("is-error");
        const data = new FormData();
        data.append("action", action);
        data.append("project_id", box.dataset.project);
        data.append("nonce", box.dataset.nonce);
        data.append("expected", box.dataset.revision || "");
        if (file) data.append("project_zip", file, file.name);
        try {
          const response = await fetch(window.MONTY_UPLOAD.ajaxUrl, {
            method: "POST",
            credentials: "same-origin",
            body: data,
          });
          let result;
          try {
            result = JSON.parse(await response.text());
          } catch {
            throw new Error(
              "The server could not complete the upload. Check your site’s upload limit, reload the editor and try again.",
            );
          }
          if (!response.ok || !result.success)
            throw new Error(
              result.data?.message ||
                "The upload could not be completed. Your previous demo has been kept.",
            );
          const values = result.data;
          box.dataset.revision = values.revision || "";
          status.textContent = values.message;
          if (values.revision) {
            current.textContent = values.name;
            preview.href = values.url;
            preview.hidden = false;
            input.value = "";
          } else {
            current.textContent =
              "No uploaded demo. The selected bundled demo or external URL will be used.";
            preview.hidden = true;
            preview.removeAttribute("href");
          }
        } catch (error) {
          status.textContent = error.message;
          status.classList.add("is-error");
        } finally {
          busy = false;
          upload.disabled = false;
          input.disabled = false;
          remove.disabled = !box.dataset.revision;
        }
      }
      upload.addEventListener("click", () => {
        const file = input.files?.[0];
        if (!file || !/\.zip$/i.test(file.name)) {
          status.textContent = "Choose the ZIP of your finished website first.";
          status.classList.add("is-error");
          return;
        }
        if (file.size > Number(box.dataset.limit)) {
          status.textContent =
            "This ZIP exceeds your site’s upload limit. Reduce large assets and ZIP only the finished website.";
          status.classList.add("is-error");
          return;
        }
        request("monty_demo_upload", file);
      });
      remove.addEventListener("click", () => {
        if (
          window.confirm(
            "Remove this uploaded demo? The project entry stays saved, and you can upload the ZIP again later.",
          )
        )
          request("monty_demo_remove");
      });
    });
  }
  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", initialize);
  else initialize();
  new MutationObserver(initialize).observe(document.body, {
    childList: true,
    subtree: true,
  });
})();
