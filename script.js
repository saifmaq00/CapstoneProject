/* ---------------------------------------
   Combined JavaScript: Existing + New Scripts
   --------------------------------------- */

// Animation for Elements Appearing
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        document.body.classList.add('animate');
    }, 1000); 
});

// Overlay Menu Functionality
const menuButton = document.getElementById('menu-btn');
const overlayMenu = document.getElementById('overlay-menu');
const closeButton = document.getElementById('menu-close');

menuButton.addEventListener('click', () => {
    overlayMenu.style.visibility = 'visible'; 
    overlayMenu.classList.add('open'); 
});

closeButton.addEventListener('click', () => {
    overlayMenu.classList.remove('open'); 

    overlayMenu.addEventListener('transitionend', function hideMenu() {
        if (!overlayMenu.classList.contains('open')) {
            overlayMenu.style.visibility = 'hidden';
        }
        overlayMenu.removeEventListener('transitionend', hideMenu);
    });
});

// JavaScript for Generating Filter Boxes and Handling Filtering
document.addEventListener("DOMContentLoaded", () => {
    const allCategories = ["music", "sports", "theater", "conference", "exhibition", "festival", "workshop", "seminar"]; // All categories
    const cards = document.querySelectorAll(".card");
    const filterContainer = document.getElementById("filter-container");
    const categoryCounts = {};

    // Initialize all categories with zero count
    allCategories.forEach(category => {
        categoryCounts[category] = 0;
    });

    // Count categories based on existing cards
    cards.forEach(card => {
        const category = card.getAttribute("data-category");
        if (categoryCounts[category] !== undefined) {
            categoryCounts[category]++;
        }
    });

    // Create "All" filter box
    const allFilterBox = document.createElement("div");
    allFilterBox.classList.add("filter-box");
    allFilterBox.innerHTML = `<span>All</span>`;
    allFilterBox.addEventListener("click", () => showAllCards());
    filterContainer.appendChild(allFilterBox);

    // Create filter boxes for each category
    allCategories.forEach(category => {
        const filterBox = document.createElement("div");
        filterBox.classList.add("filter-box");
        filterBox.setAttribute("data-category", category);
        filterBox.innerHTML = `
            <span>${category.charAt(0).toUpperCase() + category.slice(1)}</span>
            <div class="filter-count">${categoryCounts[category]}</div>
        `;
        
        // Make filter box inactive if count is zero
        if (categoryCounts[category] === 0) {
            filterBox.classList.add("inactive");
        } else {
            filterBox.addEventListener("click", () => filterCards(category));
        }
        
        filterContainer.appendChild(filterBox);
    });

    // Filter function to show specific category without animation
    function filterCards(category) {
        cards.forEach(card => {
            card.style.display = card.getAttribute("data-category") === category ? "flex" : "none";
        });
    }

    // Show all cards function
    function showAllCards() {
        cards.forEach(card => {
            card.style.display = "flex";
        });
    }
});

// Dropdown Toggle Function
function toggleDropdown() {
    const dropdown = document.getElementById('dropdownMenu');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Update Price Function
function updatePrice(price) {
    document.getElementById('ticketPrice').innerHTML = `${price}.00 SAR`;
    toggleDropdown();  // Close the dropdown after selection
}

// Sign In and Sign Up Panel Toggle
const signInButton = document.getElementById('signIn');
const signUpButton = document.getElementById('signUp');
const container = document.getElementById('container');

if (signInButton && signUpButton && container) {
    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    });

    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });
}

// Function to Add More Amenities Fields with Quantity and Remove Button
function addAmenityField() {
    const container = document.getElementById('amenities-container');
    const newAmenityDiv = document.createElement('div');
    newAmenityDiv.classList.add('amenity-group');

    // Create a select dropdown for amenity
    const newSelect = document.createElement('select');
    newSelect.name = 'amenities[]';
    newSelect.required = true;
    newSelect.onchange = function() {
        toggleOtherInput(this);
    };

    // Add default disabled option
    const defaultOption = document.createElement('option');
    defaultOption.value = "";
    defaultOption.disabled = true;
    defaultOption.selected = true;
    defaultOption.textContent = "Select Amenity";
    newSelect.appendChild(defaultOption);

    // Add options to the select element
    const amenitiesOptions = ["Parking", "Chairs", "Tables", "Other"];
    amenitiesOptions.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option;
        opt.textContent = option;
        newSelect.appendChild(opt);
    });

    // Create input for quantity
    const quantityInput = document.createElement('input');
    quantityInput.type = 'number';
    quantityInput.name = 'amenity_quantity[]';
    quantityInput.min = 1;
    quantityInput.placeholder = 'Quantity';
    quantityInput.required = true;

    // Create input for "Other" specification (hidden by default)
    const otherInput = document.createElement('input');
    otherInput.type = 'text';
    otherInput.name = 'amenity_other[]';
    otherInput.placeholder = 'Please specify';
    otherInput.style.display = 'none'; // Hidden initially

    // Create remove button
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.textContent = 'Remove';
    removeButton.classList.add('remove-amenity');
    removeButton.onclick = () => container.removeChild(newAmenityDiv);

    // Append elements to the amenity div
    newAmenityDiv.appendChild(newSelect);
    newAmenityDiv.appendChild(quantityInput);
    newAmenityDiv.appendChild(otherInput);
    newAmenityDiv.appendChild(removeButton);
    container.appendChild(newAmenityDiv);
}

// Function to Toggle "Other" Input Field Based on Selection
function toggleOtherInput(selectElement) {
    const amenityGroup = selectElement.parentElement;
    const otherInput = amenityGroup.querySelector('input[name="amenity_other[]"]');

    if (selectElement.value === 'Other') {
        otherInput.style.display = 'block';
        otherInput.required = true;
    } else {
        otherInput.style.display = 'none';
        otherInput.value = '';
        otherInput.required = false;
    }
}

// Function to Handle Image Previews and Manage Removal
let facilityImages = [];
let bannerImage = null;

function previewImages(event, previewContainer, type) {
    const files = event.target.files;
    const preview = document.getElementById(previewContainer);

    if (type === 'facility') {
        // Handle Facility Images (multiple)
        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) {
                alert(`File "${file.name}" is not a supported format.`);
                return;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                alert(`File "${file.name}" exceeds the maximum size of 5MB.`);
                return;
            }

            // Check for duplicates
            const isDuplicate = facilityImages.some(existingFile => existingFile.name === file.name && existingFile.size === file.size);
            if (isDuplicate) {
                alert(`File "${file.name}" is already selected.`);
                return;
            }

            // Add file to facilityImages array
            facilityImages.push(file);
        });
        updateFacilityImagesPreview();
    } else if (type === 'banner') {
        // Handle Banner Image (single)
        const file = files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                alert(`File "${file.name}" is not a supported format.`);
                return;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                alert(`File "${file.name}" exceeds the maximum size of 5MB.`);
                return;
            }

            // Assign the new banner image
            bannerImage = file;
            updateBannerImagePreview();
        }
    }

    // Reset the file input to allow re-uploading the same file if needed
    event.target.value = '';
}

// Function to Update Facility Images Preview
function updateFacilityImagesPreview() {
    const preview = document.getElementById('facility-images-preview');
    preview.innerHTML = '';

    facilityImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const imgContainer = document.createElement('div');
            imgContainer.classList.add('preview-image');

            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = file.name;
            img.title = file.name;

            const removeBtn = document.createElement('button');
            removeBtn.classList.add('remove-image');
            removeBtn.innerHTML = '&times;'; // Multiplication sign (X)
            removeBtn.onclick = () => removeFacilityImage(index);

            imgContainer.appendChild(img);
            imgContainer.appendChild(removeBtn);
            preview.appendChild(imgContainer);
        };
        reader.readAsDataURL(file);
    });
}

// Function to Remove a Facility Image
function removeFacilityImage(index) {
    facilityImages.splice(index, 1);
    updateFacilityImagesPreview();
}

// Function to Update Banner Image Preview
function updateBannerImagePreview() {
    const preview = document.getElementById('banner-image-preview');
    preview.innerHTML = '';

    if (bannerImage) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const imgContainer = document.createElement('div');
            imgContainer.classList.add('single-image-preview');

            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = bannerImage.name;
            img.title = bannerImage.name;

            const removeBtn = document.createElement('button');
            removeBtn.classList.add('remove-image');
            removeBtn.innerHTML = '&times;'; // Multiplication sign (X)
            removeBtn.onclick = () => removeBannerImage();

            imgContainer.appendChild(img);
            imgContainer.appendChild(removeBtn);
            preview.appendChild(imgContainer);
        };
        reader.readAsDataURL(bannerImage);
    }
}

// Function to Remove Banner Image
function removeBannerImage() {
    bannerImage = null;
    const preview = document.getElementById('banner-image-preview');
    preview.innerHTML = '';
}

// Function to Handle Form Submission
function handleFormSubmission(event) {
    event.preventDefault(); // Prevent default form submission

    const form = event.target;

    // Create a new FormData object
    const formData = new FormData(form);

    // Append Facility Images from the facilityImages array
    facilityImages.forEach((file, index) => {
        formData.append('facility_images[]', file);
    });

    // Append Banner Image if exists
    if (bannerImage) {
        formData.append('banner_img', bannerImage);
    }

    // Perform form validation here if needed

    // Example: Log form data entries (for testing purposes)
    for (let pair of formData.entries()) {
        console.log(pair[0]+ ': ' + pair[1]);
    }

    // TODO: Submit formData to the server using fetch or XMLHttpRequest
    /*
    fetch('your_backend_endpoint_here', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Handle success
        alert('Facility added successfully!');
        form.reset();
        facilityImages = [];
        bannerImage = null;
        updateFacilityImagesPreview();
        updateBannerImagePreview();
    })
    .catch(error => {
        // Handle error
        console.error('Error:', error);
        alert('There was an error adding the facility.');
    });
    */
}

// Event Listener for Form Submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', handleFormSubmission);
    }
});

    // Page Transition Effect
    window.addEventListener('load', () => {
        const transitionElement = document.getElementById('page-transition');
        setTimeout(() => {
            transitionElement.classList.add('hidden'); // Add hidden class to fade out
        }, 500); // Short delay for visibility (500ms)
    });

    
        const carouselContainer = document.getElementById('carousel-container');
        const carouselTrack = document.getElementById('carousel-track');

        let isDown = false;
        let startX;
        let scrollLeft;

        // Mouse Events for dragging
        carouselContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            carouselContainer.classList.add('active');
            startX = e.pageX - carouselContainer.offsetLeft;
            scrollLeft = carouselContainer.scrollLeft;
        });

        carouselContainer.addEventListener('mouseleave', () => {
            isDown = false;
            carouselContainer.classList.remove('active');
        });

        carouselContainer.addEventListener('mouseup', () => {
            isDown = false;
            carouselContainer.classList.remove('active');
        });

        carouselContainer.addEventListener('mousemove', (e) => {
            if(!isDown) return;
            e.preventDefault();
            const x = e.pageX - carouselContainer.offsetLeft;
            const walk = (x - startX) * 2; // Scroll-fast
            carouselContainer.scrollLeft = scrollLeft - walk;
        });

        // Touch Events for mobile devices
        let touchStartX = 0;
        let touchScrollLeft = 0;

        carouselContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].pageX - carouselContainer.offsetLeft;
            touchScrollLeft = carouselContainer.scrollLeft;
        });

        carouselContainer.addEventListener('touchmove', (e) => {
            const x = e.touches[0].pageX - carouselContainer.offsetLeft;
            const walk = (x - touchStartX) * 2; // Scroll-fast
            carouselContainer.scrollLeft = touchScrollLeft - walk;
        });

        // Pause auto-scroll on hover or active dragging
        carouselContainer.addEventListener('mouseenter', () => {
            carouselTrack.style.animationPlayState = 'paused';
        });

        carouselContainer.addEventListener('mouseleave', () => {
            carouselTrack.style.animationPlayState = 'running';
        });


        