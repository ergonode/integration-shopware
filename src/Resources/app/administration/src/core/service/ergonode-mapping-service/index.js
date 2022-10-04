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

    async getErgonodeAttributes(types = []) {
        return await this.client.get('ergonode/ergonode-attributes', {
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
}
