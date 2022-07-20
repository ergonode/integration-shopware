import './acl'
import './page'
import './component'

Shopware.Module.register('strix-ergonode', {
    type: 'plugin',
    name: 'StrixErgonode',
    title: 'StrixErgonode.mainMenuItemGeneral',
    description: 'Shopware Ergonode integration plugin',
    version: '0.0.2',
    color: '#00bc87',
    icon: 'default-symbol-products',

    routes: {
        attributeMapping: {
            name: 'strix-ergonode-mapping',
            component: 'strix-ergonode-attribute-mapping',
            path: 'mapping',
            meta: {
                privilege: 'strix_ergonode_attribute_mapping.viewer',
            }
        },
        importHistory: {
            name: 'strix-ergonode-import-history',
            component: 'strix-ergonode-import-history',
            path: 'imports',
            // TODO: add privilege for import history viewing in routes
        },
        synchronisation: {
            name: 'strix-ergonode-synchronisation',
            component: 'strix-ergonode-synchronisation',
            path: 'synchronisation',
            meta: {
                privilege: 'strix_ergonode_synchronisation.triggerer',
            }
        },
    },

    navigation: [
        {
            id: 'strix.ergonode',
            path: 'strix.ergonode.attributeMapping',
            label: 'StrixErgonode.mainMenuItemGeneral',
            parent: 'sw-settings',
            privilege: 'strix_ergonode_attribute_mapping.viewer',
        },
        {
            id: 'strix.ergonode.mapping',
            path: 'strix.ergonode.attributeMapping',
            label: 'StrixErgonode.tabs.attributeMappings',
            parent: 'strix.ergonode',
            privilege: 'strix_ergonode_attribute_mapping.viewer',
        },
        {
            id: 'strix.ergonode.synchronisation',
            path: 'strix.ergonode.synchronisation',
            label: 'StrixErgonode.tabs.synchronisation',
            parent: 'strix.ergonode',
            privilege: 'strix_ergonode_synchronisation.triggerer',
        },
        {
            id: 'strix.ergonode.imports',
            path: 'strix.ergonode.importHistory',
            label: 'StrixErgonode.tabs.importHistory',
            parent: 'strix.ergonode',
            // TODO: add privilege for import history viewing in navigation
        },
    ],
})
