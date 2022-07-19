import './acl'
import './page'
import './component'

Shopware.Module.register('ergonode-integration-shopware', {
    type: 'plugin',
    name: 'ErgonodeIntegrationShopware',
    title: 'ErgonodeIntegrationShopware.mainMenuItemGeneral',
    description: 'Shopware Ergonode integration plugin',
    version: '0.0.5',
    color: '#00bc87',
    icon: 'default-symbol-products',

    routes: {
        attributeMapping: {
            name: 'ergonode-mapping',
            component: 'ergonode-attribute-mapping',
            path: 'mapping',
            meta: {
                privilege: 'ergonode_attribute_mapping.viewer',
            }
        },
        synchronization: {
            name: 'ergonode-synchronization',
            component: 'ergonode-synchronization',
            path: 'synchronization',
            meta: {
                privilege: 'ergonode_synchronization.triggerer',
            }
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
            id: 'ergonode.mapping',
            path: 'ergonode.integration.shopware.attributeMapping',
            label: 'ErgonodeIntegrationShopware.tabs.attributeMappings',
            parent: 'ergonode-integration',
            privilege: 'ergonode_attribute_mapping.viewer',
        },
        {
            id: 'ergonode.synchronization',
            path: 'ergonode.integration.shopware.synchronization',
            label: 'ErgonodeIntegrationShopware.tabs.synchronization',
            parent: 'ergonode-integration',
            privilege: 'ergonode_synchronization.triggerer',
        },
    ],
})
