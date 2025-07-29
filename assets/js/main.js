let provinces = [], cities = [], barangays = [];

        async function loadAddressData() {
            try {
                provinces = await fetch('app/ph_addresses/province.json')
                    .then(res => {
                        if (!res.ok) throw new Error('Province data failed to load');
                        return res.json();
                    });
                cities = await fetch('app/ph_addresses/city.json')
                    .then(res => {
                        if (!res.ok) throw new Error('City data failed to load');
                        return res.json();
                    });
                barangays = await fetch('app/ph_addresses/barangay.json')
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
            provinceSelect.innerHTML = '<option value="">Select Province</option>';

            provinces.forEach((province) => {
                const option = new Option(province.province_name, province.province_code);
                provinceSelect.add(option);
            });
        }

        document.getElementById('province').addEventListener('change', function () {
            const provinceCode = this.value;
            const citySelect = document.getElementById('city');
            const barangaySelect = document.getElementById('barangay');

            citySelect.innerHTML = '<option value="">Select City</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            document.getElementById('province_name').value = this.options[this.selectedIndex].text;

            cities
                .filter(city => city.province_code === provinceCode)
                .forEach(city => {
                    const option = new Option(city.city_name, city.city_code);
                    citySelect.add(option);
                });
        });

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