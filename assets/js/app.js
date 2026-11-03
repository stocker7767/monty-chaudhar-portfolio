(() => {
  "use strict";
  const projects = window.MONTY_PROJECTS || [];
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [
    ...root.querySelectorAll(selector),
  ];
  const escape = (value) =>
    String(value).replace(
      /[&<>"']/g,
      (char) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[char],
    );
  const repository = (p) =>
    p.codeUrl ||
    (p.repo
      ? `https://github.com/stocker7767/${encodeURIComponent(p.repo)}`
      : "");
  const storage = {
    get(key) {
      try {
        return localStorage.getItem(key);
      } catch {
        return null;
      }
    },
    set(key, value) {
      try {
        localStorage.setItem(key, value);
      } catch {}
    },
  };
  const categoryNames = {
    wordpress: "WordPress",
    business: "Business website",
    interactive: "Interactive",
    practice: "Early work",
  };
  let filter = "featured",
    currentProject = null,
    heroProject = window.MONTY_CONFIG?.heroProject || projects[0]?.id,
    toastTimer,
    playClicks = 0;
  let layout = "grid",
    previewObserver = null,
    previewResize = null;
  document.documentElement.classList.add("js-ready");

  function cover(p, index = projects.indexOf(p)) {
    const flower = `<svg viewBox="0 0 600 350" fill="none" aria-hidden="true"><g stroke="currentColor" stroke-width="1.2" opacity=".4"><path d="M475 350C430 225 435 180 480 130M480 220c-80 0-90-40-85-75 45 5 80 30 85 75zM462 260c70-10 90-45 80-75-40 7-73 30-80 75z"/><ellipse cx="480" cy="109" rx="23" ry="58"/><ellipse cx="480" cy="109" rx="23" ry="58" transform="rotate(60 480 109)"/><ellipse cx="480" cy="109" rx="23" ry="58" transform="rotate(120 480 109)"/><path d="M40 300c80-75 100-145 55-230M86 175c-55-10-65-45-43-70 30 0 47 29 43 70z"/></g></svg>`;
    const rings = `<svg viewBox="0 0 600 350" fill="none" aria-hidden="true"><g stroke="currentColor" stroke-width="1" opacity=".22"><ellipse cx="300" cy="175" rx="225" ry="120"/><ellipse cx="300" cy="175" rx="225" ry="120" transform="rotate(32 300 175)"/><ellipse cx="300" cy="175" rx="225" ry="120" transform="rotate(-32 300 175)"/><circle cx="300" cy="175" r="120"/></g></svg>`;
    const arches = `<svg viewBox="0 0 600 350" fill="none" aria-hidden="true"><g stroke="currentColor" opacity=".3" stroke-width="1.5"><path d="M340 350V150a100 100 0 01200 0v200M365 350V150a75 75 0 01150 0v200M390 350V150a50 50 0 01100 0v200M415 350V150a25 25 0 0150 0v200"/></g></svg>`;
    const deco = /flowers|bright/.test(p.id)
      ? flower
      : p.id === "abhika-aura"
        ? arches
        : rings;
    return `<div class="cover cover-${escape(p.id)}" style="--cover-bg:${p.colour};--cover-ink:${p.ink}"><span class="cover-index">PROJECT / ${String(index + 1).padStart(2, "0")}</span><div class="cover-decoration">${deco}</div><div class="cover-wordmark">${escape(p.wordmark).replace(/\n/g, "<br>")}${p.id === "chirpy" ? "<small>little things. big joy.</small>" : ""}</div><span class="cover-label">${escape(p.caption)}</span><span class="cover-arrow" aria-hidden="true">↗</span></div>`;
  }

  function setFilter(value) {
    filter = value;
    $$("[data-filter]").forEach((button) =>
      button.setAttribute(
        "aria-pressed",
        String(button.dataset.filter === filter),
      ),
    );
  }
  function projectVisual(p, context = "card") {
    if (!p.path) {
      if (p.id !== "gotitmate") return cover(p);
      return `<div class="workflow-study" style="--study-colour:${p.colour}"><div class="study-sidebar"><span class="study-logo">gm<span>↗</span></span><span>Overview</span><span class="study-active">Find a service</span><span>My bookings</span><span>Messages</span><span>Account</span></div><div class="study-main"><span class="study-caption">GOTITMATE / INTERFACE STUDY</span><h4>Your next job.<br>One place to start.</h4><div class="study-search"><span>What do you need a hand with?</span><span>↗</span></div><div class="study-trades"><span>⌁<small>Plumbing</small></span><span>ϟ<small>Electrical</small></span><span>⌂<small>Carpentry</small></span></div><div class="study-estimate"><i>✓</i><div><strong>A clearer booking journey</strong><small>Discover · Request · Review estimate</small></div><span>↗</span></div></div><span class="study-note">UI illustration · WordPress project in development</span></div>`;
    }
    return `<div class="site-showcase showcase-${context}" style="--showcase-colour:${p.colour};--showcase-ink:${p.ink}">${cover(p)}<div class="website-window"><div class="mini-browser-bar"><span><i></i><i></i><i></i></span><small>${escape(p.name)}</small><b>↗</b></div><div class="site-frame-holder"><iframe class="site-preview-frame" data-preview-src="${escape(p.path)}" title="Visual preview of ${escape(p.name)}" tabindex="-1" aria-hidden="true" sandbox="allow-same-origin" loading="lazy"></iframe></div></div><span class="preview-footnote">${p.category === "wordpress" ? "ORIGINAL HTML BUILD" : "ACTUAL WEBSITE PREVIEW"} <span>↗</span></span></div>`;
  }
  function hydratePreviews() {
    if (!previewResize && "ResizeObserver" in window)
      previewResize = new ResizeObserver((entries) =>
        entries.forEach(({ target }) => sizeFrame(target)),
      );
    function sizeFrame(holder) {
      const frame = $("iframe", holder);
      if (!frame || !holder.clientWidth) return;
      const scale = holder.clientWidth / 1280;
      frame.style.setProperty("--frame-scale", scale);
      frame.style.height = `${Math.max(900, holder.clientHeight / scale)}px`;
    }
    previewResize?.disconnect();
    $$(".site-frame-holder").forEach((holder) => {
      sizeFrame(holder);
      previewResize?.observe(holder);
    });
    previewObserver?.disconnect();
    if ("IntersectionObserver" in window)
      previewObserver = new IntersectionObserver(
        (entries) =>
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              loadFrame(entry.target);
              previewObserver.unobserve(entry.target);
            }
          }),
        { rootMargin: "180px 0px", threshold: 0.01 },
      );
    function loadFrame(frame) {
      const path = frame.dataset.previewSrc;
      if (!path) return;
      frame.addEventListener(
        "load",
        () => {
          try {
            const doc = frame.contentDocument;
            if (doc) {
              const style = doc.createElement("style");
              style.textContent =
                "html{scroll-behavior:auto!important}.reveal,[data-reveal]{opacity:1!important;transform:none!important}*,*:before,*:after{animation-play-state:paused!important;caret-color:transparent!important}";
              doc.head.append(style);
            }
          } catch {}
          frame.closest(".site-showcase")?.classList.add("preview-ready");
        },
        { once: true },
      );
      frame.src = path;
      delete frame.dataset.previewSrc;
    }
    $$(".site-preview-frame[data-preview-src]").forEach((frame) =>
      previewObserver ? previewObserver.observe(frame) : loadFrame(frame),
    );
  }
  function renderProjects() {
    const query = $("#project-search").value.trim().toLowerCase();
    const items = projects.filter(
      (p) =>
        (filter === "all" ||
          (filter === "featured" ? p.featured : p.category === filter)) &&
        `${p.name} ${p.tags.join(" ")} ${p.line} ${p.category}`
          .toLowerCase()
          .includes(query),
    );
    const featuredOrder = [
      "abhika-aura",
      "chirpy",
      "gotitmate",
      "royal-barber",
      "eden-bespoke-flowers",
      "the-black-rat",
    ];
    if (filter === "featured")
      items.sort(
        (a, b) => featuredOrder.indexOf(a.id) - featuredOrder.indexOf(b.id),
      );
    $("#project-grid").classList.toggle(
      "editorial-grid",
      filter === "featured" && !query,
    );
    $("#project-grid").classList.toggle("list-view", layout === "list");
    $("#project-grid").innerHTML = items
      .map(
        (p, i) =>
          `<article class="project-card" style="animation-delay:${Math.min(i * 40, 200)}ms"><div class="project-visual">${projectVisual(p)}<button class="visual-open" data-open="${escape(p.id)}" aria-label="Explore ${escape(p.name)}"><span class="view-cursor">Take a look <span>↗</span></span></button></div><div class="project-card-info"><div class="card-kicker"><span>${String(projects.indexOf(p) + 1).padStart(2, "0")} / ${escape(p.type)}</span><span class="case-marker" aria-hidden="true">↗</span></div><div class="project-card-top"><h3>${escape(p.name)}</h3><span class="project-category">${categoryNames[p.category]}</span></div><p>${escape(p.line)}</p><div class="card-bottom"><div class="card-tags">${p.tags
            .slice(0, 2)
            .map((tag) => `<span>${escape(tag)}</span>`)
            .join(
              "",
            )}</div><div class="card-links">${p.path ? `<a href="${escape(p.path)}" target="_blank" rel="noopener noreferrer" aria-label="View ${escape(p.name)} website">View website ↗</a>` : `<button data-open="${escape(p.id)}">Project details ↗</button>`}${repository(p) ? `<a href="${escape(repository(p))}" target="_blank" rel="noopener noreferrer" aria-label="View ${escape(p.name)} source code">Code &lt;/&gt;</a>` : ""}</div></div></div></article>`,
      )
      .join("");
    $("#result-count").textContent =
      `${String(items.length).padStart(2, "0")} PROJECT${items.length === 1 ? "" : "S"} / ${filter === "featured" ? "A FEW FAVOURITES" : filter === "all" ? "THE WHOLE COLLECTION" : categoryNames[filter].toUpperCase()}`;
    $("#empty-state").hidden = items.length !== 0;
    $("#show-all").hidden = filter === "all" && !query;
    hydratePreviews();
  }
  function setLayout(value) {
    layout = value;
    $("#grid-view").setAttribute("aria-pressed", String(layout === "grid"));
    $("#list-view").setAttribute("aria-pressed", String(layout === "list"));
    $("#project-grid").classList.toggle("list-view", layout === "list");
    requestAnimationFrame(hydratePreviews);
  }
  $("#grid-view").addEventListener("click", () => setLayout("grid"));
  $("#list-view").addEventListener("click", () => setLayout("list"));
  $$("[data-filter]").forEach((button) =>
    button.addEventListener("click", () => {
      setFilter(button.dataset.filter);
      renderProjects();
    }),
  );
  $("#project-search").addEventListener("input", () => {
    if (filter === "featured" && $("#project-search").value.trim())
      setFilter("all");
    renderProjects();
  });
  function showAll() {
    $("#project-search").value = "";
    setFilter("all");
    renderProjects();
  }
  $("#reset-search").addEventListener("click", showAll);
  $("#show-all").addEventListener("click", () => {
    showAll();
    $("#work").scrollIntoView({ behavior: motionOff() ? "auto" : "smooth" });
  });

  const dialog = $("#project-dialog");
  function openProject(id) {
    const p = projects.find((item) => item.id === id);
    if (!p) return;
    currentProject = p;
    $("#dialog-title").textContent = p.name;
    $("#dialog-type").textContent = p.type;
    $("#dialog-cover").innerHTML = projectVisual(p, "detail");
    $("#dialog-description").textContent = p.description;
    $("#dialog-note").textContent = p.note;
    $("#dialog-stack").innerHTML = p.tags
      .map((tag) => `<span>${escape(tag)}</span>`)
      .join("");
    $("#dialog-code").href = repository(p) || "#";
    $("#dialog-code").hidden = !repository(p);
    $("#dialog-live").hidden = !p.path;
    $("#dialog-live").href = p.path || "#";
    $("#dialog-live").target = "_blank";
    $("#dialog-live").rel = "noopener noreferrer";
    $("#preview-tab").disabled = !p.path;
    $("#preview-tab").textContent = p.path
      ? "Live preview"
      : "WordPress required";
    $("#preview-open").href = p.path || "#";
    $("#project-frame").removeAttribute("src");
    $("#preview-surface").classList.remove("mobile");
    $("#desktop-preview").setAttribute("aria-pressed", "true");
    $("#mobile-preview").setAttribute("aria-pressed", "false");
    setTab("overview");
    if (!dialog.open) dialog.showModal();
    hydratePreviews();
  }
  function setTab(tab) {
    if (tab === "preview" && !currentProject?.path) return;
    $("#project-overview").hidden = tab !== "overview";
    $("#project-preview").hidden = tab !== "preview";
    $$("[data-tab]").forEach((button) => {
      const selected = button.dataset.tab === tab;
      button.setAttribute("aria-selected", String(selected));
      button.tabIndex = selected ? 0 : -1;
    });
    if (tab === "preview" && !$("#project-frame").hasAttribute("src"))
      $("#project-frame").src = currentProject.path;
  }
  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-open]");
    if (button) openProject(button.dataset.open);
  });
  $$("[data-tab]").forEach((button) =>
    button.addEventListener("click", () => setTab(button.dataset.tab)),
  );
  $(".dialog-tabs").addEventListener("keydown", (event) => {
    if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) return;
    const tabs = $$("[data-tab]").filter((tab) => !tab.disabled);
    if (!tabs.includes(document.activeElement)) return;
    event.preventDefault();
    const next =
      event.key === "Home"
        ? tabs[0]
        : event.key === "End"
          ? tabs.at(-1)
          : tabs[
              (tabs.indexOf(document.activeElement) +
                (event.key === "ArrowRight" ? 1 : -1) +
                tabs.length) %
                tabs.length
            ];
    setTab(next.dataset.tab);
    next.focus();
  });
  $("#dialog-close").addEventListener("click", () => dialog.close());
  dialog.addEventListener("close", () => {
    $("#project-frame").removeAttribute("src");
    currentProject = null;
  });
  dialog.addEventListener("click", (event) => {
    if (event.target === dialog) {
      const r = dialog.getBoundingClientRect();
      if (
        event.clientX < r.left ||
        event.clientX > r.right ||
        event.clientY < r.top ||
        event.clientY > r.bottom
      )
        dialog.close();
    }
  });
  function screenSize(mobile) {
    $("#preview-surface").classList.toggle("mobile", mobile);
    $("#mobile-preview").setAttribute("aria-pressed", String(mobile));
    $("#desktop-preview").setAttribute("aria-pressed", String(!mobile));
  }
  $("#desktop-preview").addEventListener("click", () => screenSize(false));
  $("#mobile-preview").addEventListener("click", () => screenSize(true));

  function updateHero(id) {
    const p = projects.find((item) => item.id === id);
    if (!p) return;
    heroProject = id;
    $("#hero-art").innerHTML = projectVisual(p, "hero");
    $("#hero-address").textContent = `projects / ${id}`;
    $$("[data-hero]").forEach((button) =>
      button.setAttribute("aria-pressed", String(button.dataset.hero === id)),
    );
    hydratePreviews();
  }
  $$("[data-hero]").forEach((button) =>
    button.addEventListener("click", () => updateHero(button.dataset.hero)),
  );
  $("#hero-explore").addEventListener("click", () => openProject(heroProject));
  $("#hero-scene").addEventListener("pointermove", (event) => {
    if (event.pointerType !== "mouse" || motionOff()) return;
    const r = event.currentTarget.getBoundingClientRect();
    const x = (event.clientX - r.left) / r.width - 0.5,
      y = (event.clientY - r.top) / r.height - 0.5;
    $("#hero-browser").style.transform =
      `rotateX(${-y * 8}deg) rotateY(${x * 9}deg) rotate(-6deg)`;
  });
  $("#hero-scene").addEventListener("pointerleave", () => {
    $("#hero-browser").style.transform = "";
  });
  document.addEventListener(
    "pointermove",
    (event) => {
      if (event.pointerType !== "mouse" || motionOff()) return;
      const visual = event.target.closest(".project-visual");
      if (!visual) return;
      const bounds = visual.getBoundingClientRect();
      visual.style.setProperty(
        "--cursor-x",
        `${Math.max(80, Math.min(bounds.width - 80, event.clientX - bounds.left))}px`,
      );
      visual.style.setProperty(
        "--cursor-y",
        `${Math.max(35, Math.min(bounds.height - 35, event.clientY - bounds.top))}px`,
      );
    },
    { passive: true },
  );
  $$(".magnetic").forEach((button) => {
    button.addEventListener("pointermove", (event) => {
      if (event.pointerType !== "mouse" || motionOff()) return;
      const r = button.getBoundingClientRect();
      button.style.transform = `translate(${((event.clientX - r.left) / r.width - 0.5) * 13}px,${((event.clientY - r.top) / r.height - 0.5) * 9}px)`;
    });
    button.addEventListener("pointerleave", () => {
      button.style.transform = "";
    });
  });
  $$("[data-sticker]").forEach((sticker) => {
    let x = 0,
      y = 0,
      start = null;
    const angle = sticker.classList.contains("sticker-js") ? 13 : -13;
    function move(dx, dy) {
      const scene = $("#hero-scene");
      x = Math.max(
        -sticker.offsetLeft + 4,
        Math.min(
          scene.clientWidth - sticker.offsetLeft - sticker.offsetWidth - 4,
          dx,
        ),
      );
      y = Math.max(
        -sticker.offsetTop + 15,
        Math.min(
          scene.clientHeight - sticker.offsetTop - sticker.offsetHeight - 40,
          dy,
        ),
      );
      sticker.style.transform = `translate(${x}px,${y}px) rotate(${angle}deg)`;
    }
    sticker.addEventListener("pointerdown", (event) => {
      start = { px: event.clientX, py: event.clientY, x, y };
      sticker.setPointerCapture(event.pointerId);
    });
    sticker.addEventListener("pointermove", (event) => {
      if (start)
        move(
          start.x + event.clientX - start.px,
          start.y + event.clientY - start.py,
        );
    });
    sticker.addEventListener("pointerup", () => {
      start = null;
    });
    sticker.addEventListener("pointercancel", () => {
      start = null;
    });
    sticker.addEventListener("keydown", (event) => {
      const delta = {
        ArrowLeft: [-12, 0],
        ArrowRight: [12, 0],
        ArrowUp: [0, -12],
        ArrowDown: [0, 12],
      }[event.key];
      if (delta) {
        event.preventDefault();
        move(x + delta[0], y + delta[1]);
      }
    });
  });

  $$("[data-colour]").forEach((button) =>
    button.addEventListener("click", () => {
      $("#play-card").style.setProperty("--play-colour", button.dataset.colour);
      $$("[data-colour]").forEach((item) =>
        item.setAttribute("aria-pressed", String(item === button)),
      );
    }),
  );
  $("#corner-range").addEventListener("input", (event) => {
    const value = `${event.target.value}px`;
    $("#corner-value").textContent = value;
    $("#play-card").style.setProperty("--play-radius", value);
  });
  $("#play-button").addEventListener("click", () => {
    playClicks++;
    $("#play-feedback").textContent =
      playClicks === 1
        ? "One click. A little spark. Nice."
        : `${playClicks} clicks. Curiosity looks good on you.`;
    if (motionOff()) return;
    for (let i = 0; i < 15; i++) {
      const particle = document.createElement("i");
      particle.className = "confetti";
      particle.style.cssText = `left:50%;top:52%;background:${["#d0fa79", "#b6a5f5", "#ffad87", "#93d7e8"][i % 4]};--tx:${(Math.random() - 0.5) * 330}px;--ty:${-60 - Math.random() * 170}px;--rotation:${Math.random() * 600}deg`;
      $("#play-stage").append(particle);
      particle.addEventListener("animationend", () => particle.remove(), {
        once: true,
      });
    }
  });

  const media = matchMedia("(prefers-reduced-motion: reduce)");
  function motionOff() {
    return (
      document.documentElement.classList.contains("motion-off") || media.matches
    );
  }
  function updateMotion() {
    const off = motionOff();
    $("#motion-toggle").textContent = `Motion: ${off ? "off" : "on"}`;
    $("#motion-toggle").setAttribute("aria-pressed", String(off));
  }
  if (storage.get("monty-motion") === "off")
    document.documentElement.classList.add("motion-off");
  $("#motion-toggle").addEventListener("click", () => {
    if (media.matches) {
      toast("Reduced motion follows your device settings.");
      return;
    }
    document.documentElement.classList.toggle("motion-off");
    storage.set("monty-motion", motionOff() ? "off" : "on");
    updateMotion();
  });
  media.addEventListener("change", updateMotion);
  updateMotion();
  function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    $("#theme-toggle").setAttribute(
      "aria-label",
      `Switch to ${theme === "dark" ? "light" : "dark"} theme`,
    );
    $('meta[name="theme-color"]').content =
      theme === "dark" ? "#151713" : "#f3f2e9";
  }
  applyTheme(storage.get("monty-theme") === "light" ? "light" : "dark");
  $("#theme-toggle").addEventListener("click", () => {
    const theme =
      document.documentElement.dataset.theme === "dark" ? "light" : "dark";
    applyTheme(theme);
    storage.set("monty-theme", theme);
  });
  function toast(message) {
    $("#toast").textContent = message;
    $("#toast").classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => $("#toast").classList.remove("show"), 3000);
  }
  $("#copy-email").addEventListener("click", async () => {
    const email = window.MONTY_CONFIG?.email || "letsmailmonti@gmail.com";
    try {
      await navigator.clipboard.writeText(email);
      toast("Email address copied.");
    } catch {
      const field = document.createElement("textarea");
      field.value = email;
      field.style.cssText = "position:fixed;opacity:0;left:-9999px";
      document.body.append(field);
      field.select();
      let copied = false;
      try {
        copied = document.execCommand("copy");
      } catch {}
      field.remove();
      toast(copied ? "Email address copied." : email);
    }
  });

  const command = $("#command-dialog");
  const destinations = [
    { name: "Selected work", detail: "Section", anchor: "#work" },
    { name: "About Monty", detail: "Section", anchor: "#about" },
    { name: "Playground", detail: "Section", anchor: "#playground" },
    { name: "Get in touch", detail: "Section", anchor: "#contact" },
    ...projects.map((p) => ({
      name: p.name,
      detail: categoryNames[p.category],
      id: p.id,
    })),
  ];
  function renderCommands() {
    const query = $("#command-search").value.toLowerCase().trim();
    const matches = destinations.filter((item) =>
      `${item.name} ${item.detail}`.toLowerCase().includes(query),
    );
    $("#command-results").innerHTML =
      matches
        .map(
          (item) =>
            `<button ${item.id ? `data-command-project="${item.id}"` : `data-command-anchor="${item.anchor}"`}><span>${escape(item.name)}</span><small>${escape(item.detail)} ↗</small></button>`,
        )
        .join("") || "<p>No matching destinations.</p>";
  }
  function openCommands() {
    if (dialog.open) dialog.close();
    $("#command-search").value = "";
    renderCommands();
    if (!command.open) command.showModal();
    $("#command-search").focus();
  }
  $("#command-open").addEventListener("click", openCommands);
  $("#command-close").addEventListener("click", () => command.close());
  $("#command-search").addEventListener("input", renderCommands);
  $("#command-results").addEventListener("click", (event) => {
    const button = event.target.closest("button");
    if (!button) return;
    command.close();
    if (button.dataset.commandProject)
      openProject(button.dataset.commandProject);
    else
      $(button.dataset.commandAnchor)?.scrollIntoView({
        behavior: motionOff() ? "auto" : "smooth",
      });
  });
  command.addEventListener("keydown", (event) => {
    const buttons = $$(
      "button[data-command-project],button[data-command-anchor]",
      command,
    );
    if (event.key === "ArrowDown" || event.key === "ArrowUp") {
      event.preventDefault();
      const index = buttons.indexOf(document.activeElement);
      buttons[
        (index + (event.key === "ArrowDown" ? 1 : -1) + buttons.length) %
          buttons.length
      ]?.focus();
    } else if (
      event.key === "Enter" &&
      document.activeElement === $("#command-search")
    ) {
      event.preventDefault();
      buttons[0]?.click();
    }
  });
  document.addEventListener("keydown", (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
      event.preventDefault();
      command.open ? command.close() : openCommands();
    }
    if (
      event.key === "/" &&
      !event.ctrlKey &&
      !event.metaKey &&
      !["INPUT", "TEXTAREA", "SELECT"].includes(
        document.activeElement?.tagName,
      ) &&
      !dialog.open &&
      !command.open
    ) {
      event.preventDefault();
      $("#work").scrollIntoView();
      $("#project-search").focus();
    }
  });

  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1 },
    );
    $$(".reveal").forEach((item) => observer.observe(item));
  } else $$(".reveal").forEach((item) => item.classList.add("visible"));
  let scrolling = false;
  function progress() {
    const height = document.documentElement.scrollHeight - innerHeight;
    $("#reading-progress").style.width =
      `${height > 0 ? (scrollY / height) * 100 : 0}%`;
    scrolling = false;
  }
  addEventListener(
    "scroll",
    () => {
      if (!scrolling) {
        scrolling = true;
        requestAnimationFrame(progress);
      }
    },
    { passive: true },
  );
  addEventListener("resize", progress);
  progress();
  renderProjects();
  updateHero(heroProject);
  const ribbon = $(".skills-ribbon>div");
  if (ribbon) {
    const original = document.createElement("div");
    original.className = "ticker-group";
    while (ribbon.firstChild) original.append(ribbon.firstChild);
    const duplicate = original.cloneNode(true);
    duplicate.setAttribute("aria-hidden", "true");
    ribbon.classList.add("ticker-track");
    ribbon.append(original, duplicate);
  }
})();
