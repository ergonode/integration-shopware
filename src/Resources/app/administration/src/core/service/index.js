import ErgonodeAttributeService from "./ergonode-attribute-service";

const { Application, Service } = Shopware;
const initContainer = Application.getContainer('init');

Service().register('ergonodeAttributeService', (container) => {
    return new ErgonodeAttributeService(
      initContainer.httpClient,
        container.loginService,
    );
});
