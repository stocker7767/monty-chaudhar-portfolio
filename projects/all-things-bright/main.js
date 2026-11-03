const menu = document.querySelector(".menu"),
  links = document.querySelector(".links");
if (menu)
  menu.addEventListener("click", () => {
    links.classList.toggle("open");
    menu.setAttribute("aria-expanded", links.classList.contains("open"));
  });
const form = document.querySelector("#enquiry-form");
if (form)
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const data = new FormData(form);
    const subject = encodeURIComponent(
      "Floral enquiry — " + data.get("occasion"),
    );
    const body = encodeURIComponent(
      `Name: ${data.get("name")}\nEmail: ${data.get("email")}\nTelephone: ${data.get("phone")}\nOccasion: ${data.get("occasion")}\nPreferred date: ${data.get("date")}\nBudget: ${data.get("budget")}\nMessage: ${data.get("message")}`,
    );
    document.querySelector(".form-message").textContent =
      "Your email app will open with your enquiry. Please send the email to complete your request.";
    location.href = `mailto:?subject=${subject}&body=${body}`;
  });
