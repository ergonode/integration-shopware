const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeAttributeService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'test') {
        super(httpClient, loginService, apiEndpoint);
    }

    async getShopwareAttributes () {
        return await this.client.get('strix/ergonode/shopware-attributes', {
            headers: this.getBasicHeaders(),
        });
    }

    async getErgonodeAttributes (types = []) {
        return await this.client.get('strix/ergonode/ergonode-attributes', {
            headers: this.getBasicHeaders(),
            params: {
                ...(types?.length && { types }),
            },
        });
    }
}
