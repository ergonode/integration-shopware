import './page'
import './component'

// TODO: add privilege for triggering synchronisation triggers
// Shopware.Service('privileges')
//      .addPrivilegeMappingEntry({
//          category: 'additional_permissions',
//          parent: null,
//          key: 'system', //'strix_ergonode_synchronisation',
//          roles: {
//              synchroniser: {
//                  privileges: ['strix_ergonode:sync'],
//                  dependencies: [],
//              },
//          },
//      })

Shopware.Module.register('strix-ergonode', {
    type: 'plugin',
    name: 'StrixErgonode',
    title: 'StrixErgonode.mainMenuItemGeneral',
    description: 'Shopware Ergonode integration plugin',
    version: '0.0.4',
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
        synchronisation: {
            name: 'strix-ergonode-synchronisation',
            component: 'strix-ergonode-synchronisation',
            path: 'synchronisation',
            meta: {
                privilege: 'strix_ergonode_attribute_mapping.viewer',
            }
        },
    },

    navigation: [{
        id: 'strix.ergonode.mapping',
        path: 'strix.ergonode.attributeMapping',
        label: 'StrixErgonode.mainMenuItemGeneral',
        parent: 'sw-settings',
        privilege: 'strix_ergonode_attribute_mapping.viewer',
    }],
})
