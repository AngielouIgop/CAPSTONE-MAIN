<!-- ==================== HOME PAGE STYLES ==================== -->
<link rel="stylesheet" href="css/home.css">

<!-- ==================== HERO SLIDESHOW ==================== -->
<div class="header-image-container">
    <div class="slideshow">
        <div class="slide">
            <img src="" alt="Waste Management Image 1" class="header-image" loading="lazy" id="slide1">
        </div>
        <div class="slide">
            <img src="" alt="Waste Management Image 2" class="header-image" loading="lazy" id="slide2">
        </div>
        <div class="slide">
            <img src="" alt="Waste Management Image 3" class="header-image" loading="lazy" id="slide3">
        </div>
    </div>
</div>

<script>
// Function to set images based on screen size
function setResponsiveImages() {
    const slide1 = document.getElementById('slide1');
    const slide2 = document.getElementById('slide2');
    const slide3 = document.getElementById('slide3');
    
    if (window.innerWidth <= 768) {
        // Mobile images
        slide1.src = 'images/ui-elements/mobile1.png';
        slide2.src = 'images/ui-elements/mobile2.png';
        slide3.src = 'images/ui-elements/mobile1.png';
    } else {
        // Desktop images
        slide1.src = 'images/ui-elements/desktop1.png';
        slide2.src = 'images/ui-elements/desktop2.png';
        slide3.src = 'images/ui-elements/desktop1.png';
    }
}

// Set images on page load
document.addEventListener('DOMContentLoaded', setResponsiveImages);

// Update images on window resize
window.addEventListener('resize', setResponsiveImages);
</script>

<!-- ==================== ABOUT US SECTION ==================== -->
<section class="about-us-section" id="about-us-section">
    <h2><img src="images/about/about-text.png" class="about-text"></h2>
    <div class="about-us-blocks-container">
        <!-- Metal Cans Block -->
        <div class="what">
            <p class="what-p">
                <strong>What is BASURA Rewards?</strong>
                <span class="acronym-title"><span class="cap-initial">B</span>uilding <span class="cap-initial">A</span>
                    <span class="cap-initial">S</span>ustainable system with a <span class="cap-initial">U</span>nified
                    recycling <span class="cap-initial">R</span>ewards approach for <span
                        class="cap-initial">A</span>wareness (BASURA) Rewards 
                </span>
                <span class="description-text">is a smart waste management system inspired by the bottles in exchange
                for print credits program of the SK Baluyan Council. It encourages proper recycling by rewarding users
                with points every time they contribute recyclable materials like plastic bottles, glass bottles, or
                metal cans. Once an item is accepted, the system automatically sorts it into the right bin and records
                the contribution. Users can later exchange their collected points for essential goods such as canned
                food, noodles, or milk sachets.

                This initiative promotes sustainability by reducing waste, encouraging recycling habits, and
                strengthening community involvement in environmental care.</span>
            </p>
        </div>

        <div class="about-p2">
            <strong>Mission</strong><br>
            
            Our mission is to help create a cleaner and more sustainable community by promoting
            responsible waste disposal and recycling through the BASURA Rewards system. We aim to inspire residents to
            turn everyday waste into something valuable, encouraging a culture of discipline, environmental care, and
            community cooperation. Through this initiative, we strive to make proper waste management not just a
            responsibility, but a rewarding habit for everyone.</div>

        <div class="about-us-block">
            <div class="block-content">
                <img src="images/about/about-can.png" alt="can" class="block-image">
                <div class="block-text">
                    <ul class="fact-list">
                        <li><strong>Type:</strong> Cans (Metal, usually aluminum)</li>
                        <li><strong>Decay time:</strong> Aluminum cans can take 200–500 years to decompose.</li>
                        <li><strong>Contribution to climate change:</strong> Producing new aluminum requires mining bauxite, which is energy‑intensive and emits greenhouse gases.</li>
                        <li><strong>Pros of recycling:</strong> Saves up to 95% energy versus producing new cans and reduces landfill waste.</li>
                        <li><strong>Dangers:</strong> Sharp edges can cause injuries; leaching metals may contaminate soil if not disposed properly.</li>
                        <li><strong>Impact if mismanaged:</strong> Can pollute land and water, harm animals, and contribute to resource depletion.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Plastic Bottles Block -->
        <div class="about-us-block">
            <div class="block-content">
                <img src="images/about/about-bottle.png" alt="bottle" class="block-image">
                <div class="block-text">
                    
                    <ul class="fact-list">
                        <li><strong>Type:</strong> Plastic Bottles (PET)</li>
                        <li><strong>Decay time:</strong> 450–1,000 years to decompose.</li>
                        <li><strong>Contribution to climate change:</strong> Burning or decomposing plastics releases CO₂ and toxic chemicals, contributing to global warming.</li>
                        <li><strong>Pros of recycling:</strong> Reduces plastic pollution, saves energy and petroleum resources, and keeps waterways and oceans cleaner.</li>
                        <li><strong>Dangers:</strong> Choking hazards for animals, toxic microplastics if broken down, and chemical leaching.</li>
                        <li><strong>Impact if mismanaged:</strong> Can block drains, pollute oceans, harm marine and land animals, and enter the human food chain as microplastics.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Glass Bottles Block -->
        <div class="about-us-block">
            <div class="block-content">
                <img src="images/about/about-glassbottle.png" alt="glassbottle" class="block-image">
                <div class="block-text">
                    <ul class="fact-list">
                        <li><strong>Type:</strong> Glass Bottles</li>
                        <li><strong>Decay time:</strong> Up to 1 million years to decompose in a landfill.</li>
                        <li><strong>Contribution to climate change:</strong> Recycling requires energy; if not recycled, mining new raw materials increases greenhouse gas emissions.</li>
                        <li><strong>Pros of recycling:</strong> Saves raw materials, reduces energy use, lowers greenhouse gas emissions, and prevents littering.</li>
                        <li><strong>Dangers:</strong> Broken glass can cause cuts and injuries.</li>
                        <li><strong>Impact if mismanaged:</strong> Can cause injuries, pollute soil, block drainage, and harm wildlife.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== HOW IT WORKS SECTION ==================== -->
<div id="how-it-works" class="step-by-step">
    <h2> <img src="images/ui-elements/how.png" class="how-text"></h2>

    <!-- Step 1: Login -->
    <div class="step">
        <img src="images/steps/step1.png" class="steps" alt="Login Step" loading="lazy">
        <div class="step-content">
            <div class="step-number">1</div>
            <p class="step-text"><strong>Login</strong> Open the app and log in as a user.</p>
        </div>
    </div>

    <!-- Step 2: Start Contributing -->
    <div class="step reverse">
        <img src="images/steps/step2.png" class="steps" alt="Start Contributing Step" loading="lazy">
        <div class="step-content">
            <div class="step-number">2</div>
            <p class="step-text"><strong>Start</strong> Press the "Start Contributing" button.</p>
        </div>
    </div>

    <!-- Step 3: Wait for Detection -->
    <div class="step">
        <img src="images/steps/step3.png" class="steps" alt="Detection Step" loading="lazy">
        <div class="step-content">
            <div class="step-number">3</div>
            <p class="step-text"><strong>Wait</strong> Allow the system to detect and sort your waste.</p>
        </div>
    </div>

    <!-- Step 4: Complete Process -->
    <div class="step">
        <div class="step-content">
            <div class="step-number">4</div>
            <p class="step-text"><strong>Finish</strong> When done, press the "Done" button until you see the "Thanks
                for Contributing" message.</p>
        </div>
    </div>


</div>

<!-- ==================== CONTACT SECTION ==================== -->
<section class="contact-us-section">
    <hr class="contact-divider">
    <p>Contact us at</p>
    <div class="social-icons">
        <a href="#" class="social-icon"><img src="images/social-media/facebook.png"></a>
        <a href="#" class="social-icon"><img src="images/social-media/instagram.png"></a>
        <a href="#" class="social-icon"><img src="images/social-media/gmail.png"></a>
    </div>
</section>