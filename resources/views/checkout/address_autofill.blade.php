&lt;div>
    &lt;input type="text" id="address-input" wire:model="address" placeholder="Enter your address" autocomplete="off">
    &lt;div id="address-suggestions" style="display: none;">
        &lt;ul>
            &lt;template x-for="address in addresses" :key="address">
                &lt;li @click="selectAddress(address)">&lt;x-text x-text="address">&lt;/li>
            &lt;/template>
        &lt;/ul>
    &lt;/div>
&lt;/div>

&lt;script>
    document.addEventListener('livewire:load', function () {
        const input = document.getElementById('address-input');
        let autocomplete;

        function initAutocomplete() {
            autocomplete = new google.maps.places.Autocomplete(input, {types: ['geocode']});
            autocomplete.addListener('place_changed', fillInAddress);
        }

        function fillInAddress() {
            const place = autocomplete.getPlace();
            Livewire.emit('setAddress', place.formatted_address);
        }

        initAutocomplete();

        Livewire.on('setAddress', address => {
            input.value = address;
            document.getElementById('address-suggestions').style.display = 'none';
        });
    });
&lt;/script>

@livewireStyles
@livewireScripts
