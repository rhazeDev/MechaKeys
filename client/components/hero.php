<section class="hero-banner">
    <div class="hero-slider">
        <div class="hero-slide active">
            <div class="hero-overlay"></div>
            <div class="hero-image-bg" style="background-image: url('./images/banners/banner1.png');"></div>
            <div class="hero-content">
                <span class="tag">Featured Product</span>
                <h1>Premium Mechanical Keyboards for Professionals</h1>
                <p>Experience the ultimate typing experience with our high-performance mechanical keyboards. Featuring custom switches, premium builds, and stunning RGB lighting.</p>
                <div class="hero-buttons">
                    <button class="btn-primary">
                        <i class="fas fa-shopping-bag"></i>
                        Shop Now
                    </button>
                </div>
            </div>
        </div>

        <div class="hero-slide">
            <div class="hero-overlay"></div>
            <div class="hero-image-bg" style="background-image: url('./images/banners/banner2.png');"></div>
            <div class="hero-content">
                <span class="tag">New Arrival</span>
                <h1>Custom Mechanical Switches</h1>
                <p>Discover our latest collection of custom switches. Choose from tactile, linear, or clicky switches to match your typing style perfectly.</p>
                <div class="hero-buttons">
                    <button class="btn-primary">
                        <i class="fas fa-shopping-bag"></i>
                        Explore Now
                    </button>
                </div>
            </div>
        </div>

        <div class="hero-slide">
            <div class="hero-overlay"></div>
            <div class="hero-image-bg" style="background-image: url('./images/banners/banner3.png');"></div>
            <div class="hero-content">
                <span class="tag">Limited Edition</span>
                <h1>RGB Backlit Keyboards</h1>
                <p>Illuminate your setup with our stunning RGB backlit keyboards. Fully customizable lighting effects and per-key RGB control.</p>
                <div class="hero-buttons">
                    <button class="btn-primary">
                        <i class="fas fa-shopping-bag"></i>
                        Buy Now
                    </button>
                </div>
            </div>
        </div>

        <button class="slider-nav prev" onclick="moveSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slider-nav next" onclick="moveSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>

        <div class="slider-dots">
            <span class="dot active" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
        </div>
    </div>
</section>

<script>
let slideIndex = 1;
let slideTimer;

function showSlide(n) {
    const slides = document.getElementsByClassName("hero-slide");
    const dots = document.getElementsByClassName("dot");
    
    if (n > slides.length) { slideIndex = 1 }
    if (n < 1) { slideIndex = slides.length }
    
    for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
    }
    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    
    slides[slideIndex - 1].classList.add("active");
    dots[slideIndex - 1].classList.add("active");
}

function moveSlide(n) {
    clearTimeout(slideTimer);
    showSlide(slideIndex += n);
    autoSlide();
}

function currentSlide(n) {
    clearTimeout(slideTimer);
    showSlide(slideIndex = n);
    autoSlide();
}

function autoSlide() {
    slideTimer = setTimeout(() => {
        slideIndex++;
        showSlide(slideIndex);
        autoSlide();
    }, 5000);
}

document.addEventListener('DOMContentLoaded', () => {
    showSlide(slideIndex);
    autoSlide();
});
</script>
