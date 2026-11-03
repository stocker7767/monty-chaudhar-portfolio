const PRODUCTS = [
  {
    id: 1,
    name: "Rainbow Ceramic Vase",
    price: 28,
    category: "Homeware",
    emoji: "\ud83c\udffa",
  },
  {
    id: 2,
    name: "Happy Days Mug",
    price: 16,
    category: "Homeware",
    emoji: "\u2615",
  },
  {
    id: 3,
    name: "Colour Pop Candle",
    price: 19,
    category: "Homeware",
    emoji: "\ud83d\udd6f\ufe0f",
  },
  {
    id: 4,
    name: "Abstract Art Print",
    price: 24,
    category: "Art",
    emoji: "\ud83d\uddbc\ufe0f",
  },
  {
    id: 5,
    name: "Little Joy Notebook",
    price: 12,
    category: "Stationery",
    emoji: "\ud83d\udcd2",
  },
  {
    id: 6,
    name: "Birthday Wishes Card",
    price: 4,
    category: "Cards",
    emoji: "\ud83d\udc8c",
  },
  {
    id: 7,
    name: "Flower Press Kit",
    price: 22,
    category: "Gifts",
    emoji: "\ud83c\udf38",
  },
  {
    id: 8,
    name: "Creative Gift Box",
    price: 35,
    category: "Gifts",
    emoji: "\ud83c\udf81",
  },
  {
    id: 9,
    name: "Colourful Coasters",
    price: 18,
    category: "Homeware",
    emoji: "\ud83c\udf08",
  },
  {
    id: 10,
    name: "Leeds Love Print",
    price: 25,
    category: "Art",
    emoji: "\ud83c\udfa8",
  },
  {
    id: 11,
    name: "Desk Planner",
    price: 15,
    category: "Stationery",
    emoji: "\ud83d\uddd3\ufe0f",
  },
  {
    id: 12,
    name: "Celebration Card",
    price: 4,
    category: "Cards",
    emoji: "\ud83c\udf89",
  },
];
const KEY = "chirpy-concept-cart-v2";
function cart() {
  try {
    return JSON.parse(localStorage.getItem(KEY)) || {};
  } catch (e) {
    return {};
  }
}
function save(c) {
  localStorage.setItem(KEY, JSON.stringify(c));
  count();
}
function count() {
  let e = document.getElementById("cartcount");
  if (e) e.textContent = Object.values(cart()).reduce((a, b) => a + b, 0);
}
function add(id) {
  let c = cart();
  c[id] = (c[id] || 0) + 1;
  save(c);
  const b = document.querySelector('[data-id="' + id + '"]');
  if (b) {
    b.textContent = "Added ✓";
    setTimeout(() => (b.textContent = "Add to demo bag +"), 1000);
  }
}
function productCard(p) {
  return `<article class="product"><div class="art" aria-hidden="true">${p.emoji}</div><div class="details"><span class="tag">${p.category} · Demo item</span><h3>${p.name}</h3><div class="price">£${p.price.toFixed(2)}</div><button class="btn yellow" data-id="${p.id}" onclick="add(${p.id})">Add to demo bag +</button></div></article>`;
}
function shop() {
  let el = document.getElementById("catalog");
  if (!el) return;
  let cats = ["All", ...new Set(PRODUCTS.map((p) => p.category))],
    filter = new URLSearchParams(location.search).get("category") || "All",
    search = "";
  let f = document.getElementById("filters");
  f.innerHTML = cats
    .map(
      (c) =>
        `<button data-cat="${c}" class="${filter === c ? "active" : ""}" aria-pressed="${filter === c}">${c}</button>`,
    )
    .join("");
  function render() {
    let ps = PRODUCTS.filter(
      (p) =>
        (filter === "All" || p.category === filter) &&
        p.name.toLowerCase().includes(search),
    );
    el.innerHTML =
      ps.map(productCard).join("") ||
      "<p>No matching goodies. Try another search.</p>";
    document.getElementById("resultcount").textContent =
      ps.length + " demo products";
    f.querySelectorAll("button").forEach((b) => {
      b.classList.toggle("active", b.dataset.cat === filter);
      b.setAttribute("aria-pressed", String(b.dataset.cat === filter));
    });
  }
  f.onclick = (e) => {
    if (e.target.dataset.cat) {
      filter = e.target.dataset.cat;
      render();
    }
  };
  document.getElementById("search").oninput = (e) => {
    search = e.target.value.toLowerCase();
    render();
  };
  render();
}
function change(id, d) {
  let c = cart();
  c[id] = Math.max(0, (c[id] || 0) + d);
  if (!c[id]) delete c[id];
  save(c);
  renderCart();
}
function renderCart() {
  let el = document.getElementById("cartitems");
  if (!el) return;
  let c = cart(),
    ids = Object.keys(c),
    sum = 0;
  if (!ids.length)
    el.innerHTML =
      '<div class="empty">Your demo bag is empty. <a href="shop.html"><strong>Explore the shop ↗</strong></a></div>';
  else
    el.innerHTML = ids
      .map((id) => {
        let p = PRODUCTS.find((x) => x.id == id);
        if (!p) return "";
        sum += p.price * c[id];
        return `<div class="cartrow"><div><strong>${p.emoji} ${p.name}</strong><br><small>£${p.price.toFixed(2)} each</small></div><div class="qty"><button aria-label="Remove one ${p.name}" onclick="change(${id},-1)">−</button>${c[id]}<button aria-label="Add one ${p.name}" onclick="change(${id},1)">+</button></div></div>`;
      })
      .join("");
  document.getElementById("subtotal").textContent =
    "Demo subtotal: £" + sum.toFixed(2);
}
function init() {
  count();
  shop();
  renderCart();
  let feat = document.getElementById("featured");
  if (feat) feat.innerHTML = PRODUCTS.slice(0, 4).map(productCard).join("");
  let m = document.getElementById("menu"),
    l = document.getElementById("links");
  m.onclick = () => {
    let open = l.classList.toggle("open");
    m.setAttribute("aria-expanded", open);
    m.textContent = open ? "✕" : "☰";
  };
  document.getElementById("year").textContent = new Date().getFullYear();
  let form = document.getElementById("contactform");
  if (form)
    form.onsubmit = (e) => {
      e.preventDefault();
      let v = (id) => document.getElementById(id).value;
      location.href =
        "mailto:hello@chirpystore.co.uk?subject=" +
        encodeURIComponent(v("subject")) +
        "&body=" +
        encodeURIComponent(
          "From: " + v("name") + " (" + v("email") + ")\n\n" + v("message"),
        );
    };
  if ("IntersectionObserver" in window) {
    let o = new IntersectionObserver((es) =>
      es.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add("show");
          o.unobserve(e.target);
        }
      }),
    );
    document.querySelectorAll(".reveal").forEach((e) => o.observe(e));
  } else
    document
      .querySelectorAll(".reveal")
      .forEach((e) => e.classList.add("show"));
}
document.addEventListener("DOMContentLoaded", init);
