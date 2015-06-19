** DEPRECATED **
================

These plugins can now be found in [Uber's arcanist fork](https://github.com/uber/arcanist)

uber-dot-arcanist
=================

This product encapsulates any arcanist plugins we depend on at Uber that are not
included with arcanist itself. With this repo, you can avoid copypasta plugin 
folders in the .arcanist folder at the root of your project that are likely to
be out of date.

Included Arcanist Plugins
-------------------------

Currently, this module contains three arcanist plugins:
* tap
* [uber-standard][uber-standard]
* [lint-trap][lint-trap] (deprecated)

Usage
-----

To use this module, follow these steps from the root of your project:

```bash
# first make sure you have the most recent version of arcanist
arc upgrade

# install uber-dot-arcanist as a dev-dependency
npm install --save-dev uber-dot-arcanist

# if you currently have a .arcanist/ folder in your project, check it to make
# sure that is doesn't include any plugins that uber-dot-arcanist doesn't
# include.
# Legacy Uber projects may have tap, jenkinsphoo and jshintlinter. This module
# contains only the tap plugin as usage of the other two are deprecated at Uber.
ls -al .arcanist

# if there are no folders or directories in .arcanist besides jenkinsphoo, tap
# or jshintlinter, you can delete .arcanist. If there are any other modules,
# follow the instructions in the section 'How to handle other plugins in
# .arcanist'
git rm -rf .arcanist
```

After removing the .arcanist folder, you need to configure arcanist to load the
plugins from the `node_modules/uber-dot-arcanist/.arcanist/` folder.

Open your `.arcconfig` file and look for the `load` property. It is likely to be
an array like so:

```json
{
    "load": [".arcanist/tap", "arcanist/uber-standard"]
}
```

If you see `jshintlinter` or `jenkinsphoo` in the array, you can delete those
elements. For `tap`, you just need to prepend the path to the .arcanist
folder in this module. Assuming your `load` value was the one right above, your
new values would be:

```json
{
    "load": [
        "node_modules/uber-dot-arcanist/.arcanist/tap",
        "node_modules/uber-dot-arcanist/.arcanist/uber-standard"
    ]
}
```

To add support for uber-standard when submitting differentials to Phabricator
with `arc diff`, you can add the following to your `.arclint` file.

```json
{
    "linters": {
        "uber-standard": {
            "type": "uber-standard",
            "include": "(\\.js$)"
        }
    }
}
```

Once you've made these changes just stage your changes and commit:

```bash
git add package.json
git add .arclint
git add .arcconfig
git commit -m "Loading arcanist plugins from uber-dot-arcanist npm module"
```

How to handle other plugins in .arcanist
----------------------------------------

If you encounter a plugin in your .arcanist folder that is not either `tap` or
`uber-standard`, you should first check the [phacility/arcanist][arcrepo]
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


[lint-trap]: https://github.com/uber/lint-trap
[uber-standard]: https://github.com/uber/standard
