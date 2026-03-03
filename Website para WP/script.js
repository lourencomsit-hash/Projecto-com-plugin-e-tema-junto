const header = document.getElementById("siteHeader");
const hero = document.getElementById("hero");
const navToggle = document.getElementById("navToggle");
const siteNav = document.getElementById("siteNav");
const storySection = document.getElementById("journey-design");
const storyImage = document.querySelector(".home-story-media img");

if (header && hero) {
  const syncHeaderOnScroll = () => {
    header.classList.toggle("is-solid", window.scrollY > 0);
  };

  window.addEventListener("scroll", syncHeaderOnScroll, { passive: true });
  syncHeaderOnScroll();
}

function closeMobileMenu() {
  if (!header || !navToggle) return;
  header.classList.remove("is-menu-open");
  navToggle.setAttribute("aria-expanded", "false");
}

if (navToggle && header && siteNav) {
  navToggle.addEventListener("click", () => {
    const isOpen = header.classList.toggle("is-menu-open");
    navToggle.setAttribute("aria-expanded", String(isOpen));
  });

  siteNav.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", closeMobileMenu);
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 980) closeMobileMenu();
  });
}

if (storySection && storyImage) {
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  const maxShift = 32;
  let currentY = 0;
  let targetY = 0;
  let rafId = 0;

  const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

  const renderParallax = () => {
    currentY += (targetY - currentY) * 0.14;
    storyImage.style.setProperty("--story-parallax-y", `${currentY.toFixed(2)}px`);

    if (Math.abs(targetY - currentY) > 0.08) {
      rafId = requestAnimationFrame(renderParallax);
    } else {
      rafId = 0;
    }
  };

  const queueRender = () => {
    if (!rafId) rafId = requestAnimationFrame(renderParallax);
  };

  const updateStoryParallax = () => {
    if (reducedMotion.matches) {
      targetY = 0;
      queueRender();
      return;
    }

    const rect = storySection.getBoundingClientRect();
    const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
    const progress = clamp((viewportHeight - rect.top) / (viewportHeight + rect.height), 0, 1);
    targetY = (progress * 2 - 1) * maxShift;
    queueRender();
  };

  const onReducedMotionChange = () => updateStoryParallax();

  window.addEventListener("scroll", updateStoryParallax, { passive: true });
  window.addEventListener("resize", updateStoryParallax);
  if (typeof reducedMotion.addEventListener === "function") {
    reducedMotion.addEventListener("change", onReducedMotionChange);
  } else if (typeof reducedMotion.addListener === "function") {
    reducedMotion.addListener(onReducedMotionChange);
  }
  updateStoryParallax();
}

const modal = document.getElementById("packageModal");
const modalContent = document.getElementById("modalContent");
const modalClose = document.getElementById("modalClose");
const cards = document.querySelectorAll(".interactive-card");
const parksCarousel = document.getElementById("parksCarousel");
const parksPrev = document.getElementById("parksPrev");
const parksNext = document.getElementById("parksNext");
const parksDots = document.getElementById("parksDots");
const packagesCarousel = document.getElementById("packagesCarousel");
const packagesPrev = document.getElementById("packagesPrev");
const packagesNext = document.getElementById("packagesNext");
const packagesDots = document.getElementById("packagesDots");
const accomCarousel = document.getElementById("accomCarousel");
const accomPrev = document.getElementById("accomPrev");
const accomNext = document.getElementById("accomNext");
const accomDots = document.getElementById("accomDots");
const accomLightbox = document.getElementById("accomLightbox");
const accomLightboxImage = document.getElementById("accomLightboxImage");
const accomLightboxClose = document.getElementById("accomLightboxClose");
const accomLightboxPrev = document.getElementById("accomLightboxPrev");
const accomLightboxNext = document.getElementById("accomLightboxNext");
const priceGrid = document.getElementById("priceGrid");
const packagePriceHeadline = document.getElementById("packagePriceHeadline");

const templateMap = {
  "ndutu-calving-6d": "tpl-ndutu-calving-6d",
  "luxury-migration-8d": "tpl-luxury-migration-8d",
  "tanzania-zanzibar-9d": "tpl-tanzania-zanzibar-9d"
};

function openModal(packageKey) {
  const tplId = templateMap[packageKey];
  if (!tplId || !modal || !modalContent) return;
  const tpl = document.getElementById(tplId);
  if (!tpl) return;

  modalContent.innerHTML = "";
  modalContent.appendChild(tpl.content.cloneNode(true));
  modal.classList.add("is-open");
  modal.setAttribute("aria-hidden", "false");
  document.body.classList.add("modal-open");
}

function closeModal() {
  if (!modal || !modalContent) return;
  modal.classList.remove("is-open");
  modal.setAttribute("aria-hidden", "true");
  document.body.classList.remove("modal-open");
  modalContent.innerHTML = "";
}

cards.forEach((card) => {
  card.addEventListener("click", () => openModal(card.dataset.package));
  card.addEventListener("keydown", (event) => {
    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      openModal(card.dataset.package);
    }
  });
});

if (modalClose) {
  modalClose.addEventListener("click", closeModal);
}

if (modal) {
  modal.addEventListener("click", (event) => {
    if (event.target === modal) closeModal();
  });
}

if (parksCarousel && parksPrev && parksNext) {
  const getStep = () => {
    const firstSlide = parksCarousel.querySelector(".park-slide");
    if (!firstSlide) return Math.max(320, Math.floor(parksCarousel.clientWidth * 0.85));
    const gap = Number.parseFloat(getComputedStyle(parksCarousel).gap || getComputedStyle(parksCarousel).columnGap || "0");
    return firstSlide.clientWidth + gap;
  };

  const getIndex = () => Math.round(parksCarousel.scrollLeft / getStep());

  const syncDots = () => {
    if (!parksDots) return;
    const index = getIndex();
    parksDots.querySelectorAll(".dot").forEach((dot, dotIndex) => {
      dot.classList.toggle("is-active", dotIndex === index);
    });
  };

  parksPrev.addEventListener("click", () => {
    parksCarousel.scrollBy({ left: -getStep(), behavior: "smooth" });
  });

  parksNext.addEventListener("click", () => {
    parksCarousel.scrollBy({ left: getStep(), behavior: "smooth" });
  });

  parksCarousel.addEventListener("scroll", syncDots, { passive: true });

  if (parksDots) {
    parksDots.querySelectorAll(".dot").forEach((dot) => {
      dot.addEventListener("click", () => {
        const index = Number(dot.getAttribute("data-index"));
        parksCarousel.scrollTo({ left: getStep() * index, behavior: "smooth" });
      });
    });
  }

  syncDots();
}

if (packagesCarousel && packagesPrev && packagesNext) {
  const packageCards = Array.from(packagesCarousel.querySelectorAll(".interactive-card"));
  const getCarouselPaddingLeft = () => Number.parseFloat(getComputedStyle(packagesCarousel).paddingLeft || "0") || 0;

  const getPackageIndex = () => {
    if (packageCards.length === 0) return 0;
    const targetLeft = packagesCarousel.scrollLeft + getCarouselPaddingLeft();
    let closestIndex = 0;
    let closestDistance = Number.POSITIVE_INFINITY;

    packageCards.forEach((card, index) => {
      const distance = Math.abs(card.offsetLeft - targetLeft);
      if (distance < closestDistance) {
        closestDistance = distance;
        closestIndex = index;
      }
    });

    return closestIndex;
  };

  const scrollToPackageIndex = (index, behavior = "smooth") => {
    if (packageCards.length === 0) return;
    const nextIndex = Math.max(0, Math.min(packageCards.length - 1, index));
    const paddingLeft = getCarouselPaddingLeft();
    const target = Math.max(0, packageCards[nextIndex].offsetLeft - paddingLeft);
    packagesCarousel.scrollTo({ left: target, behavior });
  };

  const syncPackageDots = () => {
    if (!packagesDots) return;
    const dots = packagesDots.querySelectorAll(".dot");
    const maxIndex = Math.max(0, dots.length - 1);
    const index = Math.max(0, Math.min(maxIndex, getPackageIndex()));
    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle("is-active", dotIndex === index);
    });
  };

  const ensurePackageStartVisible = () => {
    if (!window.matchMedia("(max-width: 980px)").matches) return;
    scrollToPackageIndex(0, "auto");
  };

  packagesPrev.addEventListener("click", () => {
    scrollToPackageIndex(getPackageIndex() - 1);
  });

  packagesNext.addEventListener("click", () => {
    scrollToPackageIndex(getPackageIndex() + 1);
  });

  packagesCarousel.addEventListener("scroll", syncPackageDots, { passive: true });

  if (packagesDots) {
    packagesDots.querySelectorAll(".dot").forEach((dot) => {
      dot.addEventListener("click", () => {
        const index = Number(dot.getAttribute("data-index"));
        scrollToPackageIndex(index);
      });
    });
  }

  requestAnimationFrame(ensurePackageStartVisible);
  window.addEventListener("pageshow", ensurePackageStartVisible);
  window.addEventListener("load", ensurePackageStartVisible);
  window.addEventListener("resize", ensurePackageStartVisible);
  setTimeout(ensurePackageStartVisible, 200);
  setTimeout(ensurePackageStartVisible, 900);
  syncPackageDots();
}

if (accomCarousel && accomPrev && accomNext) {
  const accomLinks = Array.from(accomCarousel.querySelectorAll(".accom-photo"));
  let activeAccomIndex = 0;

  const openAccomLightbox = (index) => {
    if (!accomLightbox || !accomLightboxImage || accomLinks.length === 0) return;
    activeAccomIndex = (index + accomLinks.length) % accomLinks.length;
    const current = accomLinks[activeAccomIndex];
    const image = current.querySelector("img");
    accomLightboxImage.src = current.getAttribute("href") || "";
    accomLightboxImage.alt = image ? image.alt : "Accommodation photo";
    accomLightbox.classList.add("is-open");
    accomLightbox.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
  };

  const closeAccomLightbox = () => {
    if (!accomLightbox || !accomLightboxImage) return;
    accomLightbox.classList.remove("is-open");
    accomLightbox.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
    accomLightboxImage.src = "";
  };

  const nextAccomImage = () => openAccomLightbox(activeAccomIndex + 1);
  const prevAccomImage = () => openAccomLightbox(activeAccomIndex - 1);

  accomLinks.forEach((link, index) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();
      openAccomLightbox(index);
    });
  });

  if (accomLightboxClose) accomLightboxClose.addEventListener("click", closeAccomLightbox);
  if (accomLightboxNext) accomLightboxNext.addEventListener("click", nextAccomImage);
  if (accomLightboxPrev) accomLightboxPrev.addEventListener("click", prevAccomImage);
  if (accomLightbox) {
    accomLightbox.addEventListener("click", (event) => {
      if (event.target === accomLightbox) closeAccomLightbox();
    });
  }

  const getAccomStep = () => {
    const firstCard = accomCarousel.querySelector(".accom-photo");
    if (!firstCard) return Math.max(320, Math.floor(accomCarousel.clientWidth * 0.85));
    const gap = Number.parseFloat(getComputedStyle(accomCarousel).gap || getComputedStyle(accomCarousel).columnGap || "0");
    return firstCard.clientWidth + gap;
  };

  const getAccomIndex = () => Math.round(accomCarousel.scrollLeft / getAccomStep());

  const syncAccomDots = () => {
    if (!accomDots) return;
    const index = getAccomIndex();
    accomDots.querySelectorAll(".dot").forEach((dot, dotIndex) => {
      dot.classList.toggle("is-active", dotIndex === index);
    });
  };

  accomPrev.addEventListener("click", () => {
    accomCarousel.scrollBy({ left: -getAccomStep(), behavior: "smooth" });
  });

  accomNext.addEventListener("click", () => {
    accomCarousel.scrollBy({ left: getAccomStep(), behavior: "smooth" });
  });

  accomCarousel.addEventListener("scroll", syncAccomDots, { passive: true });

  if (accomDots) {
    accomDots.querySelectorAll(".dot").forEach((dot) => {
      dot.addEventListener("click", () => {
        const index = Number(dot.getAttribute("data-index"));
        accomCarousel.scrollTo({ left: getAccomStep() * index, behavior: "smooth" });
      });
    });
  }

  syncAccomDots();
}

if (priceGrid && packagePriceHeadline) {
  const priceCards = Array.from(priceGrid.querySelectorAll(".price-card"));

  const setActivePriceCard = (card) => {
    const travelers = card.getAttribute("data-travelers") || "";
    const price = card.getAttribute("data-price") || "";
    priceCards.forEach((item) => {
      const isActive = item === card;
      item.classList.toggle("is-active", isActive);
      item.setAttribute("aria-pressed", isActive ? "true" : "false");
    });

    if (travelers === "6+") {
      packagePriceHeadline.innerHTML = `Starting from <strong>${price} per person</strong>`;
      return;
    }

    packagePriceHeadline.innerHTML = `Price for <strong>${travelers} travelers: ${price} per person</strong>`;
  };

  priceCards.forEach((card) => {
    card.addEventListener("click", () => setActivePriceCard(card));
  });
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeModal();
    closeMobileMenu();
    if (accomLightbox && accomLightbox.classList.contains("is-open")) {
      accomLightbox.classList.remove("is-open");
      accomLightbox.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");
      if (accomLightboxImage) accomLightboxImage.src = "";
    }
  }

  if (accomLightbox && accomLightbox.classList.contains("is-open")) {
    if (event.key === "ArrowRight") {
      if (accomLightboxNext) accomLightboxNext.click();
    }
    if (event.key === "ArrowLeft") {
      if (accomLightboxPrev) accomLightboxPrev.click();
    }
  }
});

const filterSearch = document.getElementById("filterSearch");
const filterDestination = document.getElementById("filterDestination");
const filterStyle = document.getElementById("filterStyle");
const filterDays = document.getElementById("filterDays");
const filterBudget = document.getElementById("filterBudget");
const filterBudgetValue = document.getElementById("filterBudgetValue");
const filterBeach = document.getElementById("filterBeach");
const filterApply = document.getElementById("filterApply");
const filterReset = document.getElementById("filterReset");
const packagesExplorerGrid = document.getElementById("packagesExplorerGrid");
const resultsCount = document.getElementById("resultsCount");
const resultsLabel = document.getElementById("resultsLabel");
const packagesEmpty = document.getElementById("packagesEmpty");

if (
  filterSearch &&
  filterDestination &&
  filterStyle &&
  filterDays &&
  filterBudget &&
  filterBudgetValue &&
  filterBeach &&
  filterApply &&
  filterReset &&
  packagesExplorerGrid &&
  resultsCount &&
  resultsLabel &&
  packagesEmpty
) {
  const explorerCards = Array.from(packagesExplorerGrid.querySelectorAll(".package-result-card"));
  const filterButtons = filterApply.closest(".filter-buttons");

  const forceFilterButtonsLayout = () => {
    if (!filterButtons) return;
    filterButtons.style.display = "grid";
    filterButtons.style.gridTemplateColumns = "1fr 1fr";
    filterButtons.style.alignItems = "center";
    filterButtons.style.gap = "0.75rem";
    filterButtons.style.width = "100%";

    filterApply.style.gridColumn = "1";
    filterApply.style.justifySelf = "start";
    filterApply.style.order = "initial";
    filterApply.style.margin = "0";
    filterApply.style.width = "100%";

    filterReset.style.gridColumn = "2";
    filterReset.style.justifySelf = "end";
    filterReset.style.order = "initial";
    filterReset.style.margin = "0";
    filterReset.style.width = "100%";
  };

  const updateBudgetLabel = () => {
    const budget = Number(filterBudget.value);
    filterBudgetValue.textContent = `$${budget.toLocaleString("en-US")}`;
  };

  const applyPackageFilters = () => {
    const searchValue = filterSearch.value.trim().toLowerCase();
    const destinationValue = filterDestination.value;
    const styleValue = filterStyle.value;
    const daysValue = filterDays.value;
    const budgetValue = Number(filterBudget.value);
    const beachValue = filterBeach.checked;

    let visibleCount = 0;

    explorerCards.forEach((card) => {
      const title = (card.getAttribute("data-title") || "").toLowerCase();
      const destinations = (card.getAttribute("data-destinations") || "").toLowerCase();
      const style = (card.getAttribute("data-style") || "").toLowerCase();
      const days = Number(card.getAttribute("data-days") || "0");
      const rawPrice = card.getAttribute("data-price") || "";
      const price = Number(rawPrice);
      const hasPrice = rawPrice !== "" && !Number.isNaN(price) && price > 0;
      const beach = card.getAttribute("data-beach") === "true";

      const matchesSearch = !searchValue || title.includes(searchValue) || destinations.includes(searchValue);
      const matchesDestination = destinationValue === "all" || destinations.includes(destinationValue);
      const matchesStyle = styleValue === "all" || style === styleValue;
      const matchesDays = daysValue === "all" || days <= Number(daysValue);
      const matchesBudget = !hasPrice || price <= budgetValue;
      const matchesBeach = !beachValue || beach;

      const isVisible = matchesSearch && matchesDestination && matchesStyle && matchesDays && matchesBudget && matchesBeach;
      card.classList.toggle("is-filtered-out", !isVisible);
      card.hidden = !isVisible;
      if (isVisible) visibleCount += 1;
    });

    resultsCount.textContent = String(visibleCount);
    resultsLabel.textContent = visibleCount === 1 ? "package" : "packages";
    packagesEmpty.hidden = visibleCount > 0;
  };

  const resetPackageFilters = () => {
    filterSearch.value = "";
    filterDestination.value = "all";
    filterStyle.value = "all";
    filterDays.value = "all";
    filterBudget.value = "8000";
    filterBeach.checked = false;
    updateBudgetLabel();
    applyPackageFilters();
  };

  filterBudget.addEventListener("input", updateBudgetLabel);

  filterApply.addEventListener("click", applyPackageFilters);
  filterSearch.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
      event.preventDefault();
      applyPackageFilters();
    }
  });

  filterReset.addEventListener("click", resetPackageFilters);

  forceFilterButtonsLayout();
  updateBudgetLabel();
  applyPackageFilters();
}


// ─── Nav live search ─────────────────────────────────────────────────────────
(function () {
  var INDEX = [
    { title: 'Home',         url: '/',             type: 'Page',        keywords: 'home breeze safaris tanzania safari' },
    { title: 'All Packages', url: '/packages.html',type: 'Page',        keywords: 'packages tours safaris tanzania all' },
    { title: 'Contact',      url: '/contact.html', type: 'Page',        keywords: 'contact inquire book reserve' },
    { title: 'About Us',     url: '/about.html',   type: 'Page',        keywords: 'about team breeze safaris story' },

    { title: 'Serengeti National Park',       url: '/destinations/serengeti-national-park.html',       type: 'Destination', keywords: 'serengeti wildlife migration lions leopard cheetah big five savanna grasslands' },
    { title: 'Ngorongoro Conservation Area',  url: '/destinations/ngorongoro-conservation-area.html',  type: 'Destination', keywords: 'ngorongoro crater rhino hippo flamingo wildlife conservation' },
    { title: 'Tarangire National Park',       url: '/destinations/tarangire-national-park.html',       type: 'Destination', keywords: 'tarangire elephants baobab dry season tree climbing' },
    { title: 'Lake Manyara National Park',    url: '/destinations/lake-manyara-national-park.html',    type: 'Destination', keywords: 'lake manyara flamingos tree climbing lions birdwatching' },
    { title: 'Arusha National Park',          url: '/destinations/arusha-national-park.html',          type: 'Destination', keywords: 'arusha mount meru giraffe colobus monkey city park' },
    { title: 'Ndutu Area',                    url: '/destinations/ndutu-area.html',                    type: 'Destination', keywords: 'ndutu calving season wildebeest migration february short rains' },
    { title: 'Zanzibar',                      url: '/destinations/zanzibar.html',                      type: 'Destination', keywords: 'zanzibar beach island spice stone town ocean snorkeling coral' },

    { title: 'Luxury Migration & Big Five Safari (8 Days)', url: '/packages/luxury-migration-big-five-safari-8-day.html',  type: 'Package', keywords: 'luxury 8 day migration big five serengeti ngorongoro tarangire private' },
    { title: 'Ndutu Calving Season Safari',                 url: '/packages/ndutu-calving-season-safari.html',             type: 'Package', keywords: 'ndutu calving baby wildebeest great migration short rains february' },
    { title: 'Tanzania & Zanzibar Honeymoon (9 Days)',      url: '/packages/tanzania-zanzibar-honeymoon-9-day.html',       type: 'Package', keywords: 'honeymoon zanzibar romantic couple 9 day beach safari tanzania' },
  ];

  var wrap = document.querySelector('.nav-search');
  if (!wrap) return;
  var toggle = wrap.querySelector('.nav-search-toggle');
  var input  = wrap.querySelector('.nav-search-input');
  if (!input) return;

  // Build dropdown element
  var dropdown = document.createElement('div');
  dropdown.className = 'nav-search-results';
  wrap.appendChild(dropdown);

  function open() { wrap.classList.add('is-search-open'); input.focus(); }
  function close() {
    wrap.classList.remove('is-search-open');
    dropdown.innerHTML = '';
    dropdown.classList.remove('is-open');
    input.value = '';
  }

  // Clicking the icon button → open input and focus it immediately
  if (toggle) {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      if (wrap.classList.contains('is-search-open')) { close(); } else { open(); }
    });
  }

  function doSearch(q) {
    q = q.toLowerCase().trim();
    if (!q) return [];
    return INDEX.filter(function (item) {
      return item.title.toLowerCase().indexOf(q) !== -1 ||
             item.keywords.toLowerCase().indexOf(q) !== -1;
    });
  }

  function render(q) {
    var trimmed = q.trim();
    if (!trimmed) { dropdown.innerHTML = ''; dropdown.classList.remove('is-open'); return; }
    var results = doSearch(trimmed);
    if (results.length === 0) {
      dropdown.innerHTML = '<p class="nav-search-no-results">No results for \u201c' + trimmed + '\u201d</p>';
    } else {
      dropdown.innerHTML = results.map(function (r) {
        return '<a class="nav-search-result-item" href="' + r.url + '">'
          + '<span>' + r.title + '</span>'
          + '<span class="nav-search-result-type">' + r.type + '</span>'
          + '</a>';
      }).join('');
    }
    dropdown.classList.add('is-open');
  }

  input.addEventListener('input', function () { render(input.value); });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      var q = input.value.trim();
      var results = doSearch(q);
      var dest = results.length > 0
        ? results[0].url
        : (q ? 'https://www.google.com/search?q=site%3Abreezesafaris.com+' + encodeURIComponent(q) : null);
      close();
      if (dest) window.location.href = dest;
    }
    if (e.key === 'Escape') { close(); }
  });

  document.addEventListener('click', function (e) {
    if (!wrap.contains(e.target)) {
      dropdown.classList.remove('is-open');
      if (!input.value) close();
    }
  });
})();
