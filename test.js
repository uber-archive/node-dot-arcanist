'use strict';
var test = require('tape');
var path = require('path');
var fs = require('fs');
var fmt = require('util').format;

var dotArcanist = require('./dot-arcanist');

var dotArcanistFolder = path.resolve(__dirname, '.arcanist');

function hasPlugin(name) {
    var pluginPath = path.join(dotArcanistFolder, name);
    return fs.existsSync(pluginPath);
}

function assertMessage(name) {
    return fmt('%s plugin exists at ./arcanist/%s', name, name);
}

test('dummy test', function t(assert) {
    assert.plan(5);

    var plugins = ['tap', 'jenkinsphoo', 'lint-trap', 'uber-standard'];

    plugins.forEach(assertPluginExists);

    function assertPluginExists(name) {
        assert.ok(hasPlugin(name), assertMessage(name));
    }

    var throwsMessage = 'Calling uber-dot-arcanist programmatically throws';
    assert.throws(dotArcanist, throwsMessage);
});
