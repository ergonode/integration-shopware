Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'additional_permissions',
    parent: null,
    key: 'ergonode_synchronization',
    roles: {
        triggerer: {
            privileges: [
                'ergonode_synchronization:triggerer',
            ],
            dependencies: [],
        },
    },
});
