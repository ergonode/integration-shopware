const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeSynchronizationService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'test') {
        super(httpClient, loginService, apiEndpoint);
    }

    async triggerSynchronization (endpoint, payload = {}) {
        return await this.client.post(`_action/ergonode/${endpoint}`, payload, {
            headers: this.getBasicHeaders(),
        });
    }
}
