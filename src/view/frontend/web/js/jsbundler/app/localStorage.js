export default function LocalStorage() {
    const STORAGE_KEY = 'mage-cache-storage';

    return {
        initialize() {
            const cache = this.getCacheStorage();
            cache['checkout-data'] = cache['checkout-data'] || {};
            this.setCacheStorage(cache);
        },

        getShippingFormData() {
            return this.getCheckoutDataItem('shippingAddressFormData');
        },

        setShippingFormData(data) {
            this.setCheckoutDataItem('shippingAddressFormData', data);
        },

        getShippingAddressSelected() {
            return this.getCheckoutDataItem('shippingAddressSelected');
        },

        setShippingAddressSelected(addressId) {
            this.setCheckoutDataItem('shippingAddressSelected', addressId);
        },

        getCheckoutDataItem(key) {
            const checkoutData = this.getCheckoutData();
            return checkoutData[key] || null;
        },

        setCheckoutDataItem(key, value) {
            const checkoutData = this.getCheckoutData();
            checkoutData[key] = value;

            const fullCache = this.getCacheStorage();
            fullCache['checkout-data'] = checkoutData;

            this.setCacheStorage(fullCache);
        },

        getCacheStorage() {
            try {
                const data = localStorage.getItem(STORAGE_KEY);
                return data ? JSON.parse(data) : {};
            } catch (e) {
                console.error('LocalStorage::getCacheStorage failed', e);
                return {};
            }
        },

        setCacheStorage(data) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            } catch (e) {
                console.error('LocalStorage::setCacheStorage failed', e);
            }
        },

        getCheckoutData() {
            const cache = this.getCacheStorage();
            return cache['checkout-data'] || {};
        },
    };
}
