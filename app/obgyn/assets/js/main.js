let profile = document.querySelector('.header .flex .drop');

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
   searchForm.classList.remove('active');
}

let sidebar = document.querySelector(".side-bar");

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("menu-btn").addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const dropdowns = document.querySelectorAll(".dropdown > a");

    dropdowns.forEach(dropdown => {
        dropdown.addEventListener("click", function (e) {
            e.preventDefault();
            this.parentElement.classList.toggle("active");
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const serviceSelector = document.getElementById('serviceSelector');
    const allFields = document.querySelectorAll('.service-fields'); // Select all service fields
        
    // Initially hide all service fields
    allFields.forEach(field => {
        field.style.display = 'none';
    });

    // Show the relevant fields when a service is selected
    serviceSelector.addEventListener('change', function() {
        // Hide all service fields
        allFields.forEach(field => {
            field.style.display = 'none';
        });

         const selectedService = this.value; // Get selected service
            
        // Show the selected service fields
        if (selectedService) {
            const selectedFields = document.getElementById(selectedService);
            if (selectedFields) {
                selectedFields.style.display = 'block';
            }
        }
    });
});

let provinces = [], cities = [], barangays = [];

async function loadAddressData() {
    try {
        provinces = await fetch('../ph_addresses/province.json')
            .then(res => {
                if (!res.ok) throw new Error('Province data failed to load');
                return res.json();
            });
        cities = await fetch('../ph_addresses/city.json')
            .then(res => {
                if (!res.ok) throw new Error('City data failed to load');
                return res.json();
            });
        barangays = await fetch('../ph_addresses/barangay.json')
            .then(res => {
                if (!res.ok) throw new Error('Barangay data failed to load');
                return res.json();
            });
        populateProvinces();
    } catch (error) {
        console.error(error);
        alert('Address data failed to load. Please refresh the page.');
    }
}

function populateProvinces() {
    const provinceSelect = document.getElementById('province');
    provinceSelect.innerHTML = ''; // Clear default options

    // Find Laguna from the loaded provinces
    const laguna = provinces.find(p => p.province_name === 'Laguna');

    if (laguna) {
        const option = new Option(laguna.province_name, laguna.province_code);
        provinceSelect.add(option);
        provinceSelect.value = laguna.province_code; // Set as selected
        document.getElementById('province_name').value = laguna.province_name;

        // Automatically load Laguna's cities
        loadCities(laguna.province_code);
    } else {
        provinceSelect.innerHTML = '<option value="">Laguna not found</option>';
    }
}

function loadCities(provinceCode) {
    const citySelect = document.getElementById('city');
    citySelect.innerHTML = '<option value="">Select City</option>';

    const lagunaCities = cities.filter(city => city.province_code === provinceCode);

    lagunaCities.forEach(city => {
        const option = new Option(city.city_name, city.city_code);
        citySelect.add(option);
    });
}
document.getElementById('city').addEventListener('change', function () {
    const cityCode = this.value;
    const barangaySelect = document.getElementById('barangay');
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

    //Update hidden field
    document.getElementById('city_name').value = this.options[this.selectedIndex].text;

    //Filter barangays by city_code
    const filteredBarangays = barangays.filter(brgy =>
        brgy.city_code === cityCode // Verify this matches your JSON structure
    );

    filteredBarangays.forEach(brgy => {
        const option = new Option(brgy.brgy_name, brgy.brgy_code);
        barangaySelect.add(option);
    });
});

document.getElementById('barangay').addEventListener('change', function () {
    document.getElementById('barangay_name').value = this.options[this.selectedIndex].text;
});

// Load the data once the document is fully ready
document.addEventListener('DOMContentLoaded', loadAddressData);
