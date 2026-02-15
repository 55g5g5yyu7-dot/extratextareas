ExtraTextAreas.panel.Home = function(config) {
    config = config || {};
    Ext.apply(config, {
        border: false,
        layout: 'form',
        cls: 'container',
        items: [{
            html: '<h2>' + _('extratextareas') + '</h2>',
            border: false,
            cls: 'modx-page-header'
        }, {
            xtype: 'extratextareas-grid-fields'
        }]
    });
    ExtraTextAreas.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(ExtraTextAreas.panel.Home, MODx.Panel);
Ext.reg('extratextareas-panel-home', ExtraTextAreas.panel.Home);
