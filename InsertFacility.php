<?php
session_start(); // Start the session

$host = "localhost";
$username = "root";
$password = "";
$database = "test2";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_facility'])) {
    $facilityName = $_POST['facility_name'];
    $country = $_POST['country'];
    $region = $_POST['region'];
    $city = $_POST['city'];
    $gmLocationLink = $_POST['gm_location_link'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $capacity = $_POST['capacity'];

    // Prepare Amenities JSON
    $amenities = [];
    if (isset($_POST['amenity_type']) && isset($_POST['amenity_quantity'])) {
        foreach ($_POST['amenity_type'] as $index => $type) {
            $quantity = $_POST['amenity_quantity'][$index];
            $other = isset($_POST['amenity_other'][$index]) ? $_POST['amenity_other'][$index] : null;
            $amenities[] = [
                'type' => $type,
                'quantity' => $quantity,
                'other' => $type === 'Other' ? $other : null
            ];
        }
    }
    $amenitiesJson = json_encode($amenities);

    // Handle file uploads
    $facilityImages = [];
    if (!empty($_FILES['facility_images']['name'][0])) {
        foreach ($_FILES['facility_images']['tmp_name'] as $index => $tmpName) {
            $fileName = "uploads/" . uniqid() . "_" . basename($_FILES['facility_images']['name'][$index]);
            if (move_uploaded_file($tmpName, $fileName)) {
                $facilityImages[] = $fileName;
            }
        }
    }
    $facilityImages = json_encode($facilityImages);

    $bannerImage = '';
    if (!empty($_FILES['banner_img']['tmp_name'])) {
        $bannerImage = "uploads/" . uniqid() . "_" . basename($_FILES['banner_img']['name']);
        if (!move_uploaded_file($_FILES['banner_img']['tmp_name'], $bannerImage)) {
            $errorMessage = "Failed to upload banner image.";
        }
    }

    // Retrieve OwnerID from session
    if (isset($_SESSION['user_id'])) {
        $ownerID = $_SESSION['user_id'];
    } else {
        $errorMessage = "OwnerID is not set in the session.";
    }

    $status = 'Pending';

    // Insert into the database if OwnerID is set and no upload errors
    if (!empty($ownerID) && empty($errorMessage)) {
        $stmt = $conn->prepare("
        INSERT INTO facility 
        (FacilityName, Country, Region, City, GMLocationLink, Price, Description, Capacity, Amenities, FacilityImages, BannerImage, OwnerID, Status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssdsssssds", 
            $facilityName, 
            $country, 
            $region, 
            $city, 
            $gmLocationLink, 
            $price, 
            $description, 
            $capacity, 
            $amenitiesJson, 
            $facilityImages, 
            $bannerImage, 
            $ownerID, 
            $status
        );

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errorMessage = "Insert failed: " . $stmt->error;
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@200;400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Add Facility</title>
    
    <style>
        /* Reset some default styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212; /* Dark background */
            color: #ffffff; /* Default text color set to white */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background-color: #ffffff; /* White background for the form */
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #000000; /* Black text for the heading */
        }

        form label {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            position: relative;
        }

        form label span {
            margin-bottom: 8px;
            font-weight: 600;
            color: #000000; /* Black text for labels */
        }

        input[type="text"],
        input[type="number"],
        input[type="url"],
        textarea,
        select {
            padding: 12px 15px;
            border: 1px solid #cccccc; /* Light gray border */
            border-radius: 4px;
            font-size: 16px;
            background-color: #ffffff; /* White background */
            color: #000000; /* Black text */
            transition: border-color 0.3s, background-color 0.3s, color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="url"]:focus,
        textarea:focus,
        select:focus {
            border-color: #dbfe72bd;
            outline: none;
            background-color: #f0f8ff; /* Light blue background on focus */
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .amenity-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .amenity-group select,
        .amenity-group input[type="number"],
        .amenity-group input[type="text"] {
            margin-right: 10px;
            flex: 1 1 200px;
            margin-bottom: 10px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #cccccc;
        }

        .amenity-group button {
            background-color: transparent; /* Transparent background */
            color: #000000; /* Black text */
            border: 1px solid #000000; /* Black border */
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            flex: 0 0 auto;
        }

        .amenity-group button:hover {
            background-color: #000000; /* Black background on hover */
            color: #ffffff; /* White text on hover */
        }

        #add-amenity-btn {
            background-color: transparent; /* Transparent background */
            color: #000000; /* Black text */
            border: 2px solid #dbfe72bd; /* Green border */
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            margin-bottom: 20px;
            display: block;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
        }

        #add-amenity-btn:hover {
            background-color: #dbfe72bd; /* Green background on hover */
            color: #ffffff; /* White text on hover */
        }

        .file-input {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .file-input input[type="file"] {
            padding: 5px 0;
            background-color: transparent; /* Transparent background */
            color: #000000; /* Black text */
            border: 1px solid #cccccc; /* Light gray border */
            border-radius: 4px;
            cursor: pointer;
        }

        .file-input input[type="file"]::file-selector-button {
            background-color: #dbfe72bd; /* Blue background for the file selector button */
            color: #ffffff; /* White text */
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .file-input input[type="file"]::file-selector-button:hover {
            background-color: #dbfe72bd; /* Darker blue on hover */
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background-color: transparent; /* Transparent background */
            color: #000000; /* Black text */
            border: 2px solid #dbfe72bd; /* Blue border */
            font-size: 18px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            font-weight: 600;
        }

        .submit-btn:hover {
            background-color: #dbfe72bd; /* Blue background on hover */
            color: #ffffff; /* White text on hover */
        }

        /* Customized Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.8); /* Dark overlay */
        }

        .modal-content {
            background-color: #ffffff; /* White background for modal */
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #444;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 500px;
            border-radius: 5px;
            position: relative;
            text-align: center; /* Center the content */
            color: #000000; /* Black text */
            animation-name: animatetop;
            animation-duration: 0.4s
        }

        /* Style for the GOT IT! button */
        .modal-button {
            margin-top: 20px;
            border: 2px solid #000000; /* Black border */
            background-color: transparent; /* Transparent background */
            color: #000000; /* Black text */
            font-size: 20px;
            font-weight: bold;
            padding: 15px 50px;
            letter-spacing: 1px; /* Initial letter spacing */
            text-transform: uppercase;
            cursor: pointer;
            transform: skew(-10deg) rotate(-5deg);
            border-radius: 4px; /* Rounded corners */
            transition: background-color 0.3s, color 0.3s, letter-spacing 0.3s ease, font-weight 0.3s ease; /* Smooth transition */
        }

        .modal-button:hover {
            background-color: #000000; /* Black background on hover */
            color: #ffffff; /* White text on hover */
            letter-spacing: 3px; /* Increase letter spacing on hover */
            font-weight: bolder; /* Make text bolder on hover */
        }

        .modal-message {
            margin-top: 20px;
            font-size: 16px;
        }

        /* Optional: Add animation to modal */
        @keyframes animatetop {
            from {top: -300px; opacity: 0}
            to {top: 0; opacity: 1}
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 20px 25px;
            }

            form label {
                margin-bottom: 15px;
            }

            #add-amenity-btn {
                width: 100%;
            }

            .amenity-group {
                flex-direction: column;
                align-items: stretch;
            }

            .amenity-group select,
            .amenity-group input[type="number"],
            .amenity-group input[type="text"] {
                margin-right: 0;
                margin-bottom: 10px;
                flex: 1 1 100%;
            }

            .amenity-group button {
                align-self: flex-end;
            }

            .modal-content {
                margin: 30% auto; /* Adjust margin for smaller screens */
            }
        }
        footer {
            background-color: black;
            color: white;
            font-size: 20px;
            font-family: 'Anton', sans-serif;
            padding: 2rem 1rem;
            text-align: center;
            margin-top: auto;
            width: 100%;
        }

        footer .footer-links {
            margin-bottom: 1rem;
        }

        footer .footer-links a {
            color: white;
            text-decoration: none;
            margin: 0 1rem;
            transition: letter-spacing 0.3s, color 0.3s;
        }

        footer .footer-links a:hover {
            letter-spacing: 3px;
            color: #dbfe72bd;
        }

        footer .footer-links a:focus {
            outline: none;
        }

        footer p {
            margin-top: 1rem;
        }

        header {
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000; 
            padding: 20px 40px;
            background-color:#0000000;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        header.hidden {
            transform: translateY(-100%);
        }

        .logo h1 {
            color: white;
            letter-spacing: 2px;
            transition: letter-spacing 0.3s ease;
            text-align: left;
            font-family: 'Anton', sans-serif;
            font-size: 3rem;
            line-height: 1;
            margin: 0;
        }

        .logo h1:hover {
            color: #dbfe72bd;
            letter-spacing: 4px;
        }

        nav .menu {
            color: white; 
            text-decoration: none;
            padding: 10px;
            font-size: 20px; 
            font-family: 'Anton', sans-serif;
            transition: letter-spacing 0.3s ease;
        }

        nav .menu:hover {
            color: #dbfe72bd;
            letter-spacing: 4px;
        }
        
        .zig-zag-line {
              --a: 90deg;
              --s: 20px; 
              --b: 7px; 
              
              background: grey;
              height: calc(var(--b) + var(--s)/(2*tan(var(--a)/2)));
              width: 100%;
              --_g: var(--s) repeat-x conic-gradient(
                  from calc(var(--a)/-2) at bottom,
                  #0000,
                  #000 1deg calc(var(--a) - 1deg),
                  #0000 var(--a)
              );
              mask: 50% calc(-1*var(--b))/var(--_g) exclude, 50% / var(--_g);
 
              margin-top: 40px; 
          }
    </style>
    <script>
        const data = {
            "Bahrain": {
                "Capital": ["Manama"],
                "Muharraq": ["Muharraq"],
                "Northern Governorate": ["Riffa", "Isa Town"],
                "Southern Governorate": ["Sitra", "Budaiya"]
            },
            "Kuwait": {
                "Al Asimah": ["Kuwait City"],
                "Hawalli": ["Hawalli"],
                "Farwaniya": ["Farwaniya"],
                "Al Ahmadi": ["Al Ahmadi"],
                "Al Jahra": ["Al Jahra"]
            },
            "Oman": {
                "Muscat": ["Muscat"],
                "Dhofar": ["Salalah"],
                "Al Batinah North": ["Sohar"],
                "Al Batinah South": ["Rustaq"],
                "Ad Dakhiliyah": ["Nizwa"],
                "Al Wusta": ["Samail"],
                "Al Dhahirah": ["Ibri"],
                "Zufar": ["Zufar"]
            },
            "Qatar": {
                "Doha": ["Doha"],
                "Al Rayyan": ["Al Rayyan"],
                "Umm Salal": ["Umm Salal"],
                "Al Wakrah": ["Al Wakrah"],
                "Al Daayen": ["Al Daayen"],
                "Al Shamal": ["Ar Rayyan"],
                "Madinat ash Shamal": ["Madinat ash Shamal"]
            },
            "Saudi Arabia": {
                "Riyadh": ["Riyadh"],
                "Makkah": ["Jeddah", "Mecca"],
                "Eastern Province": ["Dammam", "Khobar"],
                "Madinah": ["Medina"],
                "Asir": ["Abha"],
                "Tabuk": ["Tabuk"],
                "Hail": ["Hail"],
                "Najran": ["Najran"]
            },
            "United Arab Emirates": {
                "Abu Dhabi": ["Abu Dhabi"],
                "Dubai": ["Dubai"],
                "Sharjah": ["Sharjah"],
                "Ajman": ["Ajman"],
                "Umm Al Quwain": ["Umm Al Quwain"],
                "Ras Al Khaimah": ["Ras Al Khaimah"],
                "Fujairah": ["Fujairah"]
            }
        };

        // Function to populate regions based on selected country
        function populateRegions() {
            const countrySelect = document.getElementById('country');
            const regionSelect = document.getElementById('region');
            const citySelect = document.getElementById('city');

            // Clear previous options
            regionSelect.innerHTML = '<option value="">Select Region</option>';
            citySelect.innerHTML = '<option value="">Select City</option>';

            const selectedCountry = countrySelect.value;

            if (selectedCountry && data[selectedCountry]) {
                const regions = Object.keys(data[selectedCountry]);
                regions.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region;
                    option.text = region;
                    regionSelect.add(option);
                });
            }
        }

        // Function to populate cities based on selected region
        function populateCities() {
            const countrySelect = document.getElementById('country');
            const regionSelect = document.getElementById('region');
            const citySelect = document.getElementById('city');

            // Clear previous options
            citySelect.innerHTML = '<option value="">Select City</option>';

            const selectedCountry = countrySelect.value;
            const selectedRegion = regionSelect.value;

            if (selectedCountry && selectedRegion && data[selectedCountry][selectedRegion]) {
                const cities = data[selectedCountry][selectedRegion];
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.text = city;
                    citySelect.add(option);
                });
            }
        }

        // Function to add a new amenity group
        function addAmenity() {
            const container = document.getElementById('amenities-container');
            const amenityGroup = document.createElement('div');
            amenityGroup.className = 'amenity-group';

            const select = document.createElement('select');
            select.name = 'amenity_type[]';
            select.required = true;
            select.innerHTML = `
                <option value="">Select Amenity</option>
                <option value="Parking">Parking</option>
                <option value="Chairs">Chairs</option>
                <option value="Tables">Tables</option>
                <option value="Other">Other</option>
            `;
            select.onchange = function () {
                const otherInput = this.parentElement.querySelector('.amenity-other');
                if (this.value === 'Other') {
                    otherInput.style.display = 'inline-block';
                } else {
                    otherInput.style.display = 'none';
                }
            };

            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.name = 'amenity_quantity[]';
            quantityInput.min = '1';
            quantityInput.placeholder = 'Quantity';
            quantityInput.required = true;

            const otherInput = document.createElement('input');
            otherInput.type = 'text';
            otherInput.name = 'amenity_other[]';
            otherInput.placeholder = 'Please specify';
            otherInput.className = 'amenity-other';
            otherInput.style.display = 'none';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerText = 'Remove';
            removeBtn.onclick = function () {
                container.removeChild(amenityGroup);
            };

            amenityGroup.appendChild(select);
            amenityGroup.appendChild(quantityInput);
            amenityGroup.appendChild(otherInput);
            amenityGroup.appendChild(removeBtn);

            container.appendChild(amenityGroup);
        }

        // Function to toggle the display of the 'Other' input field
        function toggleOtherInput(selectElement) {
            const otherInput = selectElement.parentElement.querySelector('.amenity-other');
            if (selectElement.value === 'Other') {
                otherInput.style.display = 'inline-block';
            } else {
                otherInput.style.display = 'none';
            }
        }

        // Function to show the modal with a specific message
        function showModal(title, message) {
            document.getElementById('modal-title').innerText = title;
            document.getElementById('modal-message').innerText = message;
            document.getElementById('modal').style.display = 'block';
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        // Event listener to close the modal when clicking outside the content
        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Trigger modal based on PHP variables
        window.onload = function() {
            <?php if (!empty($errorMessage)): ?>
                showModal('Error', '<?php echo addslashes($errorMessage); ?>');
            <?php elseif ($success): ?>
                showModal('Success', 'Your facility request has been submitted successfully.');
            <?php endif; ?>
        }
    </script>
</head>
<body>
<header id="header">
    <div class="logo">
        <a href="index.php" style="text-decoration: none;">
            <h1>
                TICK<br>LINE
            </h1>
        </a>
    </div>

    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="menu">PROFILE</a>
        <?php else: ?>
            <a href="Login and signup.php" class="menu">SIGN UP</a>
        <?php endif; ?>
    </nav>
</header>
    <div class="container">
        <form action="" method="POST" enctype="multipart/form-data">
            <h2>Add New Facility</h2>

            <label for="facility_name">
                <span>Facility Name:</span>
                <input type="text" id="facility_name" name="facility_name" placeholder="Enter the name of the facility" required>
            </label>
            <label for="country">
                <span>Country:</span>
                <select name="country" id="country" required onchange="populateRegions()">
                    <option value="">Select Country</option>
                    <?php
                    // Define GCC countries
                    $gcc_countries = ["Bahrain", "Kuwait", "Oman", "Qatar", "Saudi Arabia", "United Arab Emirates"];
                    foreach ($gcc_countries as $country) {
                        echo "<option value=\"" . htmlspecialchars($country) . "\">" . htmlspecialchars($country) . "</option>";
                    }
                    ?>
                </select>
            </label>
            <label for="region">
                <span>Region:</span>
                <select name="region" id="region" required onchange="populateCities()">
                    <option value="">Select Region</option>
                    <!-- Regions will be populated based on selected country -->
                </select>
            </label>
            <label for="city">
                <span>City:</span>
                <select name="city" id="city" required>
                    <option value="">Select City</option>
                    <!-- Cities will be populated based on selected region -->
                </select>
            </label>
            <label for="gm_location_link">
                <span>Google Maps Link:</span>
                <input type="url" id="gm_location_link" name="gm_location_link" placeholder="https://maps.google.com/..." required>
            </label>
            <label for="price">
                <span>Price (in SR):</span>
                <input type="number" id="price" name="price" step="0.01" placeholder="e.g., 150.00" required>
            </label>
            <label for="description">
                <span>Description:</span>
                <textarea id="description" name="description" placeholder="Provide a detailed description of the facility"></textarea>
            </label>
            <label for="capacity">
                <span>Facility Capacity:</span>
                <input type="number" id="capacity" name="capacity" placeholder="Maximum number of people">
            </label>


            <label>
                <span>Amenities:</span>
            </label>
            <div id="amenities-container">
                <div class="amenity-group">
                    <select name="amenity_type[]" required onchange="toggleOtherInput(this)">
                        <option value="">Select Amenity</option>
                        <option value="Parking">Parking</option>
                        <option value="Chairs">Chairs</option>
                        <option value="Tables">Tables</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="number" name="amenity_quantity[]" min="1" placeholder="Quantity" required>
                    <input type="text" name="amenity_other[]" placeholder="Please specify" class="amenity-other" style="display: none;">
                    <button type="button" onclick="this.parentElement.remove()">Remove</button>
                </div>
            </div>
            <button type="button" id="add-amenity-btn" onclick="addAmenity()">Add Another Amenity</button>

            <!-- Images Upload -->
            <label class="file-input" for="facility_images">
                <span>Facility Images:</span>
                <input type="file" id="facility_images" name="facility_images[]" multiple accept="image/*">
            </label>
            <label class="file-input" for="banner_img">
                <span>Banner Image:</span>
                <input type="file" id="banner_img" name="banner_img" accept="image/*">
            </label>

            <button type="submit" name="submit_facility" class="submit-btn">Submit Facility</button>
        </form>
    </div>

    

    <!-- Customized Modal Structure -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <h3 id="modal-title"></h3>
            <p id="modal-message" class="modal-message"></p>
            <button class="modal-button" onclick="closeModal()">GOT IT!</button>
        </div>
    </div>
</body>
</html>
