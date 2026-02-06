const carousel = document.getElementById("carousel");
const slides = carousel.children;
const totalSlides = slides.length;
const textBox = document.getElementById("carouselText");

const titles = [
    "Architectural Model Maker",
    "Engineering Model Makers",
    "Industrial Model Makers",
    "3D Visualization",
    "3D Printing",
];

let currentIndex = 0;

function slideWidth() {
    return carousel.parentElement.clientWidth;
}

function updateCarousel() {
    carousel.style.transform = `translateX(-${currentIndex * slideWidth()
        }px)`;
    textBox.textContent = titles[currentIndex];
}

function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides;
    updateCarousel();
}

function prevSlide() {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    updateCarousel();
}

window.addEventListener("resize", updateCarousel);

// Optional auto-slide
setInterval(nextSlide, 4000);

// Service Tabs Filtering =================================
const tabs = document.querySelectorAll(".tab-btn");
const cards = document.querySelectorAll(".service-card");

function filterCards(filter) {
    cards.forEach((card) => {
        const category = card.dataset.category;
        card.classList.toggle("hidden", category !== filter);
    });
}

tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        // set active tab
        tabs.forEach((t) => t.classList.remove("active"));
        tab.classList.add("active");

        // filter cards
        filterCards(tab.dataset.filter);
    });
});

// ✅ initial load filter
const activeTab = document.querySelector(".tab-btn.active");
if (activeTab) {
    filterCards(activeTab.dataset.filter);
}

// CLIENT CAROUSELS
const clientCarousels = document.querySelectorAll('.client-carousel');
const clientIndices = Array.from(clientCarousels).map(() => 0);
const cityCarousel2 = document.getElementById('cityCarousel2');
const cityCards2 = cityCarousel2 ? cityCarousel2.children : [];
let cityIndex = 0;

function getCardsPerView(carousel) {
    if (carousel && carousel.id === 'cityCarousel2') {
        if (window.innerWidth < 768) return 1; // mobile
        if (window.innerWidth < 1024) return 2; // tablet
        return 4; // desktop
    }
    return window.innerWidth < 768 ? 2 : 4;
}

function getCardWidth(carousel) {
    const first = carousel && carousel.children[0];
    return first ? first.offsetWidth + 24 : 0; // card width + gap (1.5rem = 24px)
}

function scrollClient() {
    clientCarousels.forEach((carousel, idx) => {
        const cardsPerView = getCardsPerView(carousel);
        const maxIndex = Math.max(0, carousel.children.length - cardsPerView);
        clientIndices[idx] = (clientIndices[idx] + 1) % (maxIndex + 1);
        carousel.style.transform = `translateX(-${clientIndices[idx] * getCardWidth(carousel)}px)`;
    });
}

function scrollCity() {
    if (!cityCarousel2) return;
    const cardsPerView = getCardsPerView(cityCarousel2);
    const maxIndex = Math.max(0, cityCarousel2.children.length - cardsPerView);
    cityIndex = (cityIndex + 1) % (maxIndex + 1);
    cityCarousel2.style.transform = `translateX(-${cityIndex * getCardWidth(cityCarousel2)}px)`;
}

window.addEventListener('resize', () => {
    clientIndices.forEach((_, idx) => (clientIndices[idx] = 0));
    clientCarousels.forEach((carousel) => (carousel.style.transform = `translateX(0)`));
    if (cityCarousel2) cityCarousel2.style.transform = `translateX(0)`;
});

setInterval(scrollClient, 3000);
setInterval(scrollCity, 3000);

// FAQ Toggle Function
function toggleFAQ(button) {
    const content = button.nextElementSibling;
    content.classList.toggle("hidden");
}


// Arrow animation
const processFlow = document.getElementById("process-flow");
const arrows = document.querySelectorAll(".md-arrow");

// Set initial state for all arrows
arrows.forEach((arrow) => {
    const line = arrow.querySelector(".line");
    const head = arrow.querySelector(".head");
    const lineLen = line.getTotalLength();
    const headLen = head.getTotalLength();

    line.style.strokeDasharray = lineLen;
    line.style.strokeDashoffset = lineLen;
    head.style.strokeDasharray = headLen;
    head.style.strokeDashoffset = headLen;
});

const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.2
};

const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // Start staggered animation
            arrows.forEach((arrow, index) => {
                const line = arrow.querySelector(".line");
                const head = arrow.querySelector(".head");

                setTimeout(() => {
                    line.style.transition = "stroke-dashoffset 0.9s ease-out";
                    head.style.transition = "stroke-dashoffset 0.3s ease-out 0.7s";

                    line.style.strokeDashoffset = "0";
                    head.style.strokeDashoffset = "0";
                }, index * 500);
            });

            // Stop observing the container
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

if (processFlow) {
    observer.observe(processFlow);
}

// SERVICE CAROUSELS AUTO-SLIDE
function setupServiceCarousel(carouselId, interval = 3000) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;

    const slides = carousel.children;
    let index = 0;

    function scroll() {
        index = (index + 1) % slides.length;
        carousel.style.transform = `translateX(-${index * 100}%)`;
    }

    setInterval(scroll, interval);
}

setupServiceCarousel("serviceCarousel", 3000);
setupServiceCarousel("visualizationCarousel", 3500); // Staggered timing
setupServiceCarousel("PrintingCarousel", 4000);      // Staggered timing
