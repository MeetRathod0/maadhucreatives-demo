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

//CLIENT CAROUSEL
const clientCarousel = document.getElementById("clientCarousel");
const clientCards = clientCarousel.children;
const clientCarousel1 = document.getElementById("clientCarousel1");
const clientCards1 = clientCarousel1.children;
const cityCarousel2 = document.getElementById("cityCarousel2");
const cityCards2 = cityCarousel2.children;
let clientIndex = 0;
let cityIndex = 0;

function getCardsPerView(carouselId) {
    if (carouselId === "cityCarousel2") {
        if (window.innerWidth < 768) return 1; // mobile
        if (window.innerWidth < 1024) return 2; // tablet
        return 4; // desktop
    }
    return window.innerWidth < 768 ? 2 : 4;
}

function getCardWidth(carousel) {
    return carousel.children[0].offsetWidth + 24; // card width + gap (1.5rem = 24px)
}

function scrollClient() {
    const cardsPerView = getCardsPerView("clientCarousel");
    const maxIndex = Math.max(0, clientCards.length - cardsPerView);
    clientIndex = (clientIndex + 1) % (maxIndex + 1);
    clientCarousel.style.transform = `translateX(-${clientIndex * getCardWidth(clientCarousel)
        }px)`;
    clientCarousel1.style.transform = `translateX(-${clientIndex * getCardWidth(clientCarousel1)
        }px)`;
}

function scrollCity() {
    const cardsPerView = getCardsPerView("cityCarousel2");
    const maxIndex = Math.max(0, cityCards2.length - cardsPerView);
    cityIndex = (cityIndex + 1) % (maxIndex + 1);
    cityCarousel2.style.transform = `translateX(-${cityIndex * getCardWidth(cityCarousel2)
        }px)`;
}

window.addEventListener("resize", () => {
    clientIndex = 0;
    cityIndex = 0;
    clientCarousel.style.transform = `translateX(0)`;
    clientCarousel1.style.transform = `translateX(0)`;
    cityCarousel2.style.transform = `translateX(0)`;
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
