(() => {
  "use strict";
  const script =
    document.currentScript ||
    [...document.scripts].find((item) => /assets\/js\/demo\.js/.test(item.src));
  const themeRoot = script
    ? new URL("../../", script.src).href
    : new URL("./", location.href).href;
  const storageKey = `monty-portfolio-demo:${themeRoot}`;
  let context = window.MONTY_DEMO || null;
  if (context) {
    try {
      sessionStorage.setItem(
        storageKey,
        JSON.stringify({ home: context.home }),
      );
    } catch {}
  } else {
    try {
      const saved = JSON.parse(sessionStorage.getItem(storageKey) || "null");
      const projectPrefix = new URL("projects/", themeRoot).pathname;
      const relative = location.pathname.startsWith(projectPrefix)
        ? location.pathname.slice(projectPrefix.length)
        : "";
      const slug = relative.split("/")[0];
      if (saved?.home && slug) {
        const entry = new URL(saved.home);
        entry.hash = "";
        entry.search = "";
        entry.searchParams.set("monty_demo", slug);
        context = {
          home: saved.home,
          entry: entry.href,
          slug,
          assetRoot: new URL(`projects/${slug}/`, themeRoot).href,
        };
      }
    } catch {}
  }
  const traditionalRoot = new URL(themeRoot);
  const contentIndex = traditionalRoot.pathname.indexOf("/wp-content/");
  traditionalRoot.pathname =
    contentIndex >= 0
      ? traditionalRoot.pathname.slice(0, contentIndex + 1)
      : "/";
  traditionalRoot.hash = "work";
  const home = context?.home || traditionalRoot.href;
  if (window.self === window.top) {
    const notice = document.createElement("aside");
    notice.className = "monty-demo-notice";
    notice.setAttribute("aria-label", "Portfolio demonstration");
    const label = document.createElement("span");
    label.textContent = "Portfolio demo";
    const link = document.createElement("a");
    link.href = home;
    link.textContent = "Back to Monty ↗";
    const close = document.createElement("button");
    close.type = "button";
    close.setAttribute("aria-label", "Dismiss demo notice");
    close.textContent = "×";
    close.addEventListener("click", () => notice.remove());
    notice.append(label, link, close);
    document.body.append(notice);
  }
  // Preserve the WordPress demo route for links created by the original projects' JavaScript.
  if (context) {
    document.addEventListener(
      "click",
      (event) => {
        const anchor = event.target.closest("a[href]");
        if (!anchor) return;
        let url;
        try {
          url = new URL(anchor.getAttribute("href"), document.baseURI);
        } catch {
          return;
        }
        const root = new URL(context.assetRoot);
        if (
          url.origin !== root.origin ||
          !url.pathname.startsWith(root.pathname) ||
          !/.+\.html?$/i.test(url.pathname)
        )
          return;
        const page = decodeURIComponent(
          url.pathname.slice(root.pathname.length),
        );
        const routed = new URL(context.entry);
        routed.search = url.search;
        routed.searchParams.set("monty_demo", context.slug);
        if (page !== "index.html")
          routed.searchParams.set("monty_demo_page", page);
        else routed.searchParams.delete("monty_demo_page");
        routed.hash = url.hash;
        anchor.href = routed.href;
      },
      true,
    );
  }
  document.addEventListener(
    "submit",
    (event) => {
      if (context?.allowForms) return;
      if (
        event.target instanceof HTMLFormElement &&
        event.target.dataset.demoLocal === "true"
      )
        return;
      event.preventDefault();
      event.stopImmediatePropagation();
      document.querySelector(".monty-demo-message")?.remove();
      const message = document.createElement("div");
      message.className = "monty-demo-message";
      message.setAttribute("role", "status");
      message.textContent =
        "This is a portfolio demonstration. Your message has not been sent.";
      document.body.append(message);
      setTimeout(() => message.remove(), 5000);
    },
    true,
  );
})();
