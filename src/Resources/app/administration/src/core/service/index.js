import ErgonodeAttributeService from "./ergonode-attribute-service";
import ErgonodeSynchronizationService from "./ergonode-synchronization-service";

const { Application, Service } = Shopware;
const initContainer = Application.getContainer('init');

Service().register('ergonodeAttributeService', (container) => {
    return new ErgonodeAttributeService(
      initContainer.httpClient,
        container.loginService,
    );
});

Service().register('ergonodeSynchronizationService', (container) => {
    return new ErgonodeSynchronizationService(
        initContainer.httpClient,
        container.loginService,
    );
});
