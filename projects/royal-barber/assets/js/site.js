const $ = (s, c = document) => c.querySelector(s),
  $$ = (s, c = document) => [...c.querySelectorAll(s)];
const menu = $(".hamb"),
  nav = $(".nav");
if (menu) {
  menu.addEventListener("click", () => {
    nav.classList.toggle("open");
    menu.setAttribute("aria-expanded", nav.classList.contains("open"));
  });
}
$$(".nav a").forEach((a) =>
  a.addEventListener("click", () => nav?.classList.remove("open")),
);
const io = new IntersectionObserver(
  (es) =>
    es.forEach((e) => {
      if (e.isIntersecting) e.target.classList.add("show");
    }),
  { threshold: 0.08 },
);
$$(".reveal").forEach((x) => io.observe(x));
// gallery lightbox
const lb = $(".lightbox");
$$(".gallery a").forEach((a) =>
  a.addEventListener("click", (e) => {
    e.preventDefault();
    if (!lb) return;
    $("img", lb).src = $("img", a).src;
    lb.classList.add("open");
  }),
);
if (lb) {
  $("button", lb).onclick = () => lb.classList.remove("open");
  lb.addEventListener("click", (e) => {
    if (e.target === lb) lb.classList.remove("open");
  });
}
// current year
$$("[data-year]").forEach((x) => (x.textContent = new Date().getFullYear()));
