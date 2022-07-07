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
        }
    },

    navigation: [{
        id: 'strix.ergonode.mapping',
        path: 'strix.ergonode.attributeMapping',
        label: 'StrixErgonode.mainMenuItemGeneral',
        parent: 'sw-settings',
        privilege: 'strix_ergonode_attribute_mapping.viewer',
    }],
})
