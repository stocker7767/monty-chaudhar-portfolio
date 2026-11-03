(() => {
  "use strict";
  const menu = document.querySelector(".menu");
  const links = document.querySelector(".links");
  if (menu && links) {
    menu.addEventListener("click", () => {
      const open = links.classList.toggle("open");
      menu.setAttribute("aria-expanded", String(open));
      menu.textContent = open ? "✕" : "☰";
    });
  }
  if (links)
    links.querySelectorAll("a").forEach((link) =>
      link.addEventListener("click", () => {
        links.classList.remove("open");
        if (menu) {
          menu.textContent = "☰";
          menu.setAttribute("aria-expanded", "false");
        }
      }),
    );
  document.querySelectorAll("[data-year]").forEach((element) => {
    element.textContent = new Date().getFullYear();
  });
  const reveals = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries) =>
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("show");
            observer.unobserve(entry.target);
          }
        }),
      { threshold: 0.06 },
    );
    reveals.forEach((element) => observer.observe(element));
  } else reveals.forEach((element) => element.classList.add("show"));
  const form = document.getElementById("enquiry");
  const status = document.getElementById("formstatus");
  if (!form) return;
  form.dataset.demoLocal = "true";
  const showStatus = (text) => {
    if (status) {
      status.textContent = text;
      status.style.whiteSpace = "pre-wrap";
    }
  };
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const data = new FormData(form);
    const field = (name) => String(data.get(name) || "");
    const message = [
      "Eden website enquiry: " + field("type"),
      "",
      "Name: " + field("name"),
      "Email: " + field("email"),
      "Phone: " + field("phone"),
      "Occasion: " + field("type"),
      "Preferred date: " + field("date"),
      "Budget: " + field("budget"),
      "Message:",
      field("message"),
    ].join("\n");
    if (navigator.clipboard && window.isSecureContext) {
      try {
        await navigator.clipboard.writeText(message);
        showStatus("Enquiry copied. Call Eden on 0113 258 7007 to discuss it.");
      } catch {
        showStatus(message);
      }
    } else showStatus(message);
  });
})();
