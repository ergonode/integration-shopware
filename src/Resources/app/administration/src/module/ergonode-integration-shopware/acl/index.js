Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'additional_permissions',
    parent: null,
    key: 'ergonode',
    roles: {
        synchronisation_triggerer: {
            privileges: [
                'ergonode:synchronisation_triggerer',
            ],
            dependencies: [],
        },
        history_viewer: {
            privileges: [
                'ergonode:history_viewer',
            ],
            dependencies: [],
        },
    },
});
