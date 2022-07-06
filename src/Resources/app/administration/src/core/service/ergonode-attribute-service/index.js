const ApiService = Shopware.Classes.ApiService;

export default class ErgonodeAttributeService extends ApiService {
    async getShopwareAttributes () {
        return await this.client.get('strix/ergonode/shopware-attributes');
    }

    async getErgonodeAttributes () {
        return await this.client.get('strix/ergonode/ergonode-attributes');
    }

    async triggerSynchronisation (endpoint) {
        return await this.client.post(`_action/strix/ergonode/${endpoint}`);
    }
}
