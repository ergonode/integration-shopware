const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeSynchronisationService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'test') {
        super(httpClient, loginService, apiEndpoint);
    }

    async triggerSynchronisation (endpoint) {
        return await this.client.post(`_action/strix/ergonode/${endpoint}`, null,{
            headers: this.getBasicHeaders(),
        });
    }
}
