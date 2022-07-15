const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeAttributeService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'test') {
        super(httpClient, loginService, apiEndpoint);
    }

    async getShopwareAttributes () {
        return await this.client.get('ergonode/shopware-attributes', {
            headers: this.getBasicHeaders(),
        });
    }

    async getErgonodeAttributes () {
        return await this.client.get('ergonode/ergonode-attributes', {
            headers: this.getBasicHeaders(),
        });
    }
}
