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
            xtype: 'box',
            autoEl: {
                tag: 'div',
                html: '' +
                    '<div class="panel panel-default" style="padding:10px;margin-bottom:12px;">' +
                    '  <div style="margin-bottom:8px;"><strong>' + _('extratextareas.diagnostics_title') + '</strong></div>' +
                    '  <div id="extratextareas-diagnostics-btn-wrap"></div>' +
                    '  <textarea id="extratextareas-diagnostics-log" readonly style="width:100%;min-height:180px;margin-top:8px;font-family:monospace;">' +
                    'Нажмите кнопку «' + _('extratextareas.diagnostics_run') + '», чтобы получить отчёт.' +
                    '</textarea>' +
                    '</div>'
            }
        }, {
            xtype: 'extratextareas-grid-fields'
        }],
        listeners: {
            afterrender: {
                fn: function() {
                    var btnWrap = Ext.get('extratextareas-diagnostics-btn-wrap');
                    if (!btnWrap) {
                        return;
                    }

                    new Ext.Button({
                        renderTo: btnWrap,
                        text: _('extratextareas.diagnostics_run'),
                        handler: this.runDiagnostics,
                        scope: this
                    });
                },
                scope: this
            }
        }
    });
    ExtraTextAreas.panel.Home.superclass.constructor.call(this, config);
};

Ext.extend(ExtraTextAreas.panel.Home, MODx.Panel, {
    writeDiagnosticsLog: function(text) {
        var logEl = Ext.get('extratextareas-diagnostics-log');
        if (logEl) {
            logEl.dom.value = text;
        }
    },

    runDiagnostics: function() {
        this.writeDiagnosticsLog('Запускаю диагностику...');

        MODx.Ajax.request({
            url: ExtraTextAreas.config.connectorUrl,
            params: { action: 'mgr/diagnostics/run' },
            callback: function(options, success, response) {
                var payload = null;
                var log = '';

                try {
                    payload = response && response.responseText
                        ? Ext.decode(response.responseText)
                        : null;
                } catch (e) {
                    payload = null;
                }

                if (payload && payload.object && payload.object.log) {
                    log = payload.object.log;
                } else if (payload && payload.message) {
                    log = '❌ ' + payload.message;
                } else if (response && response.responseText) {
                    log = '❌ HTTP ' + response.status + "\n" + response.responseText;
                } else {
                    log = '❌ Не удалось получить ответ от connector.php';
                }

                this.writeDiagnosticsLog(log);

                if (!success) {
                    MODx.msg.alert(_('extratextareas.diagnostics_title'), log);
                }
            },
            scope: this
        });
    }
});

Ext.reg('extratextareas-panel-home', ExtraTextAreas.panel.Home);
