const toggle = document.querySelector(".menu-toggle"),
  nav = document.querySelector("nav");
toggle?.addEventListener("click", () => {
  const open = nav.classList.toggle("open");
  toggle.setAttribute("aria-expanded", String(open));
  toggle.setAttribute(
    "aria-label",
    open ? "Close navigation" : "Open navigation",
  );
});
const form = document.querySelector("#enquiry");
if (form) {
  const preset = new URLSearchParams(location.search).get("service");
  if (preset) {
    const normalise = (value) =>
      value.toLowerCase().replace(/&/g, "and").replace(/\s+/g, " ").trim();
    const option = [...form.service.options].find(
      (o) => normalise(o.value) === normalise(preset),
    );
    if (option) form.service.value = option.value;
  }
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!form.reportValidity()) return;
    const data = new FormData(form);
    const subject = "Project enquiry — " + data.get("service");
    const body = `Hello Abhika Aura,\n\nI would like to discuss a project.\n\nName: ${data.get("name")}\nCompany: ${data.get("company") || "Not provided"}\nEmail: ${data.get("email")}\nPhone: ${data.get("phone")}\nService: ${data.get("service")}\n\nRequirement:\n${data.get("message")}\n\nKind regards,\n${data.get("name")}`;
    document.querySelector("#email-copy").value = body;
    document.querySelector("#fallback").hidden = false;
    document.querySelector("#form-status").textContent =
      "Your email is prepared. Please send it from your email app to complete your enquiry.";
    location.href = `mailto:insights@abhikaaura.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  });
  document.querySelector("#copy")?.addEventListener("click", async () => {
    const text = document.querySelector("#email-copy");
    try {
      await navigator.clipboard.writeText(text.value);
      document.querySelector("#copy").textContent = "Copied";
    } catch {
      text.focus();
      text.select();
      document.querySelector("#form-status").textContent =
        "Select and copy the prepared enquiry above.";
    }
  });
}

document
  .querySelectorAll(".print-policy")
  .forEach((button) => button.addEventListener("click", () => window.print()));
