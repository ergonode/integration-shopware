import ErgonodeAttributeService from "./ergonode-attribute-service";
import ErgonodeSynchronisationService from "./ergonode-synchronisation-service";

const { Application, Service } = Shopware;
const initContainer = Application.getContainer('init');

Service().register('ergonodeAttributeService', (container) => {
    return new ErgonodeAttributeService(
      initContainer.httpClient,
        container.loginService,
    );
});

Service().register('ergonodeSynchronisationService', (container) => {
    return new ErgonodeSynchronisationService(
        initContainer.httpClient,
        container.loginService,
    );
});
