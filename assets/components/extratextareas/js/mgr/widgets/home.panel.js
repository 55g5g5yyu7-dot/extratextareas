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
            xtype: 'panel',
            border: true,
            cls: 'main-wrapper',
            style: 'margin-bottom: 12px; padding: 10px;',
            items: [{
                html: '<strong>' + _('extratextareas.diagnostics_title') + '</strong><br/><small>Запуск быстрой проверки работоспособности компонента</small>',
                border: false,
                style: 'margin-bottom:8px;'
            }, {
                xtype: 'button',
                text: _('extratextareas.diagnostics_run'),
                handler: this.runDiagnostics,
                scope: this
            }, {
                xtype: 'textarea',
                id: 'extratextareas-diagnostics-log',
                readOnly: true,
                anchor: '100%',
                height: 180,
                style: 'margin-top: 8px; font-family: monospace;',
                value: 'Нажмите кнопку «' + _('extratextareas.diagnostics_run') + '», чтобы получить отчёт.'
            }]
        }, {
            xtype: 'extratextareas-grid-fields'
        }]
    });
    ExtraTextAreas.panel.Home.superclass.constructor.call(this, config);
};

Ext.extend(ExtraTextAreas.panel.Home, MODx.Panel, {
    runDiagnostics: function() {
        var logField = Ext.getCmp('extratextareas-diagnostics-log');
        if (logField) {
            logField.setValue('Запускаю диагностику...');
        }

        MODx.Ajax.request({
            url: ExtraTextAreas.config.connectorUrl,
            params: { action: 'mgr/diagnostics/run' },
            listeners: {
                success: {
                    fn: function(r) {
                        var log = r && r.object && r.object.log ? r.object.log : _('error');
                        if (logField) {
                            logField.setValue(log);
                        }
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        var log = r && r.message ? r.message : _('error');
                        if (logField) {
                            logField.setValue('❌ ' + log);
                        }
                        MODx.msg.alert(_('extratextareas.diagnostics_title'), log);
                    },
                    scope: this
                }
            }
        });
    }
});

Ext.reg('extratextareas-panel-home', ExtraTextAreas.panel.Home);
