/**
 * Check for write permission for the selected plugin type
 *
 * @module      moodle-tool_managecourse-permcheck
 * @author      David Mudrak <david@moodle.com>
 */
YUI.add('moodle-tool_managecourse-permcheck', function(Y) {

    M.tool_managecourse = M.tool_managecourse || {};

    /**
     * @class permcheck
     * @static
     */
    M.tool_managecourse.permcheck = {

        /**
         * @method init
         * @param {Object} config Configuration passed from the PHP
         */
        init : function(config) {
            this.config = config;
            var plugintypesel = Y.one('#tool_managecourse_uploadpdf_plugintype');
            if (plugintypesel) {
                plugintypesel.on('change', function() {
                    this.check_for_permission(plugintypesel);
                }, this);
                this.repeat_permcheck_icon = Y.Node.create('<div><a href="#repeat-permcheck"><img src="' + M.util.image_url('i/reload') + '" /> ' +
                    M.util.get_string('permcheckrepeat', 'tool_managecourse') + '</a></div>');
                this.repeat_permcheck_icon.one('a').on('click', function(e) {
                    e.preventDefault();
                    this.check_for_permission(plugintypesel);
                }, this);
            }
        },

        /**
         * @method check_for_permission
         * @param {Node} plugintypesel Plugin type selector node
         */
        check_for_permission : function(plugintypesel) {
            var plugintype = plugintypesel.get('value');

            if (plugintype == '') {
                return;
            }
            Y.log('Selected plugin type: ' + plugintype, 'debug', 'moodle-tool_managecourse-permcheck');
            Y.io(this.config.permcheckurl, {
                'method' : 'GET',
                'data' : {
                    'sesskey' : M.cfg.sesskey,
                    'plugintype' : plugintype
                },
                'context' : this,
                'on' : {
                    'start' : function(transid, args) {
                        this.showresult(M.util.get_string('permcheckprogress', 'tool_managecourse'), 'progress');
                    },
                    'success': function(transid, outcome, args) {
                        var response;
                        try {
                            response = Y.JSON.parse(outcome.responseText);
                            if (response.error) {
                                Y.log(response.error, 'error', 'moodle-tool_managecourse-permcheck');
                                this.showresult(M.util.get_string('permcheckerror', 'tool_managecourse', response), 'error');
                            } else if (response.path && response.writable == 1) {
                                this.showresult(M.util.get_string('permcheckresultyes', 'tool_managecourse', response), 'success');
                            } else if (response.path && response.writable == 0) {
                                this.showresult(M.util.get_string('permcheckresultno', 'tool_managecourse', response), 'error');
                            } else {
                                Y.log(response, 'debug', 'moodle-tool_managecourse-permcheck');
                                this.showresult(M.util.get_string('permcheckerror', 'tool_managecourse', response), 'error');
                            }

                        } catch (e) {
                            Y.log(e, 'error', 'moodle-tool_managecourse-permcheck');
                            this.showresult(M.util.get_string('permcheckerror', 'tool_managecourse'), 'error');
                        }
                    },
                    'failure': function(transid, outcome, args) {
                        Y.log(outcome.statusText, 'error', 'moodle-tool_managecourse-permcheck');
                        this.showresult(M.util.get_string('permcheckerror', 'tool_managecourse'));
                    }
                }
            });
        },

        /**
         * @method showresult
         * @param {String} msg Message to display
         * @param {String} status Message status
         */
        showresult : function(msg, status) {
            var resultline = Y.one('#tool_managecourse_uploadpdf_permcheck');
            if (resultline) {
                if (status === 'success') {
                    resultline.setHTML('<span class="success"><img src="' + M.util.image_url('i/valid') + '" /> ' +
                        msg + '</span>');
                } else if (status === 'progress') {
                    resultline.setHTML('<span class="progress"><img src="' + M.cfg.loadingicon + '" /> ' +
                        msg + '</span>');
                } else {
                    resultline.setHTML('<span class="error"><img src="' + M.util.image_url('i/invalid') + '" /> ' +
                        msg + '</span>').append(this.repeat_permcheck_icon);
                }
            }
        },

        /**
         * @property
         * @type {Y.Node}
         */
        repeat_permcheck_icon : null,

        /**
         * @property
         * @type {Object}
         */
        config : null
    };

}, '@VERSION@', {
    requires:['node', 'event', 'io-base']
});
