uber-dot-arcanist
=================

This product encapsulates any arcanist plugins we depend on at Uber. With it,
you can avoid copypasta plugin folders in the .arcanist folder at the root of
your project.

Included Arcanist Plugins
-------------------------

Currently, this module contains only two arcanist plugins:
* tap
* [jenkinsphoo][jenkinsphoo]
* [lint-trap][lint-trap]

Usage
-----

To use this module, follow these steps from the root of your project:

```bash
# upgrade arcanist
arc upgrade

# install uber-dot-arcanist as a dev-dependency
npm install --save-dev uber-dot-arcanist

# check the contents of .arcanist to verify that it does not contain any
# plugins not included with either this module or with arcanist itself. Most
# projects will only have tap, jenkinsphoo and jshintlinter. This module
# contains the first two and the most recent version of arc that you just
# upgraded to includes jshintlinter.
ls -al .arcanist

# if there are no folders or directories in .arcanist besides jenkinsphoo, tap
# or jshintlinter, you can delete .arcanist. If there are any other modules,
# follow the instructions in the section 'How to handle other plugins in
# .arcanist'
rm -rf .arcanist
```

After removing the .arcanist folder, you need to configure arcanist to load the
plugins from the `node_modules/uber-dot-arcanist/.arcanist/` folder.

Open your `.arcconfig` file and look for the `load` property. It should be an
array like so:

```json
{
    "load": [".arcanist/tap", ".arcanist/jenkinsphoo", "arcanist/jshintlinter"]
}
```

If you see `.arcanist/jshintlinter` in the array, you can delete that element.
For `jenkinsphoo` and `tap`, you just need to prepend the path to the .arcanist
folder in this module. Assuming your `load` value was the one right above, your
new value should be:

```json
{
    "load": [
        "node_modules/uber-dot-arcanist/.arcanist/tap",
        "node_modules/uber-dot-arcanist/.arcanist/jenkinsphoo",
        "node_modules/uber-dot-arcanist/.arcanist/lint-trap"
    ]
}
```

To add support for lint-trap when submitting differentials to Phabricator with
`arc diff`, you can add the following to your `.arclint` file.

```json
{
    "linters": {
        "lint-trap": {
            "type": "lint-trap"
        }
    }
}
```

Once you've made these changes just stage your changes and commit:

```bash
git add package.json
git add .arclint
git rm .arcanist
git add .arcconfig
git commit -m "Loading arcanist plugins from uber-dot-arcanist npm module"
```

How to handle other plugins in .arcanist
----------------------------------------

If you encounter a plugin in your .arcanist folder that is not `jenkinsphoo`,
`tap` or `jshintlinter`, you should first check the [phacility/arcanist][arcrepo]
to see if the plugin you are using is already part of the standard arcanist
install. If it is not, git clone this repo and add the plugin to this repo to
make it available to other engineers at Uber. Don't forget to add the repo to
the section titled "Included Arcanist Plugins" at the top of this README.

Tests
-----

This module is just a wrapper around arcanist plugins. It is not callable as a
library and contains no binary file. The tests just check that the expected
plugins are included and that this module throws if called programmatically.

License
-------

[`JenkinsDiffEventListener.php`][jenkinsphoo] is Apache 2 licensed from
Disqus. Everything else is MIT licensed from Uber Technologies, Inc.

The MIT License (MIT)

Copyright (c) 2014 Uber Technologies, Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.


[jenkinsphoo]: https://github.com/disqus/disqus-arcanist/blob/master/src/event/JenkinsDiffEventListener.php
[lint-trap]: https://github.com/uber/lint-trap
