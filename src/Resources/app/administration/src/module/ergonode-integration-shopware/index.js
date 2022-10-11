import './acl'
import './page'
import './component'

Shopware.Module.register('ergonode-integration-shopware', {
    type: 'plugin',
    name: 'ErgonodeIntegrationShopware',
    title: 'ErgonodeIntegrationShopware.mainMenuItemGeneral',
    description: 'Shopware Ergonode integration plugin',
    version: '1.4.0',
    color: '#00bc87',
    icon: 'default-symbol-products',

    routes: {
        attributeMapping: {
            name: 'ergonode-attribute-mapping',
            component: 'ergonode-attribute-mapping',
            path: 'attribute-mapping',
            meta: {
                privilege: 'ergonode_attribute_mapping.viewer',
            }
        },
        customFieldMapping: {
            name: 'ergonode-custom-field-mapping',
            component: 'ergonode-custom-field-mapping',
            path: 'custom-field-mapping',
            meta: {
                privilege: 'ergonode_custom_field_mapping.viewer',
            }
        },
        importHistory: {
            name: 'ergonode-import-history',
            component: 'ergonode-import-history',
            path: 'imports',
            meta: {
                privilege: 'ergonode.history_viewer',
            },
        },
        synchronization: {
            name: 'ergonode-synchronization',
            component: 'ergonode-synchronization',
            path: 'synchronization',
            meta: {
                privilege: 'ergonode.synchronisation_triggerer',
            },
        },
    },

    navigation: [
        {
            id: 'ergonode-integration',
            path: 'ergonode.integration.shopware.attributeMapping',
            label: 'ErgonodeIntegrationShopware.mainMenuItemGeneral',
            parent: 'sw-settings',
            privilege: 'ergonode_attribute_mapping.viewer',
        },
        {
            id: 'ergonode.mapping.attribute',
            path: 'ergonode.integration.shopware.attributeMapping',
            label: 'ErgonodeIntegrationShopware.tabs.attributeMappings',
            parent: 'ergonode-integration',
            privilege: 'ergonode_attribute_mapping.viewer',
        },
        {
            id: 'ergonode.mapping.custom-field',
            path: 'ergonode.integration.shopware.customFieldMapping',
            label: 'ErgonodeIntegrationShopware.tabs.customFieldMappings',
            parent: 'ergonode-integration',
            privilege: 'ergonode_custom_field_mapping.viewer',
        },
        {
            id: 'ergonode.synchronization',
            path: 'ergonode.integration.shopware.synchronization',
            label: 'ErgonodeIntegrationShopware.tabs.synchronization',
            parent: 'ergonode-integration',
            privilege: 'ergonode.synchronisation_triggerer',
        },
        {
            id: 'ergonode.imports',
            path: 'ergonode.integration.shopware.importHistory',
            label: 'ErgonodeIntegrationShopware.tabs.importHistory',
            parent: 'ergonode-integration',
            privilege: 'ergonode.history_viewer',
        },
    ],
})
