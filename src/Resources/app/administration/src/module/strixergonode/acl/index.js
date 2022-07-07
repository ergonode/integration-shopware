Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'additional_permissions',
    parent: null,
    key: 'strix_ergonode_synchronisation',
    roles: {
        triggerer: {
            privileges: [
                'strix_ergonode_synchronisation:triggerer',
            ],
            dependencies: [],
        },
    },
});
