const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeImportHistoryService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'test') {
        super(httpClient, loginService, apiEndpoint);
    }

    async fetchImportLog (id) {
        return await this.client.get(`/ergonode/sync-history-log/${String(id)}`,{
            headers: this.getBasicHeaders(),
        });
    }
}
