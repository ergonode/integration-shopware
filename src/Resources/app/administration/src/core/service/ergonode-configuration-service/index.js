const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeConfigurationService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'test') {
        super(httpClient, loginService, apiEndpoint);
    }

    async verifyCredentials (config = {}) {
        return await this.client.post('_action/strix/ergonode/test-credentials', {
            baseUrl: config?.baseUrl,
            apiKey: config?.apiKey,
        },{
            headers: this.getBasicHeaders(),
        });
    }
}
