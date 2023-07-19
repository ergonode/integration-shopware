const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeMappingService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'test') {
        super(httpClient, loginService, apiEndpoint);
    }

    async getShopwareAttributes() {
        return await this.client.get('ergonode/shopware-attributes', {
            headers: this.getBasicHeaders(),
        });
    }

    async getShopwareCustomFields() {
        return await this.client.get('ergonode/shopware-custom-fields', {
            headers: this.getBasicHeaders(),
        });
    }
    
    async getShopwareCategories() {
        return await this.client.get('ergonode/shopware-categories', {
            headers: this.getBasicHeaders(),
        });
    }

    async getErgonodeAttributes(types = []) {
        return await this.client.get('ergonode/ergonode-attributes', {
            headers: this.getBasicHeaders(),
            params: {types},
        });
    }
    async getErgonodeProducts(type = null) {
        return await this.client.get('ergonode/ergonode-products', {
            headers: this.getBasicHeaders(),
            params: {
                type
            },
        });
    }
    
    async getErgonodeCategories(types = []) {
        return await this.client.get('ergonode/ergonode-categories', {
            headers: this.getBasicHeaders(),
            params: {
                ...(types?.length && { types }),
            },
        });
    }
    
    async getErgonodeCategoryTrees() {
        return await this.client.get('ergonode/ergonode-category-trees', {
            headers: this.getBasicHeaders()
        });
    }
    
    async getTimezones() {
        return await this.client.get('ergonode/timezones', {
            headers: this.getBasicHeaders()
        });
    }
}
