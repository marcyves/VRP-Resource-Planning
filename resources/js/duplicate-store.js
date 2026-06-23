export function createDuplicateStore(modalName, fields) {
    const store = {
        url: '',
        request(url, ...values) {
            this.url = url;
            fields.forEach((field, index) => {
                this[field] = values[index] ?? '';
            });
            window.dispatchEvent(new CustomEvent('open-modal', { detail: modalName, bubbles: true }));
        },
    };

    fields.forEach((field) => {
        store[field] = '';
    });

    return store;
}
