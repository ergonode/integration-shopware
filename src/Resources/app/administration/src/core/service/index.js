import ErgonodeAttributeService from "./ergonode-attribute-service";
import ErgonodeSynchronizationService from "./ergonode-synchronization-service";
import ErgonodeConfigurationService from "./ergonode-configuration-service";
import ErgonodeImportHistoryService from "./ergonode-history-import-service";

const { Application, Service } = Shopware;
const initContainer = Application.getContainer('init');

Service().register('ergonodeAttributeService', (container) => {
    return new ErgonodeAttributeService(
      initContainer.httpClient,
        container.loginService,
    );
});

Service().register('ergonodeConfigurationService', (container) => {
    return new ErgonodeConfigurationService(
        initContainer.httpClient,
        container.loginService,
    );
});

Service().register('ergonodeImportHistoryService', (container) => {
    return new ErgonodeImportHistoryService(
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
