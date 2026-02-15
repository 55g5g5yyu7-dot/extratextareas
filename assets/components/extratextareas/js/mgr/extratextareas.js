var ExtraTextAreas = function (config) {
    config = config || {};
    ExtraTextAreas.superclass.constructor.call(this, config);
};
Ext.extend(ExtraTextAreas, Ext.Component, {
    page: {},
    window: {},
    grid: {},
    panel: {}
});
Ext.reg('extratextareas', ExtraTextAreas);

ExtraTextAreas = new ExtraTextAreas();
