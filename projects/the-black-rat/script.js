document.querySelector(".menu")?.addEventListener("click", () => {
  let n = document.querySelector(".navlinks");
  let open = n.classList.toggle("open");
  document.querySelector(".menu").setAttribute("aria-expanded", String(open));
});
document
  .querySelectorAll(".navlinks a")
  .forEach((a) =>
    a.addEventListener("click", () =>
      document.querySelector(".navlinks")?.classList.remove("open"),
    ),
  );
