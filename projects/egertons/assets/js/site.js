const menu = document.querySelector(".menu");
const links = document.querySelector(".nav-links");
if (menu && links)
  menu.addEventListener("click", () => links.classList.toggle("open"));
document
  .querySelectorAll('a[href^="#"]')
  .forEach((a) =>
    a.addEventListener("click", () => links?.classList.remove("open")),
  );
const form = document.querySelector("#contact-form");
if (form) {
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    document.querySelector("#form-msg").textContent =
      "Demo form only — connect Formspree, Netlify Forms or email before launch.";
  });
}
