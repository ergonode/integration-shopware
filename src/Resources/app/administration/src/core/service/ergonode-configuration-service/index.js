const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeConfigurationService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'test') {
        super(httpClient, loginService, apiEndpoint);
    }

    async verifyCredentials (config = {}) {
        return await this.client.post('_action/ergonode/test-credentials', {
            apiEndpoint: config?.apiEndpoint,
            apiKey: config?.apiKey,
        },{
            headers: this.getBasicHeaders(),
        });
    }
}
