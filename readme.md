# Gimmie Magento Plugins

Adding Gimmie rewards to Magento and allow user earn points and get real rewards when purchasing and referral friends.
To install this plugin, we provide [quickstart here](https://github.com/gimmie/quickstart/blob/master/magento.md).

## Build

- Clone all submodules (gimmie php project) requires for this plugin

```
$git submodule init && git submodule update
```

- Install gulp by npm. `npm install -g gulp`
- Run gulp command to build magento package. It will generate `gimmie.tgz` file inside root project.
- To update version, change version number in [gulpfile.js](gulpfile.js) and run `gulp` to create magento package file.

# License
The MIT License (MIT)

Copyright (c) 2014 Gimmieworld pte ltd.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
