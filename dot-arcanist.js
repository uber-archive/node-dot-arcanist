'use strict';

function dotArcanist() {
    var msg = [
        'uber-dot-arcanist is not callable programmatically.',
        'Please consult the README for usage.'
    ].join('\n');
    throw new Error(msg);
}

module.exports = dotArcanist;
